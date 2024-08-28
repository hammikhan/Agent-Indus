<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Mail\BookingMail;
use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\SabreTrait as Sabre;
use App\Http\Traits\AmadeusTrait as Amadeus;
use App\Http\Traits\APIS\HititTrait as Hitit;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BookingController extends Controller
{
    public function bookingList(Request $request) {
        $query = Order::orderBy('id', 'desc');
        if (auth('admin')->user()->type == 'Travel Agent') {
            $query->where('agency_id', auth('admin')->user()->travel_agency_id);
        }
    
        if ($request->filled('search_criteria')) {
            $searchCriteria = $request->search_criteria;
            $searchText = ltrim($request->search_text, '0');
    
            switch ($searchCriteria) {
                case 'PNR':
                    $query->where('pnrCode', 'like', '%' . $searchText . '%');
                    break;
                case 'ticket':
                    $query->whereJsonContains('tickets_data', ['TicketNumber' => $searchText]);
                    break;
                case 'pax_name':
                    $query->whereRaw("JSON_EXTRACT(customer_data, '$.passengers') IS NOT NULL")
                          ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(customer_data, '$.passengers[*].name')) LIKE ?", ["%{$request->first_name}%"])
                          ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(customer_data, '$.passengers[*].sur_name')) LIKE ?", ["%{$request->last_name}%"]);
                    break;
                case 'email':
                    $query->where('customerEmail', 'like', '%' . $searchText . '%');
                    break;
                case 'phone':
                    $query->where('customerPhone', 'like', '%' . $searchText . '%');
                break;
            }
        }
    
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'Ticketed':
                    $query->where('status', 'Ticketed');
                    break;
                case 'Not Ticketed':
                    $query->where('status', 'Not Ticketed');
                    break;
                case 'Voided':
                    $query->where('status', 'Voided');
                    break;
                case 'Cancelled':
                    $query->where('status', 'Cancelled');
                    break;
            }
        }
    
        if ($request->has('from') && $request->has('to') && !is_null($request->from) && !is_null($request->to)) {
            $fromDate = Carbon::createFromFormat('d-m-Y', $request->from)->startOfDay();
            $toDate = Carbon::createFromFormat('d-m-Y', $request->to)->endOfDay();
            if ($request->date_by === 'issued_date') {
                $query->whereBetween('issued_at', [$fromDate, $toDate]);
            } else {
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            }
        }
    
        $bookings = $query->paginate(8);
        return view('admin.bookings.list', compact('bookings'));
    }
    
    public function bookingListOld(Request $request){
        $query = Order::orderBy('id', 'desc');
        if (auth('admin')->user()->type == 'Travel Agent') {
            $query->where('agency_id', auth('admin')->user()->travel_agency_id);
        }

        if ($request->filled('pnr')) {
            $query->where('pnrCode', 'like', '%' . $request->pnr . '%');
        }
        if ($request->filled('email')) {
            $query->where('customerEmail', 'like', '%' . $request->email . '%');
        }

        if ($request->has('first_name') && !is_null($request->first_name)) {
            $firstName = $request->first_name;
            $query->whereRaw("JSON_EXTRACT(customer_data, '$.passengers') IS NOT NULL")
                  ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(customer_data, '$.passengers[*].name')) LIKE ?", ["%{$firstName}%"]);
        }
        if ($request->has('last_name') && !is_null($request->last_name)) {
            $lastName = $request->last_name;
            $query->whereRaw("JSON_EXTRACT(customer_data, '$.passengers') IS NOT NULL")
                  ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(customer_data, '$.passengers[*].sur_name')) LIKE ?", ["%{$lastName}%"]);
        }
        if ($request->has('ticket_number') && !is_null($request->ticket_number)) {
            $query->whereJsonContains('tickets_data', ['TicketNumber' => $request->ticket_number]);
        }
        if ($request->has('from') && $request->has('to') && !is_null($request->from) && !is_null($request->to)) {
            $fromDate = Carbon::createFromFormat('d-m-Y', $request->from)->startOfDay();
            $toDate = Carbon::createFromFormat('d-m-Y', $request->to)->endOfDay();
            $query->whereBetween('created_at', [$fromDate, $toDate]);
        }
        $bookings = $query->paginate(8);

        return view('admin.bookings.list',compact('bookings'));
    }
    public function bookingDetail($booking_ref){

        $order = Order::where('ref_key',$booking_ref)->first();
        if($order->api == 'Sabre'){
            if($order->pnr_status != 'Cancelled'){ 
                $fetchPNRResponse = Sabre::fetchPNR($order);
                if($fetchPNRResponse['status']  == 200){
                    if(@$fetchPNRResponse['airline']){
                        $order->pnr_status = $fetchPNRResponse['airline'][0]['pnrStatus'];
                        $extras = [
                            'ticket' => @$fetchPNRResponse['ticket'],
                            'services' => @$fetchPNRResponse['services'],
                            'airline' => @$fetchPNRResponse['airline'],
                            'newFligts' => @$fetchPNRResponse['newFligts']
                        ];
                        $order->extras = json_encode($extras);
                    }
                    if(@$fetchPNRResponse['ticketData']){
                        $order->tickets_data = json_encode($fetchPNRResponse['ticketData']);
                    }
                    if($fetchPNRResponse['ticketStatus']){
                        $order->status = $fetchPNRResponse['ticketStatus'];
                    }
                    $order->fetch_response = $fetchPNRResponse['msg'];
                    $order->save();
                }
            }
            return view('admin.checkout.pnr',compact('order'));
        }
        return view('admin.checkout.pnr',compact('order'));
    }
    public function repricePNR(Request $request){
        $order = Order::where('ref_key',$request->book_ref_key)->first();
        if($order->api == "Sabre"){
            $response = Sabre::repricePNR($order);
            
            if($response['status'] == 200){
                $order->final_data = json_encode($response['final_data']);
                $order->save();
                return response()->json(['status' => 'success', 'message' => 'PNR has been Repriced successfully, Pleas Note..! The new fare may vary from the initial booked fare.'], 200);
            }else{
                return response()->json(['status' => 'error', 'message' => $response['msg']], 201);
            }
        }else{
            return response()->json(['status' => 'error', 'message' => 'Please contact to Admin for '.$order->api.' Quotation'], 201);
        }
        
    }
    public function emailBooking(Request $request){
        try {
            $allColumns = Schema::getColumnListing('orders');

            $excludedColumns = [
                'basePrice',
                'booking_response', 
                'ticket_response', 
                'fetch_response', 
                'apiResponse',
                'apiResponse',
            ];

            $selectedColumns = array_diff($allColumns, $excludedColumns);
        
            $orderObj = Order::select($selectedColumns)
                ->where('ref_key', $request->book_ref_key)
                ->where('pnrCode', $request->pnr)
                ->first();

                if ($orderObj) {
                    $order = $orderObj->toArray();
                    $agencyObj = $orderObj->agency;
                    $adminObj = $orderObj->admin;
                    if ($agencyObj) {
                        $agency = $agencyObj->toArray();
                    } else {
                        $agency = null;
                    }
                    if ($adminObj) {
                        $admin = $adminObj->toArray();
                    } else {
                        $admin = null;
                    }
                    
                    $order['admin'] = $admin;
                    $order['f'] = $request->f;
                    if(@$request->booking_agent == 1){
                        $order['agency'] = 0;
                    }else{
                        $order['agency'] = $agency;
                    }
                    
                    $pdf = PDF::loadView('pdf.booking-pdf', $order);
                    $pdfPath = storage_path('app/public/booking.pdf');
                    $pdf->save($pdfPath);

                    $bookMail = new BookingMail($order, $pdfPath);
                    if(@$request->email){
                        Mail::to($request->email)->send($bookMail);
                    }else{
                        Mail::to($order['customerEmail'])->send($bookMail);
                    }

                    File::delete($pdfPath);
                } else {
                    dd('Order not found');
                }
            /**************** End Email Booking **************/
            return response()->json(['status' => 'success', 'message' => 'Email send Successfuly'], 200);
        } catch (Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Sorry Email not send, Some error occurs'], 201);
        }
    }
    public function cancelBooking(Request $request){
        $order_id = decrypt($request->book_ref_key);
        $order = Order::select('id', 'pnrCode','api','customerEmail','apiResponse')
                ->where('id',$order_id)
                ->where('pnrCode',$request->pnr)
                ->first();
        if($order->api == 'Amadeus'){
            $cancelResponse = Amadeus::cancelBookingRequest($order);
        }
        if($order->api == 'Hitit'){
            $cancelResponse = Hitit::cancelBookingRequest($order);
        }
        if($order->api == 'Sabre'){
            $cancelResponse = Sabre::cancelBookingRequest($order);
        }

        if($cancelResponse['status']  == 200){
            if(@$cancelResponse['airline']){
                $order->pnr_status = $cancelResponse['airline'][0]['pnrStatus'];
            }
            if(@$cancelResponse['ticket']){
                $order->status = $cancelResponse['ticket'][0]['ticketStatus'];
            }
            $order->save();
            return response()->json(['status' => 'success', 'message' => 'Booking Cancelled Successfully....'], 200);
        }else{
            return response()->json(['status' => 'error', 'message' => $cancelResponse['msg']], 201);
        }
    }
    public function voidTicket(Request $request){
        $order_id = decrypt($request->book_ref_key);
        $order = Order::select('id', 'pnrCode','api','customerEmail','customer_data','tickets_data','apiResponse')
                ->where('id',$order_id)
                ->where('pnrCode',$request->pnr)
                ->first();
        if($order->api == 'Amadeus'){
            $voidTicketResponse = Amadeus::voidBookingRequest($order);
        }
        if($order->api == 'Hitit'){
            $voidTicketResponse = Hitit::voidBookingRequest($order);
        }
        if($order->api == 'Sabre'){
            $voidTicketResponse = Sabre::voidBookingRequest($order);
        }
        // return $voidTicketResponse;
        if($voidTicketResponse['status']  == 200){
            if(@$voidTicketResponse['ticket']){
                $order->status = $voidTicketResponse['ticket'][0]['ticketStatus'];
            }
            $order->save();
            return response()->json(['status' => 'success', 'message' => 'Booking Cancelled Successfully....'], 200);
        }else{
            return response()->json(['status' => 'error', 'message' => $voidTicketResponse['msg']], 201);
        }
    }
    // ***********************************************\\
    public function bookingList2(){
        $bookings = Order::orderBy('id','desc')->get();
        return view('admin.bookings.list2',compact('bookings'));
    }
    public function bookingPDF($booking_ref,$f){
        
        $allColumns = Schema::getColumnListing('orders');

        $excludedColumns = [
            'basePrice',
            'booking_response', 
            'ticket_response', 
            'fetch_response', 
            'apiResponse',
            'apiResponse',
        ];

        $selectedColumns = array_diff($allColumns, $excludedColumns);
    
        $orderObj = Order::select($selectedColumns)
            ->where('ref_key', $booking_ref)
            ->first();
            if ($orderObj) {
                $order = $orderObj->toArray();
                $agencyObj = $orderObj->agency;
                $adminObj = $orderObj->admin;
                if ($agencyObj) {
                    $agency = $agencyObj->toArray();
                } else {
                    $agency = null;
                }
                if ($adminObj) {
                    $admin = $adminObj->toArray();
                } else {
                    $admin = null;
                }
                // dd($agency);
                $order['admin'] = $admin;
                $order['agency'] = $agency;
                $order['f'] = $f;
                $imagePath = public_path('assets/images/mainLogo.png');

                $order['imageSrc'] = $imagePath;

                $pdf = Pdf::loadView('pdf.booking-pdf', $order);
                return $pdf->stream('booking-'.$order['pnrCode'].'.pdf');
            } else {
                dd('Order not found');
            }
        }
            
}
