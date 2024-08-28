<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\Auth\ProfileController;
use App\Http\Controllers\Admin\Booking\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Flight\AdminFlightController;
use App\Http\Controllers\Admin\Flight\CheckoutController;
use App\Http\Controllers\Admin\PricingEngine\AgentPricingEngineController;
use App\Http\Controllers\Admin\PricingGroup\PricingGroupController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TravelAgencyController;
use App\Http\Controllers\Admin\TravelAgentController;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Contracts\Permission;

Route::fallback(function () {
    return response()->view('404', [], 404);
});

Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');
Route::get('/admin', [AuthController::class, 'showLoginForm'])->name('admin');
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::get('/admin/otp/{refkey?}', [AuthController::class, 'getOtp'])->name('admin.otp');
Route::post('/admin/otp-submit', [AuthController::class, 'submitOtp'])->name('admin.otp.submit');
Route::get('/admin/reset-password/{refkey?}', [AuthController::class, 'resetPassword'])->name('admin.reset.password');
Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('admin.resendOtp');
Route::post('/admin/reset-password-submit', [AuthController::class, 'resetPasswordSubmit'])->name('admin.reset.password.submit');
Route::get('/admin/forgot-password', [AuthController::class, 'forgotPassword'])->name('admin.forgot.password');
Route::post('/admin/forgot-password-submit', [AuthController::class, 'forgotPasswordSubmit'])->name('admin.forgot.password.submit');

