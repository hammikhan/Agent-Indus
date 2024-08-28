<?php

namespace App\Http\Controllers\Admin\Flight;

use App\Http\Traits\APIS\HititClass;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\SabreTrait as Sabre;
use App\Http\Traits\AmadeusTrait as Amadeus;
use App\Http\Traits\APIS\HititTrait as Hitit;
use App\Models\ApiOffer;
use App\Models\Customer;
use App\Models\PassengerProfile;
use App\Models\RecentSearch;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\PricingEngineTrait as PricingEngine;
use App\Models\Provider;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AdminFlightController extends Controller
{
    public function searchFlight(Request $request){
        $apis = Provider::where('status', '1')->pluck('identifier')->toArray();
        $RecentSearch = RecentSearch::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
            ->from('recent_searches')
            ->where('travelagent_id',Auth::guard('admin')->user()->id)
            ->groupBy(['origin', 'destination']);
        })
        ->orderBy('id', 'desc')
        ->take(5)
        ->get('data');
        return view('admin.flight.search',compact('RecentSearch','apis'));
    }
    public function emptypOldResponse(Request $request){
        $userId = Auth::guard('admin')->user()->id;
        $storagePath = 'Flights/searchResponse-' . $userId . '.json';

        Storage::put($storagePath,'');
        $prevRes = json_decode(Storage::get($storagePath), true);
        return json_encode(['status' => 'success']);
    }
    public function searchAvailability(Request $request){

        
        $thirtyMinutesAgo = Carbon::now()->subMinutes(30);
        $previousRecordsExist = ApiOffer::where('created_at', '<', $thirtyMinutesAgo)->exists();
        if ($previousRecordsExist) {
            ApiOffer::where('created_at', '<', $thirtyMinutesAgo)->delete();
        }
        $departDateSwiper = $request->departureDate;
        $requestData  = [
            'tripType' => $request->tripType,
            'origin' => strtoupper($request->origin),
            'destination' => strtoupper($request->destination),
            'departureDate' =>date('Y-m-d',strtotime($request->departureDate)),
            'origin2' => strtoupper($request->origin2),
            'destination2' => strtoupper($request->destination2),
            'departureDate2' =>date('Y-m-d',strtotime($request->departureDate2)),
            'returnDate' => date('Y-m-d',strtotime($request->returnDate)),
            'adults' => $request->adults,
            'children' => $request->children,
            'infants' => $request->infants,
            'stop' => false,
            'ticket_class' => $request->cabin,
        ];
        
        RecentSearch::create([
            'travelagent_id' => Auth::guard('admin')->user()->id,
            'origin' => strtoupper($request->origin),
            'destination' => strtoupper($request->destination),
            'data' => $requestData,
        ]);
        

        $userId = Auth::guard('admin')->user()->id;
        $storagePath = 'Flights/searchResponse-' . $userId . '.json';

        if (Storage::exists($storagePath)) {
            $prevRes = json_decode(Storage::get($storagePath), true);
        } else {
            $prevRes = [];
        }
        if ($request->api == 'sabre') {
            $resp = Sabre::lowFareSearch($requestData);
            if ($resp['status'] == 200) {
                $response = $resp['msg'];
                // return $response;
                $prevRes = is_array($prevRes) ? $prevRes : [];
                $prevRes = array_merge($prevRes, $response);
                Storage::put($storagePath, json_encode($prevRes,JSON_PRETTY_PRINT));
            }
        }
        if ($request->api == 'amadeus') {
            $resp = Amadeus::lowFareSearch((object)$request);
            if ($resp['status'] == 200) {
                $response = $resp['msg'];
                $prevRes = is_array($prevRes) ? $prevRes : [];
                $prevRes = array_merge($prevRes, $response);
                Storage::put($storagePath, json_encode($prevRes,JSON_PRETTY_PRINT));
            }
        }
        if ($request->api == 'hitit') {
            $resp = Hitit::lowFareSearch($requestData);
            if ($resp['status'] == 200) {
                $response = $resp['msg'];
                // return $response;
                $prevRes = is_array($prevRes) ? $prevRes : [];
                $prevRes = array_merge($prevRes, $response);
                Storage::put($storagePath, json_encode($prevRes,JSON_PRETTY_PRINT));
            }
        }
        
        
        // return $prevRes;

        if($resp['status'] == 200){
            $response = $resp;
            $results = array(
                'status' => 200,
                'msg' => $prevRes,
            );
            $asideFilter = view('admin.flight.includes.aside-filter',compact('results'))->render();
            $html = view('admin.flight.includes.search-availability',compact('results'))->render();
            $airSlider = view('admin.flight.includes.airline-slider-render',compact('results'))->render();
            $dateSwiper = view('admin.flight.includes.date-swiper',compact('departDateSwiper'))->render();
            return json_encode(['message' => 'success', 'html' => $html, 'filter' => $asideFilter, 'airSlider' => $airSlider, 'dateSwiper' => $dateSwiper, 'api' => $request->api]);
        }else{
            return json_encode(['message' => $resp['msg']]);
        }
    }
    public function flightDetail(Request $request){
        $api_offer = ApiOffer::where('ref_key', $request->ref_key)->first();
        $data = $api_offer->finaldata;

        $offerData = array(
            'ref_key' => $api_offer->ref_key,
            'finaldata' => json_decode(json_encode($data),true),
        );
        // dd($offerData);
        // return $offerData;
        $flightDetailHtml = view('admin.flight.includes.modal-result-render',compact('offerData'))->render();
        return json_encode(['message' => 'success', 'flightDetailHtml' => $flightDetailHtml]);
    }
    public function getFareRules(Request $request){
        $api_offer = ApiOffer::where('ref_key', $request->ref_key)->first();
        $provider = Provider::where('identifier',strtolower($api_offer->api))->first();
        $finaldata = $api_offer->finaldata;
        $response = Sabre::airRulesRQ($finaldata);
        // dd($response['Rules'][0]);
        $outbond = $response['Rules'][0];
        $ruleHtml1 = view('admin.flight.includes.fare-rule-render',compact('outbond'))->render();
        if(@$response['Rules'][1]){
            $inbound = $response['Rules'][1];
            $ruleHtml2 = view('admin.flight.includes.fare-rule-render',compact('inbound'))->render();
        }else{
            $ruleHtml2 = null;
        }

        return json_encode(['message' => 'success', 'ruleHtml1' => $ruleHtml1, 'ruleHtml2' => $ruleHtml2]);
    }
    
    public function getCustomerData(Request $request){
        if($request->type == 'customer'){
            $customer = Customer::with('passengerProfiles')->find($request->customer_id);
            
            $groupedPassengerProfiles = array();
            foreach ($customer->passengerProfiles as $passengerProfile) {
                $groupedPassengerProfiles[$passengerProfile->type][] = $passengerProfile;
            }
            $render = array();
            if(@$groupedPassengerProfiles['ADT']){
                $type = 'Adult';
                $passenger = $groupedPassengerProfiles['ADT'];
                $ADT = view('admin.passengers.includes.select-option-render',compact('passenger','type'))->render();
                $render['ADT'] = $ADT;
            }
            if(@$groupedPassengerProfiles['CNN']){
                $type = 'Child';
                $passenger = $groupedPassengerProfiles['CNN'];
                $CNN = view('admin.passengers.includes.select-option-render',compact('passenger','type'))->render();
                $render['CNN'] = $CNN;
            }
            if(@$groupedPassengerProfiles['INF']){
                $type = 'Infant';
                $passenger = $groupedPassengerProfiles['INF'];
                $INF = view('admin.passengers.includes.select-option-render',compact('passenger','type'))->render();
                $render['INF'] = $INF;
            }

            return ['status' => 200,'customerData' => $customer, 'render' => $render];
        }else{
            $passenger = PassengerProfile::find($request->customer_id);
            return ['status' => 200,'passenger' => $passenger];
        }
    }
    
    /**
     * Get Airport list
     */
    public function getAllAirPorts($q) {
        $path = "assets/json_data/airports.json";
        $airportsData = json_decode(file_get_contents($path), true);
    
        $filteredAirports = [];
        foreach ($airportsData as $airport) {
            if (stripos($airport['code'], $q) !== false ||
                stripos($airport['name'], $q) !== false ||
                stripos($airport['city'], $q) !== false) {
                $filteredAirports[] = [
                    'code' => $airport['code'],
                    'airport_name' => $airport['name'],
                    'city_name' => $airport['city']
                ];
            }
        }
        
        return response()->json($filteredAirports);
    }
}
