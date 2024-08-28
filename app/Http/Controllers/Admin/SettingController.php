<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AirlineDiscount;
use App\Models\BankAccount;
use App\Models\Faq;
use App\Models\News;
use App\Models\Provider;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index(){
        $providers = Provider::all();
        return view('admin.setting.index',compact('providers'));
    }
    public function storeAirlineDiscount(Request $request){
        $provider = Provider::where('identifier',$request->source)->first();
        $airDiscounts = new AirlineDiscount();
        $airDiscounts->airline = $request->airline;
        $airDiscounts->provider = $request->source;
        $airDiscounts->provider_id = $provider->id;
        $airDiscounts->discount = $request->discount;
        $airDiscounts->departure_codes = $request->from;
        if($airDiscounts->save()){
            return redirect()->back()->with('success','Airline discount saved successfull');
        }else{
            return redirect()->back()->with('error','Airline discount not saved');
        }
    }
    public function updateAirlineDiscount(Request $request){
        $airDiscounts = AirlineDiscount::find($request->id);
        $airDiscounts->airline = $request->airline;
        $airDiscounts->provider = $request->source;
        $airDiscounts->discount = $request->discount;
        $airDiscounts->departure_codes = $request->from;
        if($airDiscounts->save()){
            return redirect()->back()->with('success','Airline discount updated successfull');
        }else{
            return redirect()->back()->with('error','Airline discount not updated');
        }
    }
    public function deleteAirlineDiscount(Request $request){
        $airDiscounts = AirlineDiscount::find($request->id);
        if($airDiscounts->delete()){
            return redirect()->back()->with('success','Airline discount deleted successfull');
        }else{
            return redirect()->back()->with('error','Airline discount not delete');
        }
    }
    public function changeProviderStatus(Request $request){
        $provider = Provider::findOrFail($request->id);
        $provider->status = !$provider->status;
        if($provider->save()){
            return ['status' => 'success','message' => 'Provider status updated successfull'];
        }else{
            return ['status' => 'error','message' => 'Provider status not updated'];
        }
    }
    public function editApi(Setting $api){
        return view('admin.setting.apis.edit',compact('api'));
    }
    public function updateApi(Request $request){
        try{
            DB::beginTransaction();
            $api = Setting::find($request->id);
            $api->data = $request->data;
            $api->save();
            DB::commit();
            return redirect()->route('admin.setting.apis')->with('success', 'API data updated successfully');
        } catch (\Exception $exception) {
            DB::rollback();
            if (env('APP_ENV') == "local") {
                return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
            }
        }
    }
    /********************************************************\
     *                  Provider methods                    *|
    \********************************************************/
    public function ProviderDetail($contec_provider){
        $provider = Provider::where('identifier',$contec_provider)->first();
        return view('admin.setting.providers.details',compact('provider'));
    }
    public function StoreExcludeAirline(Request $request){
        $provider = Provider::find($request->provider);
        if ($provider) {
            $provider->exclude_airlines = $request->exclude_airlines;
            $provider->save();
            return redirect()->route('admin.provider',[$provider->identifier])
                    ->with('success', 'Airline added to provider.');
        }else{
            return redirect()->back()->with('error', 'Provider not found...');
        }
        
    }
    /********************************************************\
     *                  FAQs methods                    *|
    \********************************************************/
    public function faqs(){
        $faqs = Faq::all();
        return view('admin.faqs.faqs', compact('faqs'));
    }
    public function faqStore(Request $request){
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ]);
    
        $faq = Faq::create([
            'question' => $request->input('question'),
            'answer' => $request->input('answer'),
            'sequence_number' => $request->input('sequence_number', null),
            'status' => $request->input('status', 1),
        ]);
    
        return redirect()->back()->with('success', 'FAQ created successfully!');
    }
    public function faqEdit(Faq $faq){
        if (!$faq) {
            return response()->json(['message' => 'Faq not found'], 404);
        }
        return view('admin.faqs.includes.edit-modal', compact('faq'))->render();
    }
    
    public function faqUpdate(Request $request){
        $faq = Faq::findOrFail($request->id);
        $faq->update([
            'question' => $request->input('question'),
            'answer' => $request->input('answer'),
            'status' => $request->input('status', 1),
        ]);
        session()->flash('success', __("Pricing Faq has been updated"));
        return redirect()->route('admin.faqs');
    }
    
    public function faqDelete(Request $request){
        $faq = Faq::findOrFail($request->id);
        if ($faq) {
            $faq->delete();
            return ['status' => 'success','message' => 'Faq has been deleted'];
        }
        return ['status' => 'error','message' => 'Faq not found'];
    }
    /********************************************************\
     *                  News methods                    *|
    \********************************************************/
    public function news(){
        if(auth('admin')->user()->type == 'admin'){
            $news = News::orderBy('id', 'desc')->get();
        }else{
            $news = News::orderBy('id', 'desc')->where('status',1)->get();
        }
        return view('admin.news.news', compact('news'));
    }
    public function newsUpdateOrCreate(Request $request)
    {
        $request->validate([
            'news' => 'required|string',
            'status' => 'required|boolean',
        ]);

        $news = News::updateOrCreate(
            ['id' => $request->input('id')],
            [
                'news' => $request->input('news'),
                'status' => $request->input('status'),
            ]
        );

        return redirect()->route('admin.news')->with('success', 'News item updated successfully.');
    }
    public function newsEdit(News $news){
        if (!$news) {
            return response()->json(['message' => 'Faq not found'], 404);
        }
        return view('admin.news.includes.edit-news', compact('news'))->render();
    }
    public function newsDelete(Request $request){
        $news = News::findOrFail($request->id);
        if ($news) {
            $news->delete();
            return ['status' => 'success','message' => 'News has been deleted'];
        }
        return ['status' => 'error','message' => 'News not found'];
    }
    /********************************************************\
     *                  Accounts methods                        *|
    \********************************************************/
    public function bankAccounts()
    {
        if (auth('admin')->user()->type == 'admin') {
            $bankAccounts = BankAccount::orderBy('id', 'desc')->get();
        } else {
            $bankAccounts = BankAccount::orderBy('id', 'desc')->where('status', 1)->get();
        }
        return view('admin.bank-accounts.index', compact('bankAccounts'));
    }

    public function accountsCreate(Request $request)
    {
        $request->validate([
            'account_title' => 'required|string',
            'branch_code' => 'required|string',
            'account_no' => 'required|string',
            'iban' => 'required|string',
            'status' => 'required',
        ]);

        $data = $request->only(['account_title', 'branch_code', 'account_no', 'iban', 'status']);

        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('accounts_images'), $imageName);
            $data['image'] = 'accounts_images/'.$imageName;
        }
        BankAccount::create(
            $data
        );

        return redirect()->route('admin.bank.accounts')->with('success', 'Bank account updated successfully.');
    }

    public function accountsEdit(BankAccount $bankAccount)
    {
        if (!$bankAccount) {
            return response()->json(['message' => 'Bank account not found'], 404);
        }
        return view('admin.bank-accounts.edit-render', compact('bankAccount'))->render();
    }
    public function accountUpdate(Request $request)
    {
        $request->validate([
            'account_title' => 'required|string',
            'branch_code' => 'required|string',
            'account_no' => 'required|string',
            'iban' => 'required|string',
            'status' => 'required',
        ]);
        $bankAccount = BankAccount::findOrFail($request->id);

        $data = $request->only(['account_title', 'branch_code', 'account_no', 'iban', 'status']);
        if ($request->hasFile('image')) {
            // if ($bankAccount->profile_image) {
            $previousImagePath = public_path($bankAccount->image);
            if (file_exists($previousImagePath)) {
                unlink($previousImagePath);
            }

            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('accounts_images'), $imageName);
            $bankAccount->image = 'accounts_images/'.$imageName;
        }

        $bankAccount->account_title = $request->account_title;
        $bankAccount->branch_code = $request->branch_code;
        $bankAccount->account_no = $request->account_no;
        $bankAccount->iban = $request->iban;
        $bankAccount->status = $request->status;

        if($bankAccount->save()){
            session()->flash('success', __("Bank account updated successfully."));
            return redirect()->route('admin.bank.accounts');
        }else{
            session()->flash('Error', __("Oops...! Bank account has not been updated."));
            return redirect()->route('admin.bank.accounts');
        }
    }
    public function accountsDelete(Request $request)
    {
        $bankAccount = BankAccount::findOrFail($request->id);
        if ($bankAccount) {
            $bankAccount->delete();
            return ['status' => 'success', 'message' => 'Bank account has been deleted'];
        }
        return ['status' => 'error', 'message' => 'Bank account not found'];
    }
}