Route::group(['middleware'=>'admin','prefix' => 'admin', 'as'=>'admin.'],function(){
    // Route::post('/logout-on-tab-close',[DashboardController::class,'logoutOnTabClose'])->name('admin.logout.on.tab.close');
    Route::get('/logout',[DashboardController::class,'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/recent-booking', [DashboardController::class, 'bookingByStatus'])->name('dashboard.recent.booking');
    // Route::get('/users', [UserController::class, 'userList'])->name('users')->middleware(['permission:Delete Users']);

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/users-profile','userProfile')->name('users.profile');
        Route::post('/update-profile-image','userProfileUpdateImage')->name('users.profile.image.update');
        Route::post('/update-bio','updateBio')->name('users.bio.update');
        Route::get('/check-pasword','checkOldPassword')->name('users.check.oldPassword');
        Route::post('/change-pasword','changePassword')->name('users.change.password');
    });
    //**********************Admin Users******************************
    Route::controller(UserController::class)->group(function () {
        Route::get('/users','userList')->name('users')->middleware(['auth:admin', 'permission:List-Users']);
        Route::post('/create-user','createUser')->name('user.create')->middleware(['auth:admin', 'permission:Add-Users']);
        Route::get('/delete-user/{user}','deleteUser')->name('user.delete')->middleware(['auth:admin', 'permission:Delete-Users']);
        Route::get('/view-user/{user}','viewUser')->name('user.view')->middleware(['auth:admin', 'permission:List-Users']);
        Route::post('/update-user','updateUser')->name('user.update')->middleware(['auth:admin', 'permission:Edit-Users']);
    });
    //******************User Group Routes*****************************
    Route::controller(RolePermissionController::class)->group(function () {
        Route::get('/roles','roleList')->name('roles')->middleware(['auth:admin', 'permission:List-Of-Roles']);
        Route::get('/create-role','createRole')->name('roles.create')->middleware(['auth:admin', 'permission:Create-Roles']);
        Route::get('/delete-role/{role_id}','deleteRole')->name('roles.delete')->middleware(['auth:admin', 'permission:Delete-Roles']);
        Route::post('/edit-role','editRole')->name('roles.edit')->middleware(['auth:admin', 'permission:Edit-Roles']);
        Route::post('/update-role','updateRole')->name('roles.update')->middleware(['auth:admin', 'permission:Edit-Roles']);
        // Route::get('/view-role/{admin_role}','viewUser')->name('roles.view');
    });
    //**********************Flight Routes******************************
    Route::controller(AdminFlightController::class)->group(function () {
        Route::get('/flight/search','searchFlight')->name('flight.search')->middleware(['auth:admin', 'permission:Availability-Search']);
        Route::get('/flight/search-new','searchFlightNew')->name('flight.searchNew')->middleware(['auth:admin', 'permission:Availability-Search']);
        Route::get('/flight/search-flight','searchAvailability')->name('flight.search.availability')->middleware(['auth:admin', 'permission:Availability-Search']);
        Route::get('/flight/search-empty','emptypOldResponse')->name('flight.search.empty.search')->middleware(['auth:admin', 'permission:Availability-Search']);
        // Route::get('/flight/search-flight2','searchAvailability2')->name('flight.search.availability2')->middleware(['auth:admin', 'permission:Availability-Search']);
        Route::post('/flight/flight-detail','flightDetail')->name('flight.detail')->middleware(['auth:admin', 'permission:Availability-Search']);
        // Route::post('/flight/flight-detail2','flightDetail2')->name('flight.detail2')->middleware(['auth:admin', 'permission:Availability-Search']);
        Route::post('/flight/fare-rules','getFareRules')->name('flight.fare.rule');
        Route::post('/get-customer-data','getCustomerData')->name('customer.data');
    });
    //**********************Checkout Routes******************************
    Route::controller(CheckoutController::class)->group(function(){
        Route::post('/flight/checkout','flightCheckout')->name('flight.checkout')->middleware(['auth:admin', 'permission:Book-PNR']);
        Route::post('/flight/pnr','createPnr')->name('flight.create.pnr')->middleware(['auth:admin', 'permission:Book-PNR']);
        Route::post('/update-pnr','updatePNR')->name('update.pnr');
        Route::post('/issue-ticket','issueTicket')->name('issue.ticket')->middleware(['auth:admin', 'permission:Issue-PNR']);
    });
    Route::controller(BookingController::class)->group(function(){
        Route::get('/bookings','bookingList')->name('bookings');
        Route::get('/booking/{booking_ref}','bookingDetail')->name('create.booking');
        Route::post('/cancel-booking','cancelBooking')->name('cancel.booking')->middleware(['auth:admin', 'permission:Cancell-PNR']);
        Route::post('/void-ticket','voidTicket')->name('void.ticket')->middleware(['auth:admin', 'permission:Void-PNR']);
        Route::get('/reprice-pnr','repricePNR')->name('reprice.pnr');
        Route::post('/email-booking','emailBooking')->name('email.booking');
        Route::get('/pdf/{booking_ref}/{f}','bookingPDF')->name('generate.pdf');
    });
    //**********************Settings Routes******************************
    Route::controller(SettingController::class)->group(function () {
        Route::get('/setting','index')->name('setting')->middleware(['auth:admin', 'permission:Read-Settings']);
        Route::post('/setting/store-airline-discount','storeAirlineDiscount')->name('setting.store.discount')->middleware(['auth:admin', 'permission:Read-Settings']);
        Route::post('/setting/update-airline-discount','updateAirlineDiscount')->name('setting.update.discount')->middleware(['auth:admin', 'permission:Read-Settings']);
        Route::post('/setting/update-procider-status','changeProviderStatus')->name('setting.update.status')->middleware(['auth:admin', 'permission:Read-Settings']);
        Route::post('/setting/delete-airline-discount','deleteAirlineDiscount')->name('setting.delete.discount')->middleware(['auth:admin', 'permission:Read-Settings']);
        Route::get('/setting/provider/{provider}','ProviderDetail')->name('provider')->middleware(['auth:admin', 'permission:Read-Settings']);
        Route::post('/setting/store-exclude-airline/','StoreExcludeAirline')->name('provider.exclude.airline')->middleware(['auth:admin', 'permission:Read-Settings']);
        
        Route::get('/faqs','faqs')->name('faqs');
        Route::post('/faqs/store','faqStore')->name('faqs.store');
        Route::get('/faqs/edit/{faq}','faqEdit')->name('faqs.edit');
        Route::post('/faqs/update','faqUpdate')->name('faqs.update');
        Route::post('/faqs/delete','faqDelete')->name('faqs.delete');

        Route::get('/news','news')->name('news');
        Route::post('/news/create-update','newsUpdateOrCreate')->name('news.updateCreate');
        Route::get('/news/edit/{news}','newsEdit')->name('news.edit');
        Route::post('/news/delete','newsDelete')->name('news.delete');

        Route::get('/bank-accounts','bankAccounts')->name('bank.accounts');
        Route::post('/bank-accounts/create-update','accountsCreate')->name('bank.accounts.updateCreate');
        Route::post('/bank-accounts/update-update','accountUpdate')->name('bank.accounts.accountUpdate');
        Route::get('/bank-accounts/edit/{bankAccount}', 'accountsEdit')->name('bank.accounts.edit');
        Route::post('/bank-accounts/delete', 'accountsDelete')->name('bank.accounts.delete');
    });
    //**********************Settings Routes******************************
    Route::controller(TravelAgencyController::class)->group(function () {
        Route::get('/agency-list','agenciesList')->name('agency.list')->middleware(['auth:admin', 'permission:List-Of-Travel-Agencies']);
        Route::post('/agency-store','agenciesStore')->name('agency.store')->middleware(['auth:admin', 'permission:Create-Travel-Agency']);
        Route::get('/view-agency/{agency}','viewAgency')->name('agency.view')->middleware(['auth:admin', 'permission:List-Of-Travel-Agencies']);
        Route::post('/agency-update','agenciesUpdate')->name('agency.update')->middleware(['auth:admin', 'permission:Edit-Travel-Agency']);
        Route::get('/delete-agency/{agency}','deleteAgency')->name('agency.delete')->middleware(['auth:admin', 'permission:Delete-Travel-Agency']);
        // ----------------------------Agency Agents-----------------------------------
        Route::get('/agency-agents/{agency}','listAgents')->name('agency.agents')->middleware(['auth:admin', 'permission:List-Travel-Agents']);
        Route::get('/agency/{agency}/credit-limit','creditLimit')->name('agency.creditlimit')->middleware(['auth:admin', 'permission:List-Credit-Limit']);
        Route::post('/agency-agents/credit-limit/store','creditLimitStore')->name('agency.creditlimit.store')->middleware(['auth:admin', 'permission:Add-Credit-Limit']);
        Route::post('/agency-store-agents','storeAgent')->name('agency.store.agents')->middleware(['auth:admin', 'permission:Create-Travel-Agents']);
        Route::get('/agency-agents-edit/{agency}/{agent}','editAgents')->name('agency.agents.edit')->middleware(['auth:admin', 'permission:Edit-Travel-Agents']);
        Route::get('/agency-agents-delete/{agency}/{agent}','deleteAgents')->name('agency.agents.delete')->middleware(['auth:admin', 'permission:Delete-Travel-Agents']);
        Route::post('/agency-agents-update','updateAgents')->name('agency.agents.update')->middleware(['auth:admin', 'permission:Edit-Travel-Agents']);
    });
    //**********************Travel Agents******************************
    // Route::controller(TravelAgentController::class)->group(function () {
        //     // Route::get('/agents','listAgent')->name('agent');
        //     Route::post('/create-agent','createAgent')->name('agent.create');
        //     Route::get('/view-agent/{admin_user}','viewAgent')->name('agent.view');
        //     Route::get('/delete-agent/{agent_id}','deleteAgent')->name('agent.delete');
        // });
        
        //**********************Pricing groups******************************
    Route::controller(PricingGroupController::class)->group(function () {    
        Route::get('/pricing-groups','GroupList')->name('agent.pricing.group');
        Route::post('/pricing-groups/pricing-store','GroupStore')->name('agent.pricing.group.engine.store');
        Route::get('/pricing-groups/pricing-rules/{pricing_group}','ruleList')->name('agent.pricing.group.engine.rule');
        Route::get('/pricing-groups/get-rule-details/{rule}','getRuleDetails')->name('agent.pricing.group.engine.rule.detail');
        Route::post('/pricing-groups/pricing-rules-update','ruleUpdate')->name('agent.pricing.group.engine.rule.update');
        Route::get('/pricing-groups/get-rule-delete/{rule}','ruleDelete')->name('agent.pricing.group.engine.rule.delete');

    });
});
/****************************************************\
 *              Front End Routes                   * |
\****************************************************/
Route::controller(AdminFlightController::class)->group(function(){
    Route::get('getAllAirPortCodes/{q?}','getAllAirPorts')->name('getAllAirPorts');
});
Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    Artisan::call('route:clear');

    return "cache cleared...";
});
