<?php

namespace App\Http\Controllers\Admin\PricingEngine;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\PricingEngineTravelAgent;
use App\Models\Setting;
use Illuminate\Http\Request;

class AgentPricingEngineController extends Controller
{
    public function index(){
        $rulesList = PricingEngineTravelAgent::where('travel_agent_id')->with('api')->get();
        return view('admin.pricing-engine-agent.list',compact('rulesList'));
    }
    public function create(Admin $agent){
        $airports = DB::table('airports')->select('code', 'city')->get();
        $apis = Setting::where('type','api')->get();
        return view('admin.pricing-engine-agent.create',compact('apis','airports'));
    }
    public function store(Request $request){
        $data = [
                'description' => $request->description,
            ];
        
            $rulePurpose = $request->rulePurpose;
            $isMarkUpOrDiscount = ($rulePurpose == PricingEngineTravelAgent::$rulePurposeCast['Mark Up'] || $rulePurpose == PricingEngineTravelAgent::$rulePurposeCast['Discount']);

        if ($isMarkUpOrDiscount) {
            $data['destinations'] = $request->all_destination ? [] : $request->destination;
            $data['isAllDestinations'] = $request->all_destination ? 1 : 0;
        
            $data['origins'] = $request->all_origin ? [] : $request->origin;
            $data['isAllOrigins'] = $request->all_origin ? 1 : 0;
        
            $data['type'] = $request->type;
            $data['amount'] = $request->amount;
        }
        
        $isRouteMarkUpOrRoteDiscount = ($rulePurpose == PricingEngineTravelAgent::$rulePurposeCast['Route Mark Up'] || $rulePurpose == PricingEngineTravelAgent::$rulePurposeCast['Route Discount']);
        if ($isRouteMarkUpOrRoteDiscount) {
            $data['type'] = $request->type;
            $data['amount'] = $request->amount;
            $data['airline'] = $request->airlines ? $request->airlines : "";
            $data['origin'] = $request->origin;
            $data['destination'] = $request->destination;
        }
        
        // if ($request->rulePurpose == 5) {
        //     $data['airlines'] = implode(",", $request->airlines);
        // }
        
        // if ($request->rulePurpose == 6) {
        //     $data['excAirlines'] = implode(",", $request->excAirlines);
        // }

        $dataJson = json_encode($data);
        $pricing = new PricingEngineTravelAgent();
        $pricing->rule = PricingEngineTravelAgent::$rulePurpose[$request->rulePurpose];
        $pricing->api_id = $request->api_id;
        $pricing->data = $dataJson;
        $pricing->status = $request->status;
        if($pricing->save()){
            // activityLogs('Pricing Rule has been created', $pricing,null);
            session()->flash('success', __("Pricing Rule has been created"));
            return redirect()->route('admin.pricingEngine.list');
        }else{
            session()->flash('Error', __("Pricing Rule not saved...."));
            return redirect()->route('admin.pricingEngine.list');
        }
    }
    public function delete($rule_id){
        $price_rule = PricingEngineTravelAgent::where('id',$rule_id)->first();
        if($price_rule->delete()){
            session()->flash('success', __("Pricing Rule has been Delete"));
            return redirect()->route('admin.pricingEngine.list');
        }else{
            session()->flash('Error', __("Pricing Rule not Delete...."));
            return redirect()->route('admin.pricingEngine.list');
        }
    }
}
