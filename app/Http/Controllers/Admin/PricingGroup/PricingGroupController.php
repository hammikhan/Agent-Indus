<?php

namespace App\Http\Controllers\Admin\PricingGroup;

use App\Http\Controllers\Controller;
use App\Models\PricingEngineTravelAgent;
use App\Models\PricingGroup;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PricingGroupController extends Controller
{
    public function GroupList(){
        $pricingGroups = PricingGroup::all();
        $airports = DB::table('airports')->select('code', 'city')->get();
        $apis = Setting::where('type','api')->where('status',1)->get();
        return view('admin.pricing-group.pricing-group',compact('airports','apis','pricingGroups'));
    }
    public function GroupStore(Request $request) {
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'description' => 'nullable|string',
        //     'rulePurpose' => 'required|string',
        //     'api_id' => 'nullable|integer',
        //     'type' => 'nullable|string',
        //     'amount' => 'nullable|string',
        //     'all_destination' => 'nullable|boolean',
        //     'destination' => 'nullable|array',
        //     'all_origin' => 'nullable|boolean',
        //     'origin' => 'nullable|array',
        //     'airlines' => 'nullable|string',
        //     'status' => 'nullable|string',
        // ]);
        // dd($request->all());
        if($request->group_id != null){
            $group_id = $request->group_id;
        }else{
            $pricingGroup = PricingGroup::create(['name' => $request->input('name')]);
            $group_id = $pricingGroup->id;
        }
    
        $data = ['description' => $request->input('description')];
        $rule = $request->input('rulePurpose');
        $rulePurpose = PricingEngineTravelAgent::$rulePurpose;
        $rulePurposeCast = PricingEngineTravelAgent::$rulePurposeCast;
        // dd($rulePurposeCast['Mark Up'],$rulePurpose[(int)$rule]);
        // if (in_array($rule, [$rulePurposeCast['Mark Up'], $rulePurposeCast['Discount']])) {
            $data['destinations'] = $request->boolean('all_destination') ? [] : $request->input('destination');
            $data['isAllDestinations'] = $request->boolean('all_destination') ? 1 : 0;
            $data['origins'] = $request->boolean('all_origin') ? [] : $request->input('origin');
            $data['isAllOrigins'] = $request->boolean('all_origin') ? 1 : 0;
            $data['isAllAirline'] = $request->boolean('all_airline') ? 1 : 0;
            $data['airline'] = $request->input('airline', '');
        // }
    
        // if (in_array($rule, [$rulePurposeCast['Route Mark Up'], $rulePurposeCast['Route Discount']])) {
            //     $data['origin'] = $request->input('origin');
            //     $data['destination'] = $request->input('destination');
        // }
    
        $pricing = new PricingEngineTravelAgent([
            'pricing_group_id' => $group_id,
            'rule' => $rulePurpose[$rule],
            'airline' => $data['airline'] ?? null,
            'api_id' => $request->input('api_id'),
            'type' => $request->input('type'),
            'amount' => $request->input('amount'),
            'origin' => $data['origin'] ?? null,
            'destination' => $data['destination'] ?? null,
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'data' => json_encode($data),
        ]);
        if ($pricing->save()) {
            session()->flash('success', __("Pricing Rule has been created"));
        } else {
            session()->flash('error', __("Pricing Rule not saved...."));
        }
        // dd($request->all());
        if($request->group_id != null){
            return redirect()->route('admin.agent.pricing.group.engine.rule',[$request->group_id]);
        }else{
            return redirect()->route('admin.agent.pricing.group');
        }
    }
    
    public function ruleList(PricingGroup $pricing_group){
        $airports = DB::table('airports')->select('code', 'city')->get();
        $apis = Setting::where('type','api')->where('status',1)->get();
        return view('admin.pricing-group.rules.list',compact('airports','apis','pricing_group'));
    }
    public function getRuleDetails($id)
    {
        $rule = PricingEngineTravelAgent::find($id);
        $airports = DB::table('airports')->select('code', 'city')->get();
        $apis = Setting::where('type','api')->where('status',1)->get();

        if (!$rule) {
            return response()->json(['message' => 'Rule not found'], 404);
        }
        return view('admin.pricing-group.includes.edit-rule-modal', compact('rule','airports','apis'))->render();
    }
    public function ruleUpdate(Request $request) {
        // dd($request->all());
        // $request->validate([
            //     'name' => 'required|string|max:255',
            //     'description' => 'nullable|string',
            //     'rulePurpose' => 'required|string',
            //     'api_id' => 'nullable|integer',
            //     'type' => 'nullable|string',
            //     'amount' => 'nullable|string',
            //     'all_destination' => 'nullable|boolean',
            //     'destination' => 'nullable|array',
            //     'all_origin' => 'nullable|boolean',
            //     'origin' => 'nullable|array',
            //     'airlines' => 'nullable|string',
            //     'status' => 'nullable|string',
        // ]);
    
        // Find the existing PricingEngineTravelAgent by ID
        $pricing = PricingEngineTravelAgent::findOrFail($request->rule_id);
    
        // Update the PricingGroup if needed
        if ($request->has('name')) {
            $pricingGroup = PricingGroup::findOrFail($pricing->pricing_group_id);
            $pricingGroup->update(['name' => $request->input('name')]);
        }
    
        $data = ['description' => $request->input('description')];
        $rule = $request->input('rulePurpose');
        $rulePurpose = PricingEngineTravelAgent::$rulePurpose;
        $rulePurposeCast = PricingEngineTravelAgent::$rulePurposeCast;
    
        // if (in_array($rule, [$rulePurposeCast['Mark Up'], $rulePurposeCast['Discount']])) {
            $data['destinations'] = $request->boolean('all_destination') ? [] : $request->input('destination');
            $data['isAllDestinations'] = $request->boolean('all_destination') ? 1 : 0;
            $data['origins'] = $request->boolean('all_origin') ? [] : $request->input('origin');
            $data['isAllOrigins'] = $request->boolean('all_origin') ? 1 : 0;
            $data['isAllAirline'] = $request->boolean('all_airline') ? 1 : 0;
            $data['airline'] = $request->input('airline', '');
        // }
    
        // if (in_array($rule, [$rulePurposeCast['Route Mark Up'], $rulePurposeCast['Route Discount']])) {
            //     $data['origin'] = $request->input('origin');
            //     $data['destination'] = $request->input('destination');
        // }
    
        $pricing->update([
            'rule' => $rulePurpose[$rule],
            'airline' => $data['airline'] ?? $pricing->airline,
            'api_id' => $request->input('api_id'),
            'type' => $request->input('type'),
            'amount' => $request->input('amount'),
            'origin' => $data['origin'] ?? $pricing->origin,
            'destination' => $data['destination'] ?? $pricing->destination,
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'data' => json_encode($data),
        ]);
    
        session()->flash('success', __("Pricing Rule has been updated"));
        return redirect()->route('admin.agent.pricing.group.engine.rule',[$pricingGroup->id]);
    }
    
    public function ruleDelete(PricingEngineTravelAgent $rule){
        $pricing_group_id = $rule->pricing_group_id;
        if($rule->delete()){
            session()->flash('success', __("Pricing Rule has been deleted"));
            return redirect()->route('admin.agent.pricing.group.engine.rule',[$pricing_group_id]);
        }else{
            session()->flash('success', __("Pricing Rule not found..."));
            return redirect()->route('admin.agent.pricing.group.engine.rule',[$pricing_group_id]);
        }
        
    }
}
