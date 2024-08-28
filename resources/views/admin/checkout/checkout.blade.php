@extends('admin.layouts.app')


@section('styles')
    <link href="{{ asset('assets/css/contact-profile2.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/flight-search.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .form-label {
            margin-bottom: 0px !important;
        }
        #price_detail,#flight_detail,#infant_detail, .accordion-button:not(.collapsed) {
            background-color: unset;
        }
        .accordion-button.collapsed {
            background-color: rgba(59,118,225,0.5)!important;
        }
        .accordion-body{
            font-weight: 500;
        }
        .gj-textbox-md {
            border: 1px solid #e2e5e8;
            border-bottom: 1px solid #e2e5e8;
            display: block;
            font-family: Helvetica,Arial,sans-serif;
            font-size: .875rem;
            line-height: 1.5;
            padding: .47rem .75rem;
            margin: 0;
        }
        .gj-textbox-md:active, .gj-textbox-md:focus {
            border-bottom: 1px solid #e2e5e8;
        }
        .bg-light-blue{
            background-color: rgba(59,118,225,0.5)!important;
        }
        /* ******************************************** */
        

        .wait {
            margin: 5rem 0;
        }
        .iata_code {
            font-size: 6rem;
            opacity:0.3;
            top: 15%;
            position: absolute;
            color: #14498d;
        }
        .departure_city {
            left: 0;
        }

        .arrival_city {
            right: 1.5rem;
        }

        .plane {
            position: absolute;
            margin: 0 auto;
            width: 100%;
        }

        .plane-img {
            -webkit-animation: spin 2.5s linear infinite;
            -moz-animation: spin 2.5s linear infinite;
            animation: spin 2.5s linear infinite;
        }

        @-moz-keyframes spin {
            100% {
                -moz-transform: rotate(360deg);
            }
        }

        @-webkit-keyframes spin {
            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }


        .earth-wrapper {
            position: absolute;
            margin: 0 auto;
            width: 100%;
            padding-top: 2.7rem;
        }

        .earth {
            width: 160px;
            height: 160px;
            background: url("https://zupimages.net/up/19/34/6vlb.gif");
            border-radius: 100%;
            background-size: 340px;
            animation: earthAnim 12s infinite linear;
            margin: 0 auto;
            border: 1px solid #CDD1D3;
        }

        @keyframes earthAnim {
            0% {background-position-x: 0;}
            100% {background-position-x: -340px;}
        }

        @media screen and (max-width: 420px) {
            .departure_city {
                left: 0;
                right: 0;
                top: 30%;
                position: absolute;
                margin: 0 auto;
            }
            
            .arrival_city {
                left: 0;
                right: 0;
                top: 93%;
                position: absolute;
                margin: 0 auto;
            }
        }
        .loader-container {
            position: relative;
            width: 100%;
            height: 100vh;
            z-index: 2000;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .loader {
            position: absolute;
            top: 70%;
            left: 50%;
            transform: translate(-50%, -50%); 
            text-align: center;
            width: 100%;
            overflow: hidden;
            max-width: 40rem;
            height: 100%;
            margin: 0 auto;
        }
        
    </style>
@endsection

@section('content')
<div class="loader-container d-none">
    <div class="loader">
        {{-- <div class="iata_code departure_city">CDG</div> --}}
        <div class="plane">
            <img src="https://zupimages.net/up/19/34/4820.gif" class="plane-img">
        </div>
        <div class="earth-wrapper">
            <div class="earth"></div>
        </div>  
        {{-- <div class="iata_code arrival_city">JFK</div> --}}
    </div>
</div>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid p-0">
            
            <div class="row">
                <div class="col-xl-8">
                    <div class="row">
                        @if(auth('admin')->user()->can('Book-PNR'))
                            <form action="#" id="checkoutForm" class="needs-validation" novalidate>
                                @csrf
                            @else
                                <form action="#" class="needs-validation" novalidate>
                            @endif
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-lg-8">
                                                <h4 class="card-title fs-4 fw-bold">Contact Details</h4>
                                            </div>
                                            <div class="col-lg-4">
                                                <select id="formrow-inputState" class="form-select" onchange="loadCustomerData(this,'customer')">
                                                    <option selected value="">Select Customer</option>
                                                    @foreach ($customers as $customer)
                                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        {{-- --------------Passenger Detail--------------- --}}
                                        <div class="row">
                                            <input type="hidden" name="customer_id" id="customer_id">
                                            <input type="hidden" name="itn_ref_key" value="{{ $itn_ref_key }}">
                                            @if(@$brand_ref_key)
                                                @foreach ($brand_ref_key as $key => $item)
                                                    <input type="hidden" name="brand_ref_key[{{$key}}]" value="{{ $item }}">
                                                @endforeach
                                            @endif
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="customer_name" class="form-label">Full Name</label>
                                                    <input type="text" name="customer_name" class="form-control" required placeholder="Enter Full Name" id="customer_name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="customer_email" class="form-label">Email</label>
                                                    <input type="email" name="customer_email" class="form-control" required placeholder="Enter Email" id="customer_email">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="customer_country" class="form-label">Country Code</label>
                                                <select name="customer_country" class="form-select" required id="customer_country">
                                                    <option selected value="">Select Country Code</option>
                                                    @foreach (countryDialCodes() as $cntry_code)
                                                    <option value="{{$cntry_code['name']}}">
                                                        {{-- <img src="{{ asset('/assets/media/flags/afghanistan.svg')}}" alt=""> --}}
                                                        {{$cntry_code['name']}} (+{{$cntry_code['dial_code']}})
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3" x-data>
                                                    <label for="customer_phone" class="form-label">Phone</label>
                                                    <input name="customer_phone" class="form-control" required x-mask="999 9999999" min="10" max="10" placeholder="345 1234567" id="customer_phone">
                                                </div>
                                            </div>
                                        </div>
                                        <br>
                                    </div>
                                    <!-- end card body -->
                                </div>
                            </div>
                            {{-------------------- Adults -----------------------}}
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-lg-8">
                                                <h4 class="card-title fs-4 fw-bold">Adults Details</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-2 p-sm-4">
                                        @php
                                            $i = 0;
                                        @endphp
                                        {{-------------------- Adults -----------------------}}
                                        @if(@$query['adults'] > 0)
                                            @for ($adt = 1; $adt <= $query['adults']; $adt++)
                                                <div class="passenger_adults rounded p-2 p-sm-4 border mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="adult_seletion_{{ $adt }}" class="form-label fs-5 fw-bold" style="line-height: 30px;font-weight: bold;">
                                                                Adult ({{$adt}})
                                                            </label>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <select id="adult_seletion_{{ $adt }}" name="passengers[{{ $i }}][id]" onchange="loadPassengerData(this,'adult',{{ $adt }})" disabled class="form-select adult_select_option">
                                                                
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                    <input type="hidden" name="passengers[{{ $i }}][id]" id="adult_id_{{ $adt }}">
                                                    <input type="hidden" name="passengers[{{ $i }}][passenger_type]" value="ADT">
                                                    <div class="col-md-2">
                                                        <label for="adult_title_{{ $adt }}" class="form-label">Title</label>
                                                        <select id="adult_title_{{ $adt }}" name="passengers[{{ $i }}][passenger_title]" onchange="selectGenderByTitle(this,'adult',{{ $adt }})" class="form-select" required>
                                                            <option value="Mr">Mr</option>
                                                            <option value="Ms">Ms</option>
                                                            <option value="Mrs">Mrs</option>
                                                        </select>
                                                    </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label for="adult_first_name_{{ $adt }}" class="form-label">First Name</label>
                                                                <input type="text" class="form-control" name="passengers[{{ $i }}][name]" required placeholder="Enter First Name" id="adult_first_name_{{ $adt }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label for="adult_last_name_{{ $adt }}" class="form-label">Last Name</label>
                                                                <input type="text" class="form-control" name="passengers[{{ $i }}][sur_name]" required placeholder="Enter Last Name" id="adult_last_name_{{ $adt }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label for="adult_gender_{{ $adt }}" class="form-label">Gender</label>
                                                            <select id="adult_gender_{{ $adt }}" name="passengers[{{ $i }}][passenger_gender]" class="form-select" required>
                                                                <option value="M">Male</option>
                                                                <option value="F">Female</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label for="adult_dob_{{ $adt }}" class="form-label">Date of Birth</label>
                                                                <input type="text" class="form-control date_dob" name="passengers[{{ $i }}][dob]" required autocomplete="off" placeholder="Enter Date of Birth" id="adult_dob_{{ $adt }}" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="adult_country_{{ $adt }}" class="form-label">Country</label>
                                                            <select id="adult_country_{{ $adt }}" name="passengers[{{ $i }}][nationality]" class="form-select" required>
                                                                <option value="">Select Country</option>
                                                                @foreach (Countries() as $country)
                                                                    <option value="{{ $country->code }}">{{ substr($country->name, 0, 35) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="adult_identity_{{ $adt }}" class="form-label">Identification</label>
                                                            <select id="adult_identity_{{ $adt }}" name="passengers[{{ $i }}][document_type]" onchange="changeIdentity(this,'adult',{{ $adt }})" class="form-select" required>
                                                                <option value="P">Passport</option>
                                                                <option value="RI">Resident Identity</option>
                                                                <option value="I">National ID</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row" id="adult_passport_Div{{ $adt }}">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="adult_passport_{{ $adt }}" class="form-label">Passport Number</label>
                                                                <input type="text" class="form-control" name="passengers[{{ $i }}][document_number]" required placeholder="Enter Passport Number" id="adult_passport_{{ $adt }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="adult_passport_expiry_{{ $adt }}" class="form-label">Passport Expiry</label>
                                                            <input type="text" class="form-control date_passport_expiry" name="passengers[{{ $i }}][document_expiry_date]" required autocomplete="off" placeholder="Enter Passport Expiry" id="adult_passport_expiry_{{ $adt }}" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="row" id="adult_identity_Div_{{ $adt }}" style="display: none;">
                                                        <div class="col-md-6">
                                                            <label for="adult_issue_country_{{ $adt }}" class="form-label">Issuing Country</label>
                                                            <select id="adult_issue_country_{{ $adt }}" name="passengers[{{ $i }}][country]" class="form-select">
                                                                <option value="">Select Country</option>
                                                                @foreach (Countries() as $country)
                                                                    <option value="{{ $country->code }}">{{ substr($country->name, 0, 35) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="adult_identity_number_{{ $adt }}" class="form-label">Identity Number</label>
                                                                <input type="text" class="form-control" name="passengers[{{ $i }}][identity_number]" placeholder="Enter Identity Number" id="adult_identity_number_{{ $adt }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @php
                                                    $i++;
                                                @endphp
                                            @endfor
                                        @endif
                                        {{-------------------- Adults -----------------------}}
                                    </div>
                                </div>
                            </div>
                            {{-------------------- Childs -----------------------}}
                            @if(@$query['children'] > 0)
                                <div class="col-xl-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="row">
                                                <div class="col-lg-8">
                                                    <h4 class="card-title fs-4 fw-bold">Childs Details</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body p-2 p-sm-4">
                                            
                                            @for ($cnn = 1; $cnn <= $query['children']; $cnn++)
                                                <div class="passenger_childs rounded p-2 p-sm-4 border mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="child_selection_{{$cnn}}" class="form-label fs-5 fw-bold" style="line-height: 30px;font-weight: bold;">Child ({{$cnn}})</label>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <select id="child_selection_{{$cnn}}" name="passengers[{{ $i }}][id]" onchange="loadPassengerData(this,'child',{{$cnn}})" disabled class="form-select child_select_option">
                                                                
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <input type="hidden" name="passengers[{{ $i }}][id]" id="child_id_{{$cnn}}">
                                                        <input type="hidden" name="passengers[{{ $i }}][passenger_type]" value="CNN">
                                                        <div class="col-md-2">
                                                            <label for="child_title_{{$cnn}}" class="form-label">Title</label>
                                                            <select id="child_title_{{$cnn}}" name="passengers[{{ $i }}][passenger_title]" onchange="selectGenderByTitle(this,'child',{{$cnn}})" class="form-select" required>
                                                                <option value="Mstr">Mstr</option>
                                                                <option value="Miss">Miss</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label for="child_first_name_{{$cnn}}" class="form-label">First Name</label>
                                                                <input type="text" class="form-control" name="passengers[{{ $i }}][name]" required placeholder="Enter First Name" id="child_first_name_{{$cnn}}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label for="child_last_name_{{$cnn}}" class="form-label">Last Name</label>
                                                                <input type="text" class="form-control" name="passengers[{{ $i }}][sur_name]" required placeholder="Enter Last Name" id="child_last_name_{{$cnn}}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label for="child_gender_{{$cnn}}" class="form-label">Gender</label>
                                                            <select id="child_gender_{{$cnn}}" name="passengers[{{ $i }}][passenger_gender]" required class="form-select">
                                                                <option value="M">Male</option>
                                                                <option value="F">Female</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label for="child_dob_{{$cnn}}" class="form-label">Date of Birth</label>
                                                                <input type="text" class="form-control date_dob_child" name="passengers[{{ $i }}][dob]" required autocomplete="off" placeholder="Enter Date of Birth" id="child_dob_{{$cnn}}" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="child_country_{{$cnn}}" class="form-label">Country</label>
                                                            <select id="child_country_{{$cnn}}" name="passengers[{{ $i }}][nationality]" class="form-select" required>
                                                                <option value="">Select Country</option>
                                                                @foreach (Countries() as $country)
                                                                    <option value="{{ $country->code }}">{{ substr($country->name, 0, 35) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="child_identity_{{ $cnn }}" class="form-label">Identification</label>
                                                            <select id="child_identity_{{ $cnn }}" name="passengers[{{ $i }}][document_type]" onchange="changeIdentity(this,'child',{{ $cnn }})" class="form-select" required>
                                                                <option value="P">Passport</option>
                                                                <option value="RI">Resident Identity</option>
                                                                <option value="I">National ID</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row" id="child_passport_Div{{ $cnn }}">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="child_passport_{{$cnn}}" class="form-label">Passport Number</label>
                                                                <input type="text" class="form-control" name="passengers[{{ $i }}][document_number]" required placeholder="Enter Passport Number" id="child_passport_{{$cnn}}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="child_passport_expiry_{{$cnn}}" class="form-label">Passport Expiry</label>
                                                                <input type="text" class="form-control date_passport_expiry" name="passengers[{{ $i }}][document_expiry_date]" required autocomplete="off" placeholder="Enter Passport Expiry" id="child_passport_expiry_{{$cnn}}" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="row" id="child_identity_Div_{{ $cnn }}" style="display: none;">
                                                        <div class="col-md-6">
                                                            <label for="child_issue_country_{{ $cnn }}" class="form-label">Issuing Country</label>
                                                            <select id="child_issue_country_{{ $cnn }}" name="passengers[{{ $i }}][country]" class="form-select">
                                                                <option value="">Select Country</option>
                                                                @foreach (Countries() as $country)
                                                                    <option value="{{ $country->code }}">{{ substr($country->name, 0, 35) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="child_identity_number_{{ $cnn }}" class="form-label">Identity Number</label>
                                                                <input type="text" class="form-control" name="passengers[{{ $i }}][identity_number]" placeholder="Enter Identity Number" id="child_identity_number_{{ $cnn }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @php
                                                    $i++;
                                                @endphp
                                            @endfor
                                            
                                        </div>
                                    </div>
                                </div>
                            @endif
                            {{-------------------- Infants -----------------------}}
                            @if(@$query['infants'] > 0)
                                <div class="col-xl-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="row">
                                                <div class="col-lg-8">
                                                    <h4 class="card-title fs-4 fw-bold">Infants Details</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body p-2 p-sm-4">
                                            
                                            @for ($inf = 1; $inf <= $query['infants']; $inf++)
                                                <div class="passenger_infants rounded p-2 p-sm-4 border mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label for="infant_selection_{{ $inf }}" class="form-label fs-5 fw-bold" style="line-height: 30px;font-weight: bold;">Infant ({{ $inf }})</label>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <select id="infant_selection_{{ $inf }}" name="passengers[{{ $i }}][id]" onchange="loadPassengerData(this,'infant',{{ $inf }})" disabled class="form-select infant_select_option">
                                                                
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <input type="hidden" name="passengers[{{ $i }}][id]" id="infant_id_{{ $inf }}">
                                                        <input type="hidden" name="passengers[{{ $i }}][passenger_type]" value="INF">
                                                        <div class="col-md-2">
                                                            <label for="infant_title_{{ $inf }}" class="form-label">Title</label>
                                                            <select id="infant_title_{{ $inf }}" name="passengers[{{ $i }}][passenger_title]" onchange="selectGenderByTitle(this,'infant',{{ $inf }})" class="form-select" required>
                                                                <option value="Mstr">Mstr</option>
                                                                <option value="Miss">Miss</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label for="infant_first_name_{{ $inf }}" class="form-label">First Name</label>
                                                                <input type="text" class="form-control" name="passengers[{{ $i }}][name]" required placeholder="Enter First Name" id="infant_first_name_{{ $inf }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label for="infant_last_name_{{ $inf }}" class="form-label">Last Name</label>
                                                                <input type="text" class="form-control" name="passengers[{{ $i }}][sur_name]" required placeholder="Enter Last Name" id="infant_last_name_{{ $inf }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label for="infant_gender_{{ $inf }}" class="form-label">Gender</label>
                                                            <select id="infant_gender_{{ $inf }}" name="passengers[{{ $i }}][passenger_gender]" required class="form-select">
                                                                <option value="M">Male</option>
                                                                <option value="F">Female</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label for="infant_dob_{{ $inf }}" class="form-label">Date of Birth</label>
                                                                <input type="text" class="form-control date_dob_infant" name="passengers[{{ $i }}][dob]" required autocomplete="off" placeholder="Enter Date of Birth" id="infant_dob_{{ $inf }}" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="infant_country_{{ $inf }}" class="form-label">Country</label>
                                                            <select id="infant_country_{{ $inf }}" name="passengers[{{ $i }}][nationality]" required class="form-select">
                                                                <option value="">Select Country</option>
                                                                @foreach (Countries() as $country)
                                                                    <option value="{{ $country->code }}">{{ substr($country->name, 0, 35) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="infant_identity_{{ $inf }}" class="form-label">Identification</label>
                                                            <select id="infant_identity_{{ $inf }}" name="passengers[{{ $i }}][document_type]" onchange="changeIdentity(this,'infant',{{ $inf }})" class="form-select" required>
                                                                <option value="P">Passport</option>
                                                                <option value="RI">Resident Identity</option>
                                                                <option value="I">National ID</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row" id="infant_passport_Div{{ $inf }}">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="infant_passport_{{ $inf }}" class="form-label">Passport Number</label>
                                                                <input type="text" class="form-control" name="passengers[{{ $i }}][document_number]" required placeholder="Enter Passport Number" id="infant_passport_{{ $inf }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="infant_passport_expiry_{{ $inf }}" class="form-label">Passport Expiry</label>
                                                            <input type="text" class="form-control date_passport_expiry" required name="passengers[{{ $i }}][document_expiry_date]" autocomplete="off" placeholder="Enter Passport Expiry" id="infant_passport_expiry_{{ $inf }}" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="row" id="infant_identity_Div_{{ $inf }}" style="display: none;">
                                                        <div class="col-md-6">
                                                            <label for="infant_issue_country_{{ $inf }}" class="form-label">Issuing Country</label>
                                                            <select id="infant_issue_country_{{ $inf }}" name="passengers[{{ $i }}][country]" class="form-select">
                                                                <option value="">Select Country</option>
                                                                @foreach (Countries() as $country)
                                                                    <option value="{{ $country->code }}">{{ substr($country->name, 0, 35) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label for="infant_identity_number_{{ $inf }}" class="form-label">Identity Number</label>
                                                                <input type="text" class="form-control" name="passengers[{{ $i }}][identity_number]" placeholder="Enter Identity Number" id="infant_identity_number_{{ $inf }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @php
                                                    $i++;
                                                @endphp
                                            @endfor
                                            
                                        </div>
                                    </div>
                                </div>
                            @endif
                            {{-------------------Payment Info---------------------}}
                            <div class="col-xl-12 pb-5 text-end ">
                                @if(auth('admin')->user()->can('Book-PNR'))
                                    <div>
                                        <button type="submit" class="btn btn-primary w-md" id="book_now">Book Now</button>
                                    </div>
                                @endif
                            </div>

                        </form>
                    </div>
                    
                    
                </div>
                
                {{-- --------------------------------------- --}}
                <div class="col-xl-4">
                    @include('admin.checkout.includes.right-sidebar')
                </div>
                {{-- end col-6 --}}
            </div>
            {{-- end row --}}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" type="text/javascript"></script>
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    // Checkout form datepickers
    var currentDate = new Date();
    var dobMaxChild = new Date();
    var pastDateChild = new Date();

    dobMaxChild.setFullYear(dobMaxChild.getFullYear() - 2);
    pastDateChild.setFullYear(pastDateChild.getFullYear() - 12);

    var adtDobFormated = ("0" + pastDateChild.getDate()).slice(-2) + "-"
                      + ("0" + (pastDateChild.getMonth() + 1)).slice(-2) + "-"
                      + pastDateChild.getFullYear();
    
    var chdDobFormated = ("0" + dobMaxChild.getDate()).slice(-2) + "-"
                      + ("0" + (dobMaxChild.getMonth() + 1)).slice(-2) + "-"
                      + dobMaxChild.getFullYear();

    $('.date_dob_child').each(function() {
        $(this).datepicker({
            minDate: pastDateChild,
            maxDate: dobMaxChild,
            format: 'dd-mm-yyyy',
            value:chdDobFormated
        });
    });


    $('.date_dob_infant').each(function() {
        $(this).datepicker({
            minDate: dobMaxChild,
            maxDate: currentDate,
            format: 'dd-mm-yyyy',
        });
    });
    $('.date_dob').each(function() {
        $(this).datepicker({
            format: 'dd-mm-yyyy',
            maxDate: pastDateChild,
            value: adtDobFormated
        });
    });
    $('.date_passport_expiry').each(function() {
        $(this).datepicker({
            format: 'dd-mm-yyyy',
            minDate: currentDate
        });
    });
    // End Datepickers
    function changeIdentity(selectElement,type,index){
        var selectedValue = selectElement.value;

        if(selectedValue == 'P'){
            $('#'+type+'_passport_Div'+index).show();
            $('#'+type+'_identity_Div_'+index).hide();
            $('#'+type+'_passport_'+index).prop('required', true);
            $('#'+type+'_passport_expiry_'+index).prop('required', true);
            $('#'+type+'_identity_number_'+index).prop('required', false);
            $('#'+type+'_issue_country_'+index).prop('required', false);
        }else{
            $('#'+type+'_passport_Div'+index).hide();
            $('#'+type+'_identity_Div_'+index).show();
            $('#'+type+'_passport_'+index).prop('required', false);
            $('#'+type+'_passport_expiry_'+index).prop('required', false);
            $('#'+type+'_identity_number_'+index).prop('required', true);
            $('#'+type+'_issue_country_'+index).prop('required', true);
        }
    }
    function loadCustomerData(selectElement,type) {
        var selectedValue = selectElement.value;
        if(selectedValue != ''){
            $.ajax({
                url: "{{ route('admin.customer.data' )}}",
                method: 'post',
                data: { customer_id: selectedValue,type:type },
                success: function(response) {
                    if(response.status == 200){
                        emptyCheckoutForm();
                        var customer = response.customerData;
                        $('#customer_id').val(customer.id);
                        $('#customer_name').val(customer.name);
                        $('#customer_email').val(customer.email);
                        $('#customer_country').val(customer.country);
                        $('#customer_phone').val(customer.phone);
                        $('#customer_address').text(customer.address);

                        $('#email').val(customer.email);
                        $('#country').val(customer.country);
                        $('#phone').val(customer.phone);

                        if(response.render['ADT']){
                           $('.adult_select_option').html(response.render['ADT']);
                           $('.adult_select_option').prop('disabled', false);
                        }else{
                            $('.adult_select_option').html('');
                            $('.adult_select_option').prop('disabled', true);
                        }
                        if(response.render['CNN']){
                            $('.child_select_option').html(response.render['CNN']);
                            $('.child_select_option').prop('disabled', false);
                        }else{
                            $('.child_select_option').html('');
                            $('.child_select_option').prop('disabled', true);
                        }
                        if(response.render['INF']){
                            $('.infant_select_option').html(response.render['INF']);
                            $('.infant_select_option').prop('disabled', false);
                        }else{
                            $('.infant_select_option').html('');
                            $('.infant_select_option').prop('disabled', true);
                        }
                    }
               },
               error: function(xhr, status, error) {
                  console.error(error);
               }
            });
        }else{
            emptyCheckoutForm();
        }
    }
    function loadPassengerData(selectElement,type,index){
        var selectedValue = selectElement.value;
        // console.log(selectElement);
        if(selectedValue != ''){
            $.ajax({
                url: "{{ route('admin.customer.data' )}}",
                method: 'post',
                data: { customer_id: selectedValue,type:type },
                success: function(response) {
                    if(response.status == 200){
                        var passenger = response.passenger;
                        if(passenger != null){
                            $('#'+ type +'_id_'+index).val(passenger.id)
                            $('#'+ type +'_title_'+index).val(passenger.title)
                            $('#'+ type +'_first_name_'+index).val(passenger.firstName)
                            $('#'+ type +'_last_name_'+index).val(passenger.lastName)
                            $('#'+ type +'_gender_'+index).val(passenger.gender)
                            $('#'+ type +'_dob_'+index).val(passenger.dob)
                            $('#'+ type +'_country_'+index).val(passenger.region)
                            $('#'+ type +'_identity_'+index).val(passenger.identity)
                            $('#'+ type +'_phone_'+index).val(passenger.phone)
                            if(passenger.identity == 'P'){
                                $('#'+ type +'_passport_Div'+index).show();
                                $('#'+ type +'_identity_Div_'+index).hide();
                                $('#'+ type +'_passport_'+index).prop('required', true);
                                $('#'+ type +'_passport_expiry_'+index).prop('required', true);
                                $('#'+ type +'_identity_number_'+index).prop('required', false);
                                $('#'+ type +'_issue_country_'+index).prop('required', false);

                                $('#'+ type +'_passport_'+index).val(passenger.passportNumber)
                                $('#'+ type +'_passport_expiry_'+index).val(passenger.passportExpiry)
                            }else{
                                $('#'+ type +'_passport_Div'+index).hide();
                                $('#'+ type +'_identity_Div_'+index).show();
                                $('#'+ type +'_passport_'+index).prop('required', false);
                                $('#'+ type +'_passport_expiry_'+index).prop('required', false);
                                $('#'+ type +'_identity_number_'+index).prop('required', true);
                                $('#'+ type +'_issue_country_'+index).prop('required', true);
                                $('#'+ type +'_identity_number_'+index).val(passenger.identityNumber)
                                $('#'+ type +'_issue_country_'+index).val(passenger.issueCountry)
                            }
                        }else{
                            $('#'+ type +'_id_'+index).val('')
                            $('#'+ type +'_title_'+index).val('')
                            $('#'+ type +'_first_name_'+index).val('')
                            $('#'+ type +'_last_name_'+index).val('')
                            $('#'+ type +'_gender_'+index).val('')
                            $('#'+ type +'_dob_'+index).val('')
                            $('#'+ type +'_country_'+index).val('')
                            $('#'+ type +'_identity_'+index).val('')
                            $('#'+ type +'_passport_'+index).val('')
                            $('#'+ type +'_passport_expiry_'+index).val('')
                            $('#'+ type +'_identity_number_'+index).val('')
                            $('#'+ type +'_issue_country_'+index).val('')
                            $('#'+ type +'_phone_'+index).val('')
                        }

                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }else{
            emptyCheckoutForm();
        }
    }

    function selectGenderByTitle(selectElement,type,index){
        var selectedValue = selectElement.value;
        if(selectedValue =='Mr' || selectedValue =='Mstr'){
            $('#'+ type +'_gender_'+index).val('M');
        }else{
            $('#'+ type +'_gender_'+index).val('F');
        }
    }
    function emptyCheckoutForm(){
        var grandTotalInput = $('#grand_total');
        var grandTotalValue = grandTotalInput.val();
        var additional_paymentInput = $('#additional_payment');
        var additional_paymentValue = additional_paymentInput.val();
        var adjustmentInput = $('#adjustment');
        var adjustmentValue = adjustmentInput.val();

        // $('#checkoutForm input[type="text"]').val('');
        $('#checkoutForm input[type="email"]').val('');
        $('#checkoutForm input[type="number"]').val('');
        $('#checkoutForm textarea').text('');
        $('#checkoutForm select').val('');
        $('.adult_select_option').prop('disabled', true);
        $('.child_select_option').prop('disabled', true);
        $('.infant_select_option').prop('disabled', true);

        // Restore the value of the grand_total input
        grandTotalInput.val(grandTotalValue);
        additional_paymentInput.val(additional_paymentValue);
        adjustmentInput.val(adjustmentValue);
    }
    $('#book_now').click(function(event){
        event.preventDefault();

        $('.error').remove();
    
        let isValid = true;
        
        // Loop through each input field to validate
        $('#checkoutForm').find('input, select').each(function() {
            if ($(this).prop('required')) {
                if ($(this).val() === '') {
                    isValid = false;
                    $(this).after('<span class="error" style="color:red">This field is required</span>');
                }
            }
        });
        
        if (isValid) {
            $(".loader-container").removeClass("d-none");
            $(".main-content").addClass("d-none").show();
            $(this).attr('disabled', true);
            // Serialize form data
            var formData = $('#checkoutForm').serialize();

            $.ajax({
                url: "{{ route('admin.flight.create.pnr') }}",
                type: 'POST',
                data: formData,
                success: function(response) {
                    $(".loader-container").addClass("d-none");
                    $(".main-content").removeClass("d-none");
                    if(response.status == "success"){
                        window.location.href = response.url;
                    }else{
                        showSweetAlert(response.message,"warning","Okay, got it!","btn btn-danger");
                    }
                },
                error: function(xhr, status, error) {
                    $(".loader-container").addClass("d-none");
                    $(".main-content").removeClass("d-none");
                    $('#book_now').attr('disabled', false);
                    const response = JSON.parse(xhr.responseText);

                    if (response.status === "fail") {
                        showSweetAlert(response.message,"warning","Okay, got it!","btn btn-danger");
                    } else {
                        console.error("Unexpected response status:", response.status);
                        showSweetAlert(xhr.responseText,"warning","Okay, got it!","btn btn-danger");
                    }
                }
            });
        } else {
            alert("Please fill in all required fields.");
        }

        ///////////////////////////////////////////////
        
    });
    // var sessionWarning = false;
    // setInterval(function(){
    //     var searchTime = parseInt(localStorage.getItem('sessionExpiry'));
    //     var currentTime = Date.now();
    //     var expiryTime3 = searchTime + (1000 * 60 * 90);

    //     if (expiryTime3 < currentTime && !sessionWarning && searchTime > 0) {
    //         // localStorage.setItem('sessionExpiry', 0);
    //         sessionWarning = true;
    //         Swal.fire({
    //             title: 'Opps...',
    //             text: "Your session has been expired.",
    //             icon: "warning",
    //             buttonsStyling: false,
    //             confirmButtonText: "Okay, got it!",
    //             customClass: {
    //             confirmButton: "btn btn-primary"
    //             }
    //         }).then(function() {
    //             window.location.href = '/admin/flight/search';
    //         });
    //     }
    // }, 60000);
</script>
@endsection







