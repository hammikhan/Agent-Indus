<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\Admin;
use App\Models\Order;
use App\Models\PricingGroup;
use App\Models\TravelAgency;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class TravelAgencyController extends Controller
{
    public function agenciesList(){
        if(auth('admin')->user()->type == 'Travel Agent'){
            $agencies = TravelAgency::where('id',auth('admin')->user()->travel_agency_id)->with('pricingGroup')->orderBy('id', 'desc')->get();
        }else{
            $agencies = TravelAgency::with('pricingGroup')->orderBy('id', 'desc')->get();
        }
        $roles = Role::orderBy('id','asc')->get()->pluck('name')->except(0);
        $groups = PricingGroup::all();
        return view('admin.agencies.list',compact('agencies','roles','groups'));
    }
    public function agenciesStore(Request $request)
    {
        // dd($request->all());
        // $request->validate([
        //     'logo' => 'required|image|max:2048', // assuming logo is an image
        //     'name' => 'required|string|max:255',
        //     'phone' => 'required|string|max:255',
        //     'address' => 'nullable|string|max:255',
        //     'status' => 'required|in:active,inactive',
        //     'credit_limit' => 'required|numeric',
        // ]);

        // Upload logo
        $logoPath = 'agencies/'.time().'.'.$request->logo->extension();
        $request->logo->move(public_path('agencies'),$logoPath);

        // Create new travel agency
        $agency = new TravelAgency();
        $agency->name = $request->name;
        $agency->phone = $request->phone;
        $agency->address = $request->address;
        $agency->status = $request->status;
        $agency->pricing_group_id = $request->group;
        $agency->logo = $logoPath;
        $agency->save();
        $agency->creditLimits()->create([
            'price' => $request->credit_limit,
            'currency_type' => 'PKR',
            // 'created_by' => '',
            // 'updated_by' => '',
        ]);

        return redirect()->back()->with('success', 'Travel agency created successfully.');
    }
    public function viewAgency(TravelAgency $agency){
        $agency->load('pricingGroup');

        $groups = PricingGroup::all();
        
        if(auth('admin')->user()->type == 'Travel Agent'){
            if(auth('admin')->user()->travel_agency_id == $agency->id){
                return view('admin.agencies.agency-detail',compact('agency','groups'));
            }else{
                return view('404');
            }
        }else{
            return view('admin.agencies.agency-detail',compact('agency','groups'));
        }
    }
    public function agenciesUpdate(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string',
        ]);

        $agency = TravelAgency::findOrFail($request->key);

        $agency->name = $request->name;
        $agency->phone = $request->phone;
        $agency->address = $request->address;
        $agency->status = $request->status;
        $agency->pricing_group_id = $request->group;

        if ($request->hasFile('logo')) {
            $logoPath = 'agencies/'.time().'.'.$request->logo->extension();
            $request->logo->move(public_path('agencies'),$logoPath);
            $agency->logo = $logoPath;
        }
        $agency->save();
        return redirect()->back()->with('success', 'Agency details updated successfully.');
    }
    public function deleteAgency($agency){
        $agency = TravelAgency::find($agency);
        if($agency){
            $agency->delete();
            return redirect()->route('admin.agency.list')->with('success', 'Travel Agency deleted successfully.');
        }
    }
    /*********************************************************\
    |********************Agents Methods***********************|
    \*********************************************************/
    public function listAgents(TravelAgency $agency){
        if(auth('admin')->user()->type == 'Travel Agent'){
            if(auth('admin')->user()->travel_agency_id == $agency->id){
                $roles = Role::where('user_type','Agency User')->orderBy('id','asc')->get();
                $roles = $roles->pluck('name');
                $admin_users = Admin::where('type','Travel Agent')->where('travel_agency_id',$agency->id)->get();
                return view('admin.agencies.agents.list',compact('agency','admin_users','roles'));
            }else{
                return view('404');
            }
        }else{
            $roles = Role::where('user_type','Agency User')->orderBy('id','asc')->get();
            $roles = $roles->pluck('name');
            $admin_users = Admin::where('type','Travel Agent')->where('travel_agency_id',$agency->id)->get();
            return view('admin.agencies.agents.list',compact('agency','admin_users','roles'));
        }
    }
    public function storeAgent(Request $request){
        try {
            $rules = [
                'first_name' => 'required|string|max:255',
                'agency_id' => 'required|exists:travel_agencies,id',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:admins,email',
                'password' => 'required|string',
            ];

            $messages = [
                'agency_id.exists' => 'The selected agency is invalid.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->with('error', 'Some error....!');
            }
            $role = Role::where('name',$request->role_name)->first();
            $admin = new Admin();
            $admin->admin_id = auth()->guard('admin')->user()->id;
            $admin->first_name = $request->first_name;
            $admin->travel_agency_id = $request->agency_id;
            $admin->last_name = $request->last_name;
            $admin->email = $request->email;
            $admin->type = 'Travel Agent';
            $admin->password = Hash::make($request->password);
            
        
            $remember_token = Str::random(40);
            $admin->remember_token = $remember_token;

            if($admin->save()){
                $admin->assignRole($role,);
                // ----------------reset password email and welcome------------------
                $data = [
                    'first_name' => $admin->first_name,
                    'last_name' => $admin->last_name,
                    'url' => url("/admin/reset-password/{$remember_token}"),
                ];
                $welcome_email = new WelcomeEmail($data);
                $welcome_email->with($data);
                Mail::to($admin->email)->send($welcome_email);
                // ----------------reset password email and welcome------------------

                return redirect()->back()->with('success', 'Travel agent Saved successfully.');
            }else{
                return response()->json(['error' => 'Not saved. Please try again later.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred. Please try again later.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function editAgents(TravelAgency $agency, $agent_id){
        if(auth('admin')->user()->type == 'Travel Agent'){
            if(auth('admin')->user()->travel_agency_id == $agency->id){
                $agent = Admin::with('roles')->find($agent_id);
                $roles = Role::where('user_type','Agency User')->orderBy('id','asc')->get();
                $roles = $roles->pluck('name');
                return view('admin.agencies.agents.edit-agent',compact('agency','agent','roles'));
            }else{
                return view('404');
            }
        }else{
            $agent = Admin::with('roles')->find($agent_id);
            $roles = Role::where('user_type','Agency User')->orderBy('id','asc')->get();
            $roles = $roles->pluck('name');
            return view('admin.agencies.agents.edit-agent',compact('agency','agent','roles'));
        }
    }
    public function updateAgents(Request $request){

        $agent = Admin::where('type','Travel Agent')
                        ->where('id',$request->agent_id)
                        ->where('travel_agency_id',$request->agency_id)->first();
        $role = Role::where('name',$request->role_name)->first();

        $agent->first_name = $request->first_name;
        $agent->last_name = $request->last_name;
        $agent->status = $request->status;
        if(@$request->password){
            $agent->password = Hash::make($request->password);
        }
        if($agent->save()){
            $agent->roles()->detach();
            $agent->assignRole($role,);
            return redirect()->route('admin.agency.agents',['agency'=>$request->agency_id])
                    ->with('success', 'Travel agent Update successfully.');
        }else{
            return redirect()->back()->with('error', 'Travel agent not Update.......!');
        }
    }
    public function deleteAgents($agency, $agent){
        $admin = Admin::where('type','Travel Agent')
                ->where('travel_agency_id',$agency)
                ->where('id',$agent)
                ->first();
        if($admin){
            $admin->delete();
            return redirect()->route('admin.agency.agents',['agency'=>$agency])->with('success', 'Travel agent deleted successfully.');
        }
    }
    /*******************************************************\
    |* **************Credit Limit***************************|
    \*******************************************************/
    public function creditLimit(TravelAgency $agency){
        if(auth('admin')->user()->type == 'Travel Agent'){
            if(auth('admin')->user()->travel_agency_id == $agency->id){
                $totalCredit = $agency->creditLimits()->sum('price');
                $usedCredit = Order::where('agency_id', $agency->id)->where('status','Ticketed')->sum('userPricingEnginePrice');

                return view('admin.agencies.credit-limit.list',compact('agency','totalCredit','usedCredit'));
            }else{
                return view('404');
            }
        }else{
            $totalCredit = $agency->creditLimits()->sum('price');
            $usedCredit = Order::where('agency_id', $agency->id)->where('status','Ticketed')->sum('userPricingEnginePrice');

            return view('admin.agencies.credit-limit.list',compact('agency','totalCredit','usedCredit'));
        }
    }
    public function creditLimitStore(Request $request){
        $agency = TravelAgency::find($request->agency_id);
        $creditLimit = $agency->creditLimits()->create([
            'price' => $request->credit_limit,
            'currency_type' => 'PKR',
            // 'created_by' => '',
            // 'updated_by' => '',
        ]);
        return redirect()->to('admin/agency/'.$agency->id.'/credit-limit');
    }
}
