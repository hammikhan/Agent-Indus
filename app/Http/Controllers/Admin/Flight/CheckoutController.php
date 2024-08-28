<?php

namespace App\Http\Controllers\Admin\Flight;

use App\Http\Controllers\Controller;
use App\Models\ApiOffer;
use Illuminate\Http\Request;
use App\Http\Traits\SabreTrait as Sabre;
use App\Http\Traits\AmadeusTrait as Amadeus;
use App\Http\Traits\APIS\HititTrait as Hitit;
use App\Http\Traits\PricingEngineTrait as PricingEngine;
use App\Mail\BookingMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PassengerProfile;
use App\Models\Provider;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    public function flightCheckout(Request $request){
        $itn_ref_key = $request->itn_ref_key;
        $brand_ref_key = @$request->brand_ref_key;
        $api_offer = ApiOffer::where('ref_key', $request->itn_ref_key)->first();
        $query = json_decode($api_offer->query, true);
        $totalPassenger = $query['adults'] + $query['children'] + $query['infants'];
        $query = json_decode($api_offer->query,true);
        $data = $api_offer->finaldata;
        
        $results = $data;
        $finaldata = json_decode(json_encode($results), true);
        // dd($finaldata);
        // ==============End Pricing Engine===================\\
        $fareRuleHtml = '';
        if($api_offer->api == 'Sabre'){
            $fareRuleHtml = self::checkoutFareRules($itn_ref_key,$brand_ref_key);
        }
        $customers = Customer::where('travelagent_id', Auth::guard('admin')->user()->id)->get();
        return view('admin.checkout.checkout', compact('customers','query','finaldata','itn_ref_key','brand_ref_key','fareRuleHtml'));
    }
    public function createPnr(Request $request){
        $requestData = $request->all();
        
        try {
            DB::beginTransaction();
        
            // Find or create customer
            $customer = Customer::updateOrCreate(
                ['email' => $requestData['customer_email']],
                [
                    'travelagent_id' => Auth::guard('admin')->user()->id,
                    'name' => $requestData['customer_name'],
                    'country' => $requestData['customer_country'],
                    'phone' => $requestData['customer_phone'],
                    'address' => @$requestData['customer_address'],
                    'contact_email' => @$requestData['email'],
                    'contact_country' => @$requestData['country'],
                    'contact_phone' => @$requestData['phone'],
                ]
            );
        
            // Process passenger profiles
            $passengerTypes = ['ADT', 'CNN', 'INF'];
            foreach ($requestData['passengers'] as $passenger) {
                if (isset($passenger['passenger_type']) && in_array($passenger['passenger_type'], $passengerTypes)) {
                    PassengerProfile::updateOrCreate(
                        ['id' => $passenger['id']],
                        [
                            'customer_id' => $customer->id,
                            'title' => $passenger['passenger_title'],
                            'firstName' => $passenger['name'],
                            'lastName' => $passenger['sur_name'],
                            'type' => $passenger['passenger_type'],
                            'gender' => $passenger['passenger_gender'],
                            'dob' => $passenger['dob'],
                            'region' => $passenger['nationality'],
                            'identity' => $passenger['document_type'],
                            'passportNumber' => $passenger['document_number'],
                            'passportExpiry' => $passenger['document_expiry_date'],
                            'identityNumber' => $passenger['identity_number'],
                            'issueCountry' => $passenger['nationality']
                        ]
                    );
                }
            }
        
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            if (env('APP_ENV') == "local") {
                return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
            }
        }

        array_walk_recursive($requestData, function (&$value, $key) {
            if (in_array($key, ['dob', 'document_expiry_date'])) {
                $value = date('Y-m-d', strtotime($value));
            }
        });

        $countryDialCode = countryDialCode($requestData['customer_country']);
        if ($countryDialCode !== null) {
            $requestData['customer_phone'] = $countryDialCode . str_replace(' ', '', $requestData['customer_phone']);
        }
        
        $api_offer = ApiOffer::where('ref_key',$requestData['itn_ref_key'])->first();
        $finaldata = $api_offer->finaldata;

        if($api_offer->api == 'Amadeus'){
            $response = Amadeus::createPNR($requestData);
        }elseif($api_offer->api == 'Hitit'){
            $response = Hitit::createPNR($requestData);
        }else{
            $response = Sabre::createPNR($requestData);
        }
        // dd($response);
        if($response['status']  == 200){
            $booked = Order::create([
                'agency_id' => @auth('admin')->user()->travel_agency_id,
                'user_id' => auth('admin')->user()->id,
                'type' => 'flight',
                'api' =>  $api_offer->api,
                'pnrCode' => $response['pnr'],
                'airline_pnr' => @$response['airlinePNR'],
                'customerEmail' => $requestData['customer_email'],
                'customerPhone' => $requestData['customer_phone'],
                'last_ticketing_date' => @$response['last_ticketing_date'],
                'total' => $response['TotalAmount'],
                'pricingEnginePrice' => $response['TotalAmount'],
                'basePrice' => $response['TotalAmount'],
                'customPrice' => $response['TotalAmount'],
                'userPricingEnginePrice' => $response['TotalAmount'],
                'paid' => 'Unpaid',
                'apiResponse' => json_encode($response['response']),
                'booking_response' => json_encode($response['response']),
                'final_data' => json_encode($finaldata),
                'status' => 'Not Ticketed',
                'pnr_status' => 'Confirmed',
                'customer_data' => json_encode($requestData),
            ]);
            if(@$booked){
                $url = route('admin.create.booking', ['booking_ref' => $booked->ref_key]);
                return response()->json(['status' => 'success', 'message' => 'Booked', 'url' => $url], 200);
            }else{
                return response()->json(['status' => 'fail', 'message' => 'General Error'], 400);
            }
        }else{
            return response()->json(['status' => 'fail', 'message' => $response['response']], 400);
        }

    }
    public function issueTicket(Request $request){
        $order_id = decrypt($request->book_ref_key);
        $order = Order::select('id', 'user_id', 'agency_id', 'pnrCode','apiResponse','final_data','customer_data','api','status','fetch_response')
                ->where('id',$order_id)
                ->where('pnrCode',$request->pnr)
                ->first();
        if($order->api == 'Amadeus'){
            $ticketResponse = Amadeus::issueTicket($order);
        }elseif($order->api == 'Hitit'){
            $ticketResponse = Hitit::issueTicket($order);
        }else{
            $ticketResponse = Sabre::issueTicket($order);
        }
        if($ticketResponse['status']  == 200){
            $customer_data = json_decode($order->customer_data,true);
            
            $order->tickets_data = @$ticketResponse['ticketData'];
            $order->status = 'Ticketed';
            $order->ticket_response = $ticketResponse['msg'];
            $order->issued_at = now();
            $order->save();

            $ticketRenderHtml = view('admin.checkout.includes.passenger-ticket-table',compact('customer_data','order'))->render();
            return response()->json(['status' => 'success', 'message' => 'Ticketed', 'ticketRenderHtml' => $ticketRenderHtml], 200);
        }else{
            return response()->json(['status' => 'error', 'message' => $ticketResponse['msg']], 201);
            // return json_encode(['status' => '201' , 'msg' => "PNR can't be ticketed anymore"]);
        }
    }
    public function updatePNR(Request $request){
        $order_id = decrypt($request->book_ref_key);
        $order = Order::select('id', 'pnrCode','customer_data','api','status')
                ->where('id',$order_id)
                ->where('pnrCode',$request->pnr)
                ->first();
        if($order->api == 'Amadeus'){
            $fetchPNRResponse = Amadeus::fetchPNR($order);
        }else{
            $fetchPNRResponse = Sabre::fetchPNR($order);
        }
        // dd($fetchPNRResponse);
        if($fetchPNRResponse['status']  == 200){
            if(@$fetchPNRResponse['airline']){
                $order->pnr_status = $fetchPNRResponse['airline'][0]['pnrStatus'];
                $extras = [
                    'ticket' => @$fetchPNRResponse['ticket'],
                    'services' => @$fetchPNRResponse['services'],
                    'airline' => @$fetchPNRResponse['airline']
                ];
                $order->extras = json_encode($extras);
                $order->tickets_data = @$fetchPNRResponse['ticketData'];
            }
            if(@$fetchPNRResponse['ticket']){
                $order->status = $fetchPNRResponse['ticketStatus'];
            }
            $order->fetch_response = $fetchPNRResponse['msg'];
            $order->save();
            return response()->json(['status' => 'success', 'message' => 'PNR Updated'], 200);
        }else{
            return response()->json(['status' => 'error', 'message' => $fetchPNRResponse['msg']], 201);
        }
    }
    public function checkoutFareRules($itn_ref_key,$brand_ref_key){
        // dd($itn_ref_key,$brand_ref_key);
        $api_offer = ApiOffer::where('ref_key', $itn_ref_key)->first();
        $provider = Provider::where('identifier',strtolower($api_offer->api))->first();
        $finaldata = $api_offer->finaldata;
        $response = Sabre::airRulesRQ($finaldata,$brand_ref_key);
        // dd($response);
        $outbond = $response['Rules'][0];
        $ruleHtml1 = view('admin.flight.includes.fare-rule-render',compact('outbond'))->render();
        if(@$response['Rules'][1]){
            $inbound = $response['Rules'][1];
            $ruleHtml2 = view('admin.flight.includes.fare-rule-render',compact('inbound'))->render();
        }else{
            $ruleHtml2 = null;
        }
        return [
            $ruleHtml1,$ruleHtml2
        ];
    }
    /************************************************\
     * *************PNR Testing******************** *|
    \************************************************/


}
