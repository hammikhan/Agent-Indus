<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AuthEmail;
use App\Mail\WelcomeEmail;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if(adminUser()){
            return redirect()->route('admin.dashboard');
        }else{
            return view('admin.auth.login');
        }
    }

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        $request->validate($rules);

        $admin = Admin::where('email', $request->email)
                  ->where('email_verified_at','!=',Null)
                  ->where('status', 1)
                  ->first();
        if (!$admin) {
            return redirect()->back()->with('error','Please verify you email first or contact to Support...');
        }
        $credentials = $request->only('email', 'password');
        if (Auth::guard('admin')->validate($credentials)) {
            
            $otp_code = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $remember_token = Str::random(40);
            $admin->otp_code = $otp_code;
            $admin->remember_token = $remember_token;
            $admin->save();
            /**
             * Sending Mail OTP
             */
            // $data = [
            //     'first_name' => $admin->first_name,
            //     'last_name' => $admin->last_name,
            //     'otp_code' => $otp_code,
            // ];
            // $otp_email = new AuthEmail($data);
            // $otp_email->with($data);
            // Mail::to($request->email)->send($otp_email);
            // ---------------

            return redirect()->route('admin.otp', ['refkey' => $remember_token]);
        }else{
            return redirect()->back()->with('error', 'Email or password incorrect');
        }

    }
    public function getOtp($refkey){
        $admin = Admin::where('remember_token', $refkey)->first();
        $email = $admin->email;
        return view('admin.auth.otp',compact('refkey','email'));
    }
    public function resendOtp(Request $request){
        $request->validate([
            'email' => 'required|email',
            'refkey' => 'required|string',
        ]);

        $admin = Admin::where('email', $request->email)
                    ->where('remember_token', $request->refkey)
                    ->first();
        if ($admin) {
            $otp_code = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $remember_token = Str::random(40);
            
            $admin->otp_code = $otp_code;
            $admin->save(); 
            /**
             * Sending Mail OTP
             */
            $data = [
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'otp_code' => $otp_code,
            ];
            $otp_email = new AuthEmail($data);
            $otp_email->with($data);
            Mail::to($request->email)->send($otp_email);
            // ---------------
            return response()->json(['success' => true, 'message' => 'otp resend successfuly ..... ']);
        } else {
            return response()->json(['success' => false, 'message' => 'not send some went wron......!']);
        }
    }
    public function submitOtp(Request $request){
        $rules = [
            'otp' => 'required'
        ];

        $request->validate($rules);
        
        $admin = Admin::where('remember_token', $request->refkey)->first();
        if ($admin && $admin->otp_code === $request->otp) {
            $admin->remember_token = '';
            $admin->save();
            Auth::guard('admin')->login($admin);
            return response()->json(['success' => true, 'redirect' => route('admin.dashboard')]);
        }
        return response()->json(['success' => false, 'error' => 'Invalid OTP code.']);
    }
    public function resetPassword($refkey){
        $admin = Admin::where('remember_token', $refkey)->first();
        if($admin){
            return view('admin.auth.reset-password',compact('refkey'));
        }
        return redirect()->route('admin.login')->with('error', 'Invalid Token.');

    }
    public function resetPasswordSubmit(Request $request){
        $admin = Admin::where('remember_token', $request->refkey)->first();

        $admin->password = Hash::make($request->password);
        $admin->remember_token = '';
        $admin->status = 1;
        $admin->email_verified_at = now();
        if($admin->save()){
            return redirect()->route('admin.login')->with('success', 'Password changed successfully.');
        }{
            return redirect()->back()->with('error', 'Some error....!');
        }
    }
    public function forgotPassword(){
        return view('admin.auth.forgot-password');
    }
    public function forgotPasswordSubmit(Request $request){
        $admin = Admin::where('email',$request->email)->first();
        $remember_token = Str::random(40);
        $admin->remember_token = $remember_token;

        if($admin->save()){
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

            return redirect()->back()->with('success', 'Email successfully sent please check your email...');
        }else{
            return response()->json(['error' => 'Not saved. Please try again later.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
