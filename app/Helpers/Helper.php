<?php

use App\Models\Order;
use App\Models\TravelAgency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;


function CityNameByAirportCode($code){
    $path = "assets/json_data/airports.json";
    $airports = File::get($path);
    if (!File::exists($path)) {
        throw new Exception("Invalid File");
    }
    $allairports = json_decode($airports,true);
    // Capitalize the $code
    $code = strtoupper($code);
    $filteredArray = array_filter($allairports, function ($element) use ($code) {
        return $element["code"] === $code;
    });
    
    if (!empty($filteredArray)) {
        $filteredElement = reset($filteredArray);
        return $filteredElement['city'];
    } else {
        return '';
    }
}
function AirportByCode($code){
    $path = "assets/json_data/airports.json";
    $airports = File::get($path);
    if (!File::exists($path)) {
        throw new Exception("Invalid File");
    }
    $allairports = json_decode($airports,true);
    // Capitalize the $code
    $code = strtoupper($code);
    $filteredArray = array_filter($allairports, function ($element) use ($code) {
        return $element["code"] === $code;
    });
    
    if (!empty($filteredArray)) {
        $filteredElement = reset($filteredArray);
        return $filteredElement['name'];
    } else {
        return '';
    }
}

function AirlineNameByAirlineCode($code){
    $path = "assets/json_data/airlines.json";
    $file = File::get($path);
    if (!File::exists($path)) {
        throw new Exception("Invalid File");
    }

    $airlines = File::get($path);
    $allAirlines = json_decode($airlines, true);

    $filteredAirlines = array_filter($allAirlines, function ($air) use ($code) {
        return $air['code'] === $code;
    });

    if (!empty($filteredAirlines)) {
        $filteredAirline = reset($filteredAirlines);
        return $filteredAirline['name'];
    } else {
        return '';
    }
}
function AirCraftByCode($code){
    $path = "assets/json_data/aircrafts.json";
    $file = File::get($path);
    if (!File::exists($path)) {
        throw new Exception("Invalid File");
    }

    $aircrafts = File::get($path);
    $allAircrafts = json_decode($aircrafts, true);

    $filteredAircrafts = array_filter($allAircrafts, function ($air) use ($code) {
        return $air['AircraftCode'] === $code;
    });

    if (!empty($filteredAircrafts)) {
        $filteredAircraft = reset($filteredAircrafts);
        return $filteredAircraft['AircraftName'];
    } else {
        return '';
    }
}
function AllAirlines(){
    $path = "assets/json_data/airlines.json";
    $file = File::get($path);
    if (!File::exists($path)) {
        throw new Exception("Invalid File");
    }

    $airlines = File::get($path);
    return json_decode($airlines, true);
}

function Countries(){
    $path = "assets/json_data/countries.json"; // ie: /var/www/laravel/app/storage/json/filename.json
    $file = File::get($path);
    if (!File::exists($path)) {
        throw new Exception("Invalid File");
    }
    $countries = File::get($path);
    return json_decode($countries);
}

