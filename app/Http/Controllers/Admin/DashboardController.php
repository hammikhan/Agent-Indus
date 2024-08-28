<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AuthEmail;
use App\Mail\WelcomeEmail;
use App\Models\Order;
use App\Models\RecentSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{
    public function index(){
        $order = Order::query();
        if (auth('admin')->user()->type == 'Travel Agent') {
            $order->where('agency_id', auth('admin')->user()->travel_agency_id);
        }
        $bookings = $order->get();
        $recent_bookings = $order->select('ref_key','pnrCode','api','status','pnr_status','agency_id','user_id','final_data','created_at','issued_at')
                ->orderBy('id', 'desc')
                ->get()->take(10);
        
        /*****************Revenue**************************/
        $data = [
            'Not Ticketed' => ['revenue' => 0, 'count' => 0],
            'Ticketed' => ['revenue' => 0, 'count' => 0],
            'Voided' => ['revenue' => 0, 'count' => 0],
            'Cancelled' => ['revenue' => 0, 'count' => 0],
            'Refunded/Exchanged' => ['revenue' => 0, 'count' => 0]
        ];
        
        foreach ($bookings as $value) {
            $status = $value['status'];
            $data[$status]['revenue'] += round($value['userPricingEnginePrice']);
            $data[$status]['count']++;
        }
        /*****************Recent Searches*********************/
        $recent_searches = RecentSearch::select('origin', 'destination')->get()->toArray();

        $counts = collect($recent_searches)->map(function ($search) {
            return $search['origin'] . '-' . $search['destination'];
        })->countBy()->toArray();

        $total_count = array_sum($counts);

        $top_5_formatted = collect($counts)
        ->map(function ($count) use ($total_count) {
            return (int) round(($count / $total_count) * 100);
        })
        ->filter(function ($percentage) {
            return $percentage > 0;
        })
        ->toArray();
        arsort($top_5_formatted);
        $top_5_formatted = array_slice($top_5_formatted, 0, 24, true);
        /*****************Recent Searches*********************/
        return view('admin.dashboard.dashboard', compact('data','top_5_formatted','recent_bookings'));
    }
    public function bookingByStatus(Request $request){
        $order = Order::query();
        if (auth('admin')->user()->type == 'Travel Agent') {
            $order->where('agency_id', auth('admin')->user()->travel_agency_id);
        }
        $recent_bookings = $order->select('ref_key','pnrCode','api','status','pnr_status','agency_id','user_id','final_data','created_at','issued_at')
                ->orderBy('id', 'desc')
                ->where('status',$request->status)
                ->get()->take(10);
        $html = view('admin.dashboard.includes.recent-booking',compact('recent_bookings'))->render();
        return json_encode(['message' => 'success', 'html' => $html]);
    }
    public function logout(){
        Auth::guard('admin')->logout();
        return redirect('/admin');
    }
    public function logoutOnTabClose(Request $request){
        Auth::guard('admin')->logout();
        return response()->json(['status' => 'Logged out']);
    }
    
}
