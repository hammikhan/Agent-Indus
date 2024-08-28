<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TravelAgency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ProfileController extends Controller
{
    public function userProfile(){
        if(Auth::guard('admin')->user()->type != 'admin' && Auth::guard('admin')->user()->type != 'Admin User') {
            $agency = TravelAgency::find(adminUser()->travel_agency_id);
            $totalCredit = $agency->creditLimits()->sum('price');
            $usedCredit = Order::where('agency_id', $agency->id)->where('status','Ticketed')->sum('userPricingEnginePrice');
            return view('admin.auth.profile',compact('agency','totalCredit','usedCredit'));
        }
        return view('admin.auth.profile');
    }
    public function userProfileUpdateImage(Request $request){
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);
    
        $adminUser = Auth::guard('admin')->user();
        if ($adminUser) {
            if ($adminUser->profile_image) {
                $previousImagePath = public_path($adminUser->profile_image);
    
                if (file_exists($previousImagePath)) {
                    unlink($previousImagePath);
                }
            }
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('admin_images'), $imageName);
    
            $adminUser->profile_image = 'admin_images/'.$imageName;
            if($adminUser->save()){
                // activityLogs('Pricing Rule has been created', $pricing,null);
                session()->flash('success', __("Profile image has been updated"));
                return redirect()->route('admin.users.profile');
            }else{
                session()->flash('Error', __("Profile image has not been updated"));
                return redirect()->route('admin.users.profile');
            }
        }
    }
    public function updateBio(Request $request){
        $adminUser = Auth::guard('admin')->user();
        if ($adminUser) {
            $adminUser->first_name = $request->first_name;
            $adminUser->last_name = $request->last_name;
            $adminUser->city = $request->city;
            $adminUser->country = $request->country;
            $adminUser->address = $request->address;
            if($adminUser->save()){
                // activityLogs('Pricing Rule has been created', $pricing,null);
                session()->flash('success', __("Profile info has been updated"));
                return redirect()->route('admin.users.profile');
            }else{
                session()->flash('Error', __("Profile info has not been updated"));
                return redirect()->route('admin.users.profile');
            }
        }
    }
    public function checkOldPassword(Request $request){
        $adminUser = Auth::guard('admin')->user();

        if (Hash::check($request->old_password, $adminUser->password)) {
            return response()->json(['status' => 200, 'message' => 'Old password is correct']);
        } else {
            return response()->json(['status' => 400, 'message' => 'Old password is incorrect']);
        }
    }
    
    public function changePassword(Request $request){
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|string|confirmed',
        ]);
    
        $adminUser = Auth::guard('admin')->user();
    
        if (!Hash::check($request->old_password, $adminUser->password)) {
            return redirect()->back()->with('error', 'Old password is incorrect');
        }
    
        $adminUser->password = Hash::make($request->new_password);
        if($adminUser->save()){
            // activityLogs('Pricing Rule has been created', $pricing,null);
            session()->flash('success', __("Password has successfuly changed"));
            return redirect()->route('admin.users.profile');
        }else{
            session()->flash('Error', __("Password has not been changed"));
            return redirect()->route('admin.users.profile');
        }
    }
}