function countryDialCodes(){
    $path  = "assets/json_data/CountryCodes.json"; // ie: /var/www/laravel/app/storage/json/filename.json
    if (!File::exists($path)) {
        throw new Exception("Invalid File");
    }
    $countries = File::get($path);
    $allCountries = json_decode($countries, true);
    return $allCountries;
}
function countryDialCode($name){
    $path  = "assets/json_data/CountryCodes.json"; // Path to your JSON file
    if (!File::exists($path)) {
        throw new Exception("Invalid File");
    }
    $countries = File::get($path);
    $allCountries = collect(json_decode($countries, true));

    $country = $allCountries->first(function ($country) use ($name) {
        return $country['name'] === $name;
    });

    return $country ? $country['dial_code'] : null;
}
// =============================Blinking===================================\\
function flightCardBlinking($count){
    $cardsHtml = '';
    for ($i = 1; $i <= $count; $i++){
        $cardsHtml .= '<a class="flight-card card text-reset">
        <div class="card-body">
            <!-- Segment -->
            <div class="row g-3 align-items-center">
                <div class="col-md-12">
                    <div class="row g-3 gy-4 gy-md-3">
                        <div class="col-lg-6 col-md-6 col-12 order-md-2">
                            <div class="row g-2 align-items-center">
                                <div class="col">
                                    <h4 class="fw-semibold mb-0">
                                        <div class="uitk-skeleton-animation" role="presentation" style="width:80%"></div>
                                    </h4>
                                    <div class="uitk-skeleton-animation" role="presentation" style="width:80%"></div>
                                    <h6 class="mb-0">
                                        <div class="uitk-skeleton-animation" role="presentation" style="width:80%"></div>
                                    </h6>
                                </div>
                                <div
                                    class="col-6 col-lg-7 route d-flex flex-column justify-content-center align-items-center">
                                    <p class="small mb-0">
                                        <div class="uitk-skeleton-animation" role="presentation" style="width:25%"></div>
                                    </p>
                                    <div class="route-line-wrapper w-100 d-flex justify-content-between align-items-center">
                                        <div class="uitk-skeleton-animation" role="presentation" style="width:100%"></div>
                                    </div>
                                    <div class="hstack gap-2  mx-auto">
                                        <p class="small lh-sm m-0">&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                        <div class="uitk-skeleton-animation" role="presentation" style="width:100%"></div>
                                        <p class="small lh-sm mb-0">&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                    </div>
                                </div>
                                <div class="col text-end">
                                    <h4 class="fw-semibold mb-0">
                                        <div class="uitk-skeleton-animation" role="presentation" style="width:80%"></div>
                                    </h4>
                                    <div class="uitk-skeleton-animation" role="presentation" style="width:80%"></div>
                                    <h6 class="mb-0">
                                        <div class="uitk-skeleton-animation" role="presentation" style="width:80%"></div>
                                    </h6>
                                </div>
                            </div>
                        </div>
                        <div
                            class="col-lg-3 col-md-3 col-8 d-flex align-items-center order-md-1">
                            <div class="airline-logo me-lg-4">
                                <div class="uitk-skeleton-animation" role="presentation" style="width:100%; height:50px;"></div>
                            </div>
                            <div class="airline-detail">
                                <h5 class="mb-0">
                                    <div class="uitk-skeleton-animation" role="presentation" style="width:100%"></div>
                                </h5>
                                <p class="uitk-skeleton-animation" role="presentation" style="width:100%"></p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-4 text-end order-md-3">
                            <p class="uitk-skeleton-animation" role="presentation" style="width:30%"></p>
                            <p class="uitk-skeleton-animation" role="presentation" style="width:30%"></p>
                            <p class="uitk-skeleton-animation" role="presentation" style="width:30%"></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Segment -->
        </div>
        <div class="card-footer d-flex align-items-center justify-content-between py-2">
            <p class="uitk-skeleton-animation" role="presentation" style="width:10%"></p>
            <p class="uitk-skeleton-animation" role="presentation" style="width:10%"></p>
        </div>
    </a>';
    }
    return $cardsHtml;
}


function flightFilterBlinking(){
    return '<div class="collapse collapse-horizontal filters d-lg-block card card-body" id="filters">
                <form>
                    <div class="row g-3">
                        <div class="col-5 col-lg-6">
                            <h5 class="mb-0">Filter</h5>
                        </div>
                        <div class="col-5 col-lg-6 text-end">
                            <button type="reset"
                                class="fw-bold bg-transparent text-primary border-0 p-0">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </button>
                        </div>
                        <div class="col-2 d-block d-lg-none text-end">
                            <button type="button" class="btn-close" data-bs-toggle="collapse"
                                data-bs-target="#filters" aria-expanded="false"
                                aria-controls="filters"></button>
                        </div>
                    </div>
                    <hr class="border-3">
                    
                    <!-- Price -->
                    <div class="price-filter">
                        <h6>Price</h6>
                        <div class="row">
                            <div class="col-6">
                                <div class="price-value">
                                    <div class="uitk-skeleton-animation" role="presentation" style="width:80%"></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="price-value">
                                    <div class="uitk-skeleton-animation" role="presentation" style="width:80%"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div id="price-range"></div>
                            </div>
                        </div>
                    </div>
                    <hr class="border-0">
                    <!-- Stops -->
                    <div class="stops-filter">
                        <h6>Stops</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="uitk-skeleton-animation mb-2" role="presentation" style="width:30%"></div>
                                <div class="uitk-skeleton-animation mb-2" role="presentation" style="width:30%"></div>
                                <div class="uitk-skeleton-animation mb-2" role="presentation" style="width:30%"></div>
                            </div>
                        </div>
                    </div>
                    <hr class="border-0">
                    <!-- Airline -->
                    <div class="airline-filter">
                        <h6>Airlines</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="uitk-skeleton-animation mb-2" role="presentation" style="width:30%"></div>
                                <div class="uitk-skeleton-animation mb-2" role="presentation" style="width:30%"></div>
                                <div class="uitk-skeleton-animation mb-2" role="presentation" style="width:30%"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>';
}
function flightCardBlinkingFront($count){
    $cardsHtml = '';
    for ($i = 1; $i <= $count; $i++){
        $cardsHtml .= '<div class="result-flight-item mb-3">
        <div class="flight-item-wrapper">
            <div class="flight-details">
                <div class="flight-brand-info">
                    <p></p>
                    <p></p>
                    <h6>
                        <div class="uitk-skeleton-animation" role="presentation" style="width:100%"></div>
                    </h6>
                </div>
                <div class="flight-details-location justify-content-center d-flex flex-column text-center">
                    <h6>
                        <div class="uitk-skeleton-animation" role="presentation" style="width:60%"></div>
                    </h6>
                    <h6>
                        <div class="uitk-skeleton-animation" role="presentation" style="width:60%"></div>
                    </h6>
                </div>
                <div class="flight-details-duration ps-3 pe-3 justify-content-center d-flex flex-column">
                    
                    <h6>
                        <div class="uitk-skeleton-animation" role="presentation" style="width:100%"></div>
                    </h6>

                </div>
                <div class="flight-details-location d-flex flex-column justify-content-center">
                    <h6>
                        <div class="uitk-skeleton-animation" role="presentation" style="width:60%"></div>
                    </h6>
                    <h6>
                        <div class="uitk-skeleton-animation" role="presentation" style="width:60%"></div>
                    </h6>
                </div>
                <div class="flight-book-actions d-flex justify-content-center flex-column">
                    <p></p>
                    <h6>
                        <div class="uitk-skeleton-animation" role="presentation" style="width:80%;height: 35px;border-radius: 40px !important;"></div>
                    </h6>
                </div>
            </div>
        </div>
    </div>';
    }
    return $cardsHtml;
}
function flightModalBlinkingFront($count){
    $cardsHtml = '';
    for ($i = 1; $i <= $count; $i++){
        $cardsHtml .= '<div class="details-content flight-details-content details content-active">

        <div class="details-date-info" style="width: 130px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" fill="none">
                <g clip-path="url(#clip0)">
                    <path d="M25.0391 2.12324H24.1391V0.773241C24.1391 0.524729 23.9376 0.323242 23.6891 0.323242C23.4405 0.323242 23.2391 0.524729 23.2391 0.773241V2.12324H19.6391V0.773241C19.6391 0.524729 19.4376 0.323242 19.1891 0.323242C18.9406 0.323242 18.7391 0.524729 18.7391 0.773241V2.12324H15.1391V0.773241C15.1391 0.524729 14.9376 0.323242 14.6891 0.323242C14.4406 0.323242 14.2391 0.524729 14.2391 0.773241V2.12324H10.6391V0.773241C10.6391 0.524729 10.4376 0.323242 10.1891 0.323242C9.94057 0.323242 9.73908 0.524729 9.73908 0.773241V2.12324H6.13909V0.773241C6.13909 0.524729 5.9376 0.323242 5.68909 0.323242C5.44058 0.323242 5.23909 0.524729 5.23909 0.773241V2.12324H4.33909C3.0971 2.1247 2.09056 3.13124 2.0891 4.37323V15.3775C-0.354227 18.1384 -0.304558 22.3024 2.20385 25.0043C2.32872 25.144 2.46125 25.2766 2.60075 25.4016L2.6048 25.4057H2.6075C5.30867 27.9154 9.47319 27.9662 12.2348 25.5231H25.0391C26.281 25.5217 27.2876 24.5151 27.289 23.2731V4.37318C27.2875 3.13118 26.281 2.1247 25.0391 2.12324ZM11.7899 24.744C9.36761 26.9851 5.628 26.9826 3.20864 24.7383C3.089 24.6309 2.97526 24.517 2.86799 24.3972C0.500097 21.8378 0.655346 17.8433 3.21477 15.4754C5.7742 13.1074 9.76873 13.2627 12.1367 15.8222C14.5046 18.3816 14.3494 22.3761 11.7899 24.744ZM26.389 23.2732C26.389 24.0188 25.7846 24.6232 25.0391 24.6232H13.1051C13.16 24.5552 13.2081 24.4828 13.2599 24.413C13.3116 24.3433 13.3562 24.2861 13.4016 24.2204C13.502 24.076 13.5956 23.927 13.6847 23.7754C13.7108 23.7304 13.74 23.6894 13.7648 23.6449C13.8758 23.4475 13.977 23.2451 14.0685 23.0378C14.0919 22.9852 14.1108 22.9307 14.1329 22.8776C14.1981 22.721 14.2593 22.5626 14.3129 22.402C14.3385 22.3264 14.3606 22.2499 14.3831 22.1734C14.4281 22.0289 14.4654 21.8831 14.4992 21.736C14.5176 21.6554 14.5356 21.5753 14.5518 21.4939C14.5815 21.34 14.6054 21.1843 14.6252 21.0277C14.6342 20.9557 14.6459 20.8841 14.6531 20.8117C14.6751 20.5835 14.6891 20.354 14.6891 20.1232C14.6843 16.1487 11.4636 12.9279 7.48909 12.9232C7.25824 12.9232 7.02874 12.9372 6.80059 12.9592C6.72814 12.9664 6.65659 12.9781 6.58414 12.9871C6.42799 13.0074 6.27274 13.0321 6.11839 13.0605C6.03694 13.0767 5.95639 13.0947 5.87584 13.1131C5.72914 13.147 5.58407 13.1856 5.44069 13.2288C5.36329 13.2517 5.28589 13.2738 5.20939 13.2994C5.05144 13.3525 4.89439 13.4128 4.74229 13.4763C4.68559 13.4997 4.62844 13.5213 4.57264 13.5442C4.36744 13.6342 4.16764 13.7355 3.97055 13.8457C3.9206 13.8736 3.87335 13.906 3.82385 13.9357C3.6785 14.0221 3.5354 14.1117 3.3959 14.2057C3.32705 14.2534 3.2609 14.3047 3.1934 14.3547C3.1259 14.4046 3.05525 14.4532 2.9891 14.5072V7.52323H26.389V23.2732ZM26.389 6.62323H2.9891V4.37323C2.9891 3.62764 3.5935 3.02324 4.33909 3.02324H5.23909V4.37323C5.23909 4.62175 5.44058 4.82323 5.68909 4.82323C5.9376 4.82323 6.13909 4.62175 6.13909 4.37323V3.02324H9.73908V4.37323C9.73908 4.62175 9.94057 4.82323 10.1891 4.82323C10.4376 4.82323 10.6391 4.62175 10.6391 4.37323V3.02324H14.2391V4.37323C14.2391 4.62175 14.4406 4.82323 14.6891 4.82323C14.9376 4.82323 15.1391 4.62175 15.1391 4.37323V3.02324H18.7391V4.37323C18.7391 4.62175 18.9406 4.82323 19.1891 4.82323C19.4376 4.82323 19.6391 4.62175 19.6391 4.37323V3.02324H23.2391V4.37323C23.2391 4.62175 23.4405 4.82323 23.6891 4.82323C23.9376 4.82323 24.1391 4.62175 24.1391 4.37323V3.02324H25.0391C25.7846 3.02324 26.389 3.62764 26.389 4.37323V6.62323Z" fill="#555555"/>
                    <path d="M7.48886 15.1729C7.24035 15.1729 7.03886 15.3743 7.03886 15.6229V19.6728H4.78887C4.54035 19.6728 4.33887 19.8743 4.33887 20.1228C4.33887 20.3714 4.54035 20.5728 4.78887 20.5728H7.48886C7.73737 20.5728 7.93886 20.3714 7.93886 20.1228V15.6229C7.93886 15.3743 7.73737 15.1729 7.48886 15.1729Z" fill="#555555"/>
                </g>
                <defs>
                    <clipPath id="clip0">
                        <rect width="27" height="27" fill="white" transform="translate(0.289062 0.322266)"/>
                    </clipPath>
                </defs>
            </svg>
            <div>
                <p class="uitk-skeleton-animation mb-2" role="presentation" style="width:100%"></p>
                <p class="uitk-skeleton-animation" role="presentation" style="width:100%"></p>
            </div>
        </div>
    
        <div class="details-flight-step">
            <p class="uitk-skeleton-animation mb-2" role="presentation" style="top:20px;width:30px;block-size: 2.75rem;"></p>
            <ul class="blink">
                <p class="uitk-skeleton-animation mb-4" role="presentation" style="width:70%"></p>
                
                <p class="uitk-skeleton-animation mb-2" role="presentation" style="width:25%"></p>
                <p class="uitk-skeleton-animation mb-3" role="presentation" style="width:30%"></p>
                <p class="uitk-skeleton-animation mb-2" role="presentation" style="width:60%"></p>
            </ul>
            <div class="flight-details_luggage-info">
                <p class="uitk-skeleton-animation mb-2" role="presentation" style="width:25%"></p>
                <p class="uitk-skeleton-animation mb-2" role="presentation" style="width:25%"></p>
            </div>
        </div>
    </div>';
    }
    return $cardsHtml;
}
function adminUser(){
    return Auth::guard('admin')->user();
}
// =========================Credit Limit checking===========================\\
function checkCreditLimit($flightFare){
    
    if(Auth::guard('admin')->user()->type != 'admin' && Auth::guard('admin')->user()->type != 'Admin User') {
        $travel_agency_id = Auth::guard('admin')->user()->travel_agency_id;
        $agency = TravelAgency::find($travel_agency_id);
        
        $totalCredit = $agency->creditLimits()->sum('price');
        $usedCredit = Order::where('agency_id', $agency->id)->where('status','Ticketed')->sum('userPricingEnginePrice');
        $remaining = $totalCredit - $usedCredit;
        if($remaining < $flightFare){
            return "Low Credit Limit";
        }
    }else{
        return '';
    }
}
function creditLimitAssigned(){
    
    if(Auth::guard('admin')->user()->type != 'admin' && Auth::guard('admin')->user()->type != 'Admin User') {
        $travel_agency_id = Auth::guard('admin')->user()->travel_agency_id;
        $agency = TravelAgency::find($travel_agency_id);
        
        $totalCredit = $agency->creditLimits()->sum('price');
        $usedCredit = Order::where('agency_id', $agency->id)->where('status','Ticketed')->sum('userPricingEnginePrice');
        $remaining = $totalCredit - $usedCredit;
        return [
            'totalCredit' => number_format((int)$totalCredit),
            'usedCredit' => number_format((int)$usedCredit),
            'remaining' => number_format((int)$remaining),
        ];
    }else{
        return '';
    }
}
function agencyCreditLimit($agency_id){
    
    if(@$agency_id) {
        $agency = TravelAgency::find($agency_id);
        
        $totalCredit = $agency->creditLimits()->sum('price');
        $usedCredit = Order::where('agency_id', $agency->id)->where('status','Ticketed')->sum('userPricingEnginePrice');
        $remaining = $totalCredit - $usedCredit;
        return [
            'totalCredit' => number_format((int)$totalCredit),
            'usedCredit' => number_format((int)$usedCredit),
            'remaining' => number_format((int)$remaining),
        ];
    }else{
        return '';
    }
}
function capitalizeAlphabetic($input) {
    return preg_replace_callback('/[a-z]/', function($matches) {
        return strtoupper($matches[0]);
    }, $input);
}
