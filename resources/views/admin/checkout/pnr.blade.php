@extends('admin.layouts.app')

@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        <a href="{{ route('admin.bookings') }}">Booking List / </a>
        <span>Booking Detail</span>
    </h4>
@endsection
@section('styles')
<link href="{{ asset('assets/css/flight-search.css') }}" rel="stylesheet" type="text/css" />
    <style>
        #full-card p{
            font-weight: 500;
        }
        #full-card span{
            font-weight: 500;
        }
        #full-card .icon-rotate .fa{
            font-size: 50px;
            transform: rotate(300deg);
        }
        .grids-section{
            border: 1px solid black;
        }
        .text-secondary-dark {
            color: var(--secondary) !important;
        }
        .text-secondary-light {
            color: var(--secondary-light) !important;
        }
        :root {
            --primary       : #3b76e1;
            --primary-rgba  : 20, 73, 139;
            --secondary     : #da7604;
            --secondary-light     : #dfad73;
            --secondary-rgba: 218, 118, 4;
        }
        /* ==============media Query================ */
        @media screen and (min-width: 480px){
            #full-card{
                padding: 20px;
            }
        }
        @media print {
            .for-print-only{
                display: block !important;
            }
            .no-print {
                display: none;
            }
            .print-top-margin-5{
                margin-top: 5px;
            }
            title {
                display: none;
            }
            @page {
                margin: 0;
            }

            body {
                margin: 0;
            }

            @page {
                margin-top: 0;
                margin-bottom: 0;
            }

            @page {
                size: auto;
                margin: 0;
            }
        }
        .toggle-form {
            position: fixed;
            top: 15%;
            right: 0;
            transform: translateX(100%);
            transition: transform 0.5s;
            z-index: 1000;
            background: white;
            padding: 10px;
            box-shadow: -2px 0 13px rgba(0,0,0,0.5);
            /* width: 40%; */
        }
        .toggle-form.visible {
            transform: translateX(0);
            border-radius: 50px 0 0 50px;
        }
        @media screen and (max-width: 479px){
            .toggle-form {
                position: fixed;
                top: 10%;
                width: 100%;
            }
            .toggle-form.visible {
                border-radius: 0px !important;
            }
        }
    </style>
@endsection

@section('content')
<div id="layout-wrapper">
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row no-print">
                    <div class="col-lg-9 mb-3">
                        @if ($order->pnr_status != 'Cancelled')
                            {{-- @if ($order->status == 'Not Ticketed' || $order->status == 'Ticketed' || $order->status == 'Voided')
                                <button class="btn btn-outline-info w-md" onclick="updatePNR('{{ @$order->pnrCode }}','{{ encrypt(@$order->id) }}', this)">
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    Refresh PNR
                                </button>
                            @endif --}}
                            @if(auth('admin')->user()->can('Issue-PNR'))
                                @if(checkCreditLimit($order->total)!="Low Credit Limit")
                                    @if ($order->status == 'Not Ticketed' || $order->status == 'Voided')
                                        <button class="btn btn-outline-success w-md" onclick="issueTicket('{{ @$order->pnrCode }}','{{ encrypt(@$order->id) }}', this)">
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                            Issue Ticket
                                        </button>
                                    @endif
                                @endif
                            @endif
                            
                            @if(auth('admin')->user()->can('Cancell-PNR'))
                                @if ($order->status == 'Not Ticketed' || $order->status == 'Voided')
                                    <button class="btn btn-outline-danger w-md" onclick="cancelBooking('{{ @$order->pnrCode }}','{{ encrypt(@$order->id) }}', this)">
                                        <span class="spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        Cancel Booking
                                    </button>
                                @endif
                            @endif

                            @if ($order->status == 'Not Ticketed')
                                <button class="btn btn-outline-primary w-md" onclick="repricePNR('{{ @$order->pnrCode }}','{{ $order->ref_key }}', this)">
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    Reprice PNR
                                </button>
                            @endif

                            @if(auth('admin')->user()->can('Void-PNR'))
                                @if ($order->status == 'Ticketed' && $order->created_at->isToday())
                                    <button class="btn btn-outline-warning w-md" onclick="voidTicket('{{ @$order->pnrCode }}','{{ encrypt(@$order->id) }}', this)">
                                        <span class="spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        Void Ticket
                                    </button>
                                @endif
                            @endif
                        @endif
                        {{-- ---------Custom Email----------------- --}}
                        <div class="float-end">
                            {{-- <form class="row gx-3 gy-2 row-cols-lg-auto align-items-center toggle-form ps-4" id="emailForm"> --}}
                            <form class="row gx-3 gy-2 align-items-center toggle-form ps-4" id="emailForm">
                                <div class="col-md-12">
                                    <label class="visually-hidden" for="input-email">Email</label>
                                    <div class="input-group mb-2 ">
                                        <div class="input-group-text bg-white">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <input type="email" class="form-control" id="input-email" placeholder="Email">
                                    </div>
                                </div>
                                <div class="col-md-8 mt-0">
                                    <span class="me-3">
                                        <input class="form-check-input" value="1" type="checkbox" checked id="hide_agent">
                                        <label class="form-check-label" for="hide_agent">
                                            Hide B.Agent
                                        </label>
                                    </span>
                                    <span>
                                        <input class="form-check-input" value="1" type="checkbox" id="with_fare">
                                        <label class="form-check-label" for="with_fare">
                                            With Fares
                                        </label>
                                    </span>
                                </div>
                                <div class="col-md-4 mt-0 text-end">
                                    <div class="" role="group" aria-label="Basic example">
                                        <button class="btn btn-primary" id="send_custom_email" data-pnr="{{ @$order->pnrCode }}" data-ref="{{ @$order->ref_key }}">
                                            <i class="bx bx-loader bx-spin font-size-16 align-middle me-2 d-none" id="custom_email_spiner"></i>
                                            <i class="fas fa-paper-plane"></i>
                                            Send
                                        </button>
                                        <a href="javascript:void(0)" class="btn btn-danger" id="hide-email-form">cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    {{-- -------------Email and PDF-------------- --}}
                    <div class="col-lg-3 text-end ">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-info px-4" onclick="emailBooking('{{ @$order->pnrCode }}','{{ @$order->ref_key }}', this,0)">
                                <span class="spinner spinner-border spinner-border-sm d-none" id="email_spiner" role="status" aria-hidden="true"></span>
                                <i class="fas fa-envelope"></i>
                            </button>
                            <button type="button" class="btn btn-outline-info dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="mdi mdi-chevron-down"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="javascript:void(0)" onclick="emailBooking('{{ @$order->pnrCode }}','{{ @$order->ref_key }}', this,0)">
                                    <i class="fas fa-envelope"></i>
                                    Email without Fares
                                </a>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="emailBooking('{{ @$order->pnrCode }}','{{ @$order->ref_key }}', this,1)">
                                    <i class="fas fa-envelope"></i>
                                    Email with Fares
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:void(0)" id="email-to-other">Send to other email</a>
                            </div>
                        </div>
                        <div class="btn-group">

                            <a class="btn btn-outline-info px-4" href="{{ route('admin.generate.pdf', ['booking_ref' => $order->ref_key, 0]) }}" target="_blank">
                                <span class="spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <i class="fa fa-file-pdf"></i>
                            </a>
                            <button type="button" class="btn btn-outline-success dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="mdi mdi-chevron-down"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('admin.generate.pdf', ['booking_ref' => $order->ref_key, 0]) }}" target="_blank">
                                    <i class="fa fa-file-pdf"></i>
                                    PDF without Fares
                                </a>
                                <a class="dropdown-item" href="{{ route('admin.generate.pdf', ['booking_ref' => $order->ref_key, 1]) }}" target="_blank">
                                    <i class="fa fa-file-pdf"></i>
                                    PDF with Fares
                                </a>
                            </div>
                        </div>
                    </div>
                </div>             
            </div>
            <div class="container-fluid print-top-margin-5">
                {{-- --------------For Print only------------------ --}}
                <div class="row p-3 pb-0 for-print-only d-none">
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            @if ($order->admin->type == 'Admin User')
                                <img src="{{ asset('assets/images/mainLogo.png') }}" height="80px" alt="Indus">
                            @else
                                @if (@$order->agency->logo)
                                    <img src="{{ asset($order->agency->logo) }}" height="80px" alt="{{ @$order->agency->name }}">
                                @endif
                            @endif
                        </div>
                        <div class="align-content-center">
                            <p class="mb-0" style="border-bottom:1px solid #000">Travel Consultant: {{ $order->admin->first_name .' '.$order->admin->last_name}}</p>
                            <p class="text-success fw-semibold"><i class="fas fa-envelope"></i> {{ $order->admin->email }}</p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-2 mt-3">
                        <span>Booking Date: <b>{{ date('d M Y',strtotime($order->created_at)) }}</b></span>
                        @if (@$order->issued_at)
                            <span>Issue Date: <b>{{ date('d M Y',strtotime($order->issued_at)) }}</b></span>
                        @endif
                    </div>
                </div>
                <div class="row">
                    {{-- ==========================PNR Status Header==================== --}}
                    <div class="col-12">
                        <div class="card bg-primary bg-gradient text-white p-3 rounded-1">
                            <div class="d-block d-lg-flex justify-content-between">
                                <span>
                                    <h5 class="text-white fw-bold mb-1">GDS PNR: {{ @$order->pnrCode}}</h5>
                                    <p class="mb-0" style="font-size: 12px;">Source: {{ @$order->api }}</p>
                                    {{-- <p class="mb-0" style="font-size: 12px;">Last Date to purchase: {{ @$order->last_ticketing_date}}</p> --}}
                                    {{-- <hr> --}}
                                </span>
                                <span class="d-flex justify-content-between no-print">
                                    <span class="no-print">
                                        <small>Ticket Status</small><br>
                                        @if ($order->status == 'Not Ticketed')
                                            <span class="fs-5 fw-bold text-warning" id="order_status">{{ $order->status }}</span>
                                        @elseif($order->status == 'Ticketed')
                                            <span class="fs-5 fw-bold text-white" id="order_status">{{ $order->status }}</span>
                                        @else
                                            <span class="fs-5 fw-bold text-danger" id="order_status">{{ $order->status }}</span>
                                        @endif
                                    </span>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <span class="no-print">
                                        <small>Segment Status</small><br>
                                        <span class="fs-5 fw-bold">{{ $order->pnr_status }}</span>
                                    </span>
                                </span>

                                <span class="d-flex justify-content-between for-print-only d-none">
                                    <span class="">
                                        <small>Ticket Status: </small>
                                        @if ($order->status == 'Not Ticketed')
                                            <span class="fs-5 fw-bold text-warning">{{ $order->status }}</span>
                                        @elseif($order->status == 'Ticketed')
                                            <span class="fs-5 fw-bold text-white">{{ $order->status }}</span>
                                        @else
                                            <span class="fs-5 fw-bold text-danger">{{ $order->status }}</span>
                                        @endif
                                    </span>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <span class="" style="text-align: right">
                                        <small>Segment Status: </small>
                                        <span class="fs-5 fw-bold">{{ $order->pnr_status }}</span>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9 col-sm-12 col-md-12">
                        <div class="card rounded-0">
                            {{-- =========================Itineraries========================= --}}
                            @php
                                $finaldata = json_decode($order['final_data'],true);
                                $extras = json_decode($order->extras,true);
                                $customer_data = json_decode($order['customer_data'],true);
                                $tickets_data = json_decode($order['tickets_data'],true);
                                $brand_ref_key = @$customer_data['brand_ref_key'];
                                $Brands = array();
                                
                                $CurrencyCode = "PKR";
                                $passBreak = [];
                                foreach (['Adult', 'Child', 'Infant'] as $paxType) {
                                    $passBreak[] = [
                                        'PaxType' => $paxType,
                                        'Quantity' => 0,
                                        'BasePrice' => 0,
                                        'TaxPrice' => 0,
                                        'TotalPaxPrice' => 0,
                                        'RuleType' => '',
                                        'Markup' => 0,
                                        'Discount' => 0,
                                        'Percentage' => 0,
                                    ];
                                }
                            @endphp
                                {{-- @dump($extras['newFligts'],$finaldata['Flights']) --}}
                            @foreach ($finaldata['Flights'] as $flightIndex => $flights)
                                @php
                                    if($flightIndex == 0){
                                        $trip = "Onward";
                                    }else{
                                        $trip = "Return";
                                    }
                                    // if (@$extras['newFligts']) {
                                    //     $segments = collect($extras['newFligts'][$flightIndex]['Segments']);
                                    // }else{
                                        $segments = collect($flights['Segments']);
                                    // }
                                    // dump($flights['Segments'],$extras['newFligts'][$flightIndex]['Segments']);
                                    $firstSegment = $segments->first();
                                    $lastSegment = $segments->last();
                                    $originCode = $firstSegment['Departure']['LocationCode'];
                                    $arrivalionCode = $lastSegment['Arrival']['LocationCode'];
    
                                    if(@$brand_ref_key){
                                        $brand_ref = $brand_ref_key[$flightIndex];
                                        if (isset($flights['Fares']) && is_array($flights['Fares'])) {
                                            $filteredFares = array_filter($flights['Fares'], function ($fare) use ($brand_ref) {
                                                return $fare['RefID'] == $brand_ref;
                                            });
                                            if (!empty($filteredFares)) {
                                                foreach ($filteredFares as $filteredFare) {
                                                    if($flightIndex == 0){
                                                        $RuleType = @$filteredFare['RuleType'];
                                                    }
                                                    foreach ($filteredFare['PassengerFares'] as $key => $value) {
                                                        if(@$value['BasePrice']){
                                                            $passBreak[$key]['Quantity'] = @$value['Quantity'];
                                                            $passBreak[$key]['BasePrice'] += $value['BasePrice'] / count($finaldata['Flights']);
                                                            $passBreak[$key]['TaxPrice'] += $value['Taxes'] / count($finaldata['Flights']);
                                                            $passBreak[$key]['TotalPaxPrice'] += $value['TotalPrice'] / count($finaldata['Flights']);
                                                            $passBreak[$key]['Markup'] += @$value['Markup'];
                                                            $passBreak[$key]['Discount'] += (@$value['Discount']);
                                                            $passBreak[$key]['Percentage'] = (@$value['Percentage']) ? @$value['Percentage'] : 0;
                                                            $passBreak[$key]['RuleType'] = @$RuleType;
                                                        }
                                                    }
                                                    $flightBrands = $filteredFare;
                                                }
                                            }
                                        }
                                    }else{
                                        $flightBrands = $flights['Fares'][0];
                                        foreach ($flights['Fares'][0]['PassengerFares'] as $key => $value) {
                                            $passBreak[$key]['Quantity'] = @$value['Quantity'];
                                            $passBreak[$key]['BasePrice'] += $value['BasePrice'] / count($finaldata['Flights']);
                                            $passBreak[$key]['TaxPrice'] += $value['Taxes'] / count($finaldata['Flights']);
                                            $passBreak[$key]['TotalPaxPrice'] += $value['TotalPrice'] / count($finaldata['Flights']);
                                            $passBreak[$key]['Markup'] += @$value['Markup'];
                                            $passBreak[$key]['Discount'] += (@$value['Discount']);
                                            $passBreak[$key]['Percentage'] = (@$value['Percentage']) ? @$value['Percentage'] : 0;
                                            $passBreak[$key]['RuleType'] = @$flightBrands['RuleType'];
                                        }
                                    }
                                    
                                    foreach ($passBreak as $key => $value) {
                                        if (empty($value['BasePrice'])) {
                                            unset($passBreak[$key]);
                                        }
                                    }
                                @endphp
                                
                                <div class="row ps-3 pe-3 gx-0 bg-soft-warning" style="border-radius: 5px 5px 0px 0px;">
                                    <div class="col p-2 pt-3">
                                        <div>
                                            <h5 class="">
                                                <i class='fas fa-plane fs-5' style="{{ ($trip == 'Return') ? 'transform: rotate(180deg);' : '' }}"></i> 
                                                {{ $trip }} <span class="fw-light fs-6">{{ count($flights['Segments']) }} Flight(s)</span> | 
                                                <span>{{ $originCode }} - {{ $arrivalionCode }}</span>
                                                @if(@$flightBrands['Name'])
                                                    <span class="badge badge-outline-primary ms-2 rounded-1 text-primary">
                                                        <i class="fas fa-check-double"></i>
                                                        {{ $flightBrands['Name'] }}
                                                    </span>
                                                @endif
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="row gx-0">
                                    <div class="col-12 table-responsive">
                                        <table class="table table-borderless p-0" style="width: 100%;">
                                            <thead class="bg-soft-primary" style="width: 100%;">
                                                <tr>
                                                    <th scope="col" colspan="2">Airline</th>
                                                    <th scope="col">Departing</th>
                                                    <th scope="col">Arriving</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody class="">
                                                @foreach ($segments as $segKey => $segment)

                                                        
                                                        @php
                                                            if (@$segment['MarketingAirline']) {
                                                                $FlightCode = $segment['MarketingAirline']['Code'];
                                                                $FlightNumber = $segment['MarketingAirline']['FlightNumber'];
                                                            }else{
                                                                $FlightCode = $segment['OperatingAirline']['Code'];
                                                                $FlightNumber = $segment['OperatingAirline']['FlightNumber'];
                                                            }
                                                            
                                                            $DepartureCode = $segment['Departure']['LocationCode'];
                                                            $ArrivalCode = $segment['Arrival']['LocationCode'];
                                                            $originTerminal = @$segment['Departure']['Terminal'];
                                                            $arrivalTerminal = @$segment['Arrival']['Terminal'];
                        
                                                            $DepartureDate =  date('D d M Y, H:i',strtotime($segment['Departure']['DepartureDateTime']));
                                                            $ArrivalDate =  date('D d M Y, H:i',strtotime($segment['Arrival']['ArrivalDateTime']));
                            
                                                            $AirName = AirlineNameByAirlineCode($FlightCode);
                                                            $AirName = strlen($AirName) > 20 ? substr($AirName, 0, 13) . "..." : $AirName;
                                                            $Cabin = @$segment['Cabin'];
                                                            $CabinClass = @$segment['CabinClass'];
                                                            // ---------------------------------------
                                                            $Layover = null;
                                                            if ($segKey > 0) {
                                                                $CurrentDeparture = new DateTime($segment['Departure']['DepartureDateTime']);
                                                                $PreviousArrival = new DateTime($flights['Segments'][$segKey - 1]['Arrival']['ArrivalDateTime']);
                                                                $Layover = $CurrentDeparture->diff($PreviousArrival)->format('%H:%I');
                                                            }
                                                            if(@$extras['airline'][0]['airlineCode']){
                                                                $filteredAirline = array_filter($extras['airline'], function($airline) use ($FlightCode) {
                                                                    return $airline['airlineCode'] === $FlightCode;
                                                                });
                                                                $filteredAirline = array_values($filteredAirline);

                                                            }else{
                                                                $ailinePnrStatus = @$extras['airline'][0]['pnrStatus'];
                                                            }
                                                        @endphp
                                                        @if ($Layover)
                                                            <tr>
                                                                <td colspan="5" class="text-center p-0 ">
                                                                    <div class="segment-layover w-50 m-auto ">
                                                                        <div class="border rounded-1 text-center bg-opacity-25 bg-secondary ">
                                                                            <i class="fas fa-clock me-2"></i>
                                                                            @php
                                                                                list($hours, $minutes) = explode(':', $Layover);
                                                                                $hours = (int) $hours;
                                                                                $minutes = (int) $minutes;
                                                                            @endphp
                                                                            Stop duration: {{ $hours.' Hours '.$minutes.' Minutes' }}
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                        <tr>
                                                            <td scope="col-3">
                                                                <img src="{{ asset('assets/airlines/'.$FlightCode.'.png') }}" alt="{{ $FlightCode }}" style="width: 50px;">
                                                            </td>
                                                            <td scope="col-3">
                                                                <h5>
                                                                    {{ $AirName }}
                                                                    <br>   
                                                                    <span style="margin-top: -20px;">{{ $FlightCode }}-{{ $FlightNumber }}</span>
                                                                </h5>
                                                                <span>{{ $CabinClass }}({{ @$flightBrands['FareBases'][$segKey]['BookingCode'] }})</span>
                                                                @if (@$segment['MarketingAirline'])
                                                                    @if ($segment['MarketingAirline']['Code'] != $segment['OperatingAirline']['Code'])
                                                                        <p class="small mb-0 text-primary">Operated By: <b>{{ AirlineNameByAirlineCode($segment['OperatingAirline']['Code']) }}</b></p>
                                                                    @endif
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <p class="mb-0">
                                                                    <b>{{ $DepartureCode }}</b>
                                                                    {{ CityNameByAirportCode($DepartureCode) }}
                                                                    @if (@$originTerminal)
                                                                        | <b>Terminal: {{ $originTerminal }}</b>
                                                                    @endif
                                                                </p>
                                                                <p class="d-none d-lg-block mb-0">
                                                                    {{ AirportByCode($DepartureCode) }}
                                                                </p>
                                                                <p>
                                                                    <b>{{ $DepartureDate }}</b>
                                                                </p> 
                                                            </td>
                                                            <td>
                                                                <p class="mb-0">
                                                                    <b>{{ $ArrivalCode }}</b> 
                                                                    {{ CityNameByAirportCode($ArrivalCode) }}
                                                                    @if (@$arrivalTerminal)
                                                                    | <b>Terminal: {{ $arrivalTerminal }}</b>
                                                                    @endif
                                                                </p>
                                                                <p class="d-none d-lg-block mb-0">
                                                                    {{ AirportByCode($ArrivalCode) }}
                                                                </p>
                                                                <p>
                                                                    <b>{{ $ArrivalDate }}</b>
                                                                </p>
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $SegHours = floor($segment['Duration'] / 60);
                                                                    $SegMinutes = $segment['Duration'] % 60;
                                                                @endphp
                                                                
                                                                @if (@$segment['seatStatus'] && @$segment['seatStatus'] !='Confirmed')
                                                                    <p class="mb-0 text-danger fw-bold">
                                                                        Flight: {{ @$segment['seatStatus'] }}
                                                                    </p>
                                                                @else
                                                                    @if (@$filteredAirline)
                                                                        <p class="mb-0 text-primary">
                                                                            {{ $FlightCode.' PNR: '}} <b>{{ $filteredAirline[0]['airlinePnr'] }}</b>
                                                                        </p>
                                                                    @else
                                                                        <p class="mb-0 text-danger fw-bold">
                                                                            {{ $FlightCode.' PNR: '.@$ailinePnrStatus }}
                                                                        </p>
                                                                    @endif
                                                                @endif
                                                                <p class="mb-0">
                                                                    <small>
                                                                        <i class="fas fa-clock me-1" style="color: #9f9494;"></i>
                                                                        {{ $SegHours }}H {{ $SegMinutes }}M
                                                                    </small>
                                                                </p>
                                                                
                                                                @foreach ($flightBrands['BaggagePolicy'] as $bagg)
                                                                    @if (is_array($bagg) && isset($bagg['PaxType']))
                                                                        <p class="mb-0">
                                                                            <small>
                                                                                <i class="fas fa-suitcase" style="color: #9f9494;"></i>
                                                                                {{ $bagg['PaxType'] }}: {{ $bagg['Weight'] }} {{ $bagg['Unit'] }}
                                                                            </small>
                                                                        </p>
                                                                    @endif
                                                                @endforeach
                                                            </td>
                                                        </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                            @endforeach
                            {{-- ====================================Passenger Details========================================= --}}
                            <div class="row gx-0">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr class="header-row Passengerheader bg-soft-primary">
                                                <th></th>
                                                <th class="">Passenger(s) Details</th>
                                                <th class="">Passport Details</th>
                                                {{-- <th class="">PNR</th> --}}
                                                <th class="">FF No</th>
                                                <th class="">E-Ticket</th>
                                                <th class="">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="passenger_ticket_data">
                                            @include('admin.checkout.includes.passenger-ticket-table',['customer_data' => $customer_data,'tickets_data' => $tickets_data, 'order' => $order])
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            {{-- ==================Airline RemarksAirline Remarks=========== --}}
                            @if (@$order->extras)
                                @if (@$extras['services']['specialServices'])
                                    <div class="row gx-0 ps-5 pe-5 no-print border-bottom">
                                        <div class="col-12">
                                            <h3 class="text-black text-opacity-75">Airline Remarks</h3>
                                            {{-- <hr> --}}
                                            <ul>
                                                @foreach ($extras['services']['specialServices'] as $item)
                                                <li>{{ $item['message'] }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            {{-- ==================Important Information==================== --}}
                            <div class="row gx-0 ps-5 pe-5 for-print-only d-none pt-3">
                                <h3 class="text-black text-opacity-75">Important Information</h3>
                                <p>
                                    All Guests, including children and infants, must present valid identification at check-in.
                                    <br>
                                    Check-in beg ins 4 hours for international and 3 hours for domestic prior to the flight for seat assignment and closes 75 minutes prior to the scheduled
                                        departure.
                                    <br>
                                    Carriage and other services provided by the carrier are subject to conditions of carriage, which are hereby incorporated by reference. These conditions may
                                    be obtained from the issuing carrier.
                                    <br>
                                    Transportation and other services provided by the carrier are subjected to conditions of contract and other important notices. Please ensure that you have
                                    received these notices, and if not, contact the booking partner or issuing carrier to obtain a copy prior to the commencement of your trip.
                                    <br>
                                    If the passenger journey involves an ultimate destination or stop in a country other than the country of departure, the Warsaw Convention may be applicable
                                    and this convention governs and on most case limits the liability of carriers for death or personal injury and in respect of loss of or damage to bag gage.
                                    <br>
                                    Please check the figures / timing s as they may change time to time without any notice to the passenger.
                                    <br>
                                    For Infants valid birth certificate is required.
                                </p>
                            </div>
                            
                        </div>
                    </div>
                    {{-- =================================Fares & Brakdown====================================== --}}
                    
                    <div class="col-lg-3 col-sm-12 col-md-12 no-print">
                        <div class="card mb-1">
                            <div class="row gx-0">
                                <div class="col-md-12 rounded-1">
                                    <div class="pt-2 bg-soft-warning pb-3 fw-bold ps-3" style="border-radius: 5px 5px 0px 0px;">
                                        <span><i class="fas fa-dollar-sign"></i> Payment Details</span>
                                    </div>
                                    @php
                                        $CurrencyCode = "PKR";
                                        $BasePrice = 0;
                                        $Taxes = 0;
                                        $BillablePrice = 0;
                                        $Markup = 0;
                                        $Discount = 0;
                                        $Percentage = 0;
                                        $RuleType = '';
                                        
                                        foreach ($passBreak as $key => $breakfare) {
                                            $BasePrice += @$breakfare['BasePrice'] * $breakfare['Quantity'];
                                            $Taxes += $breakfare['TaxPrice'] * $breakfare['Quantity'];
                                            $Markup += $breakfare['Markup'] * $breakfare['Quantity'];
                                            $Discount += @$breakfare['Discount'] * $breakfare['Quantity'];
                                            $RuleType = $breakfare['RuleType'];
                                            $Percentage = $breakfare['Percentage'];
                                        }
                                        if(@$Markup > 0){
                                            $BillablePrice = $order->userPricingEnginePrice + $Markup;
                                        }else{
                                            $BillablePrice = $order->userPricingEnginePrice - $Discount;
                                        }
                                    @endphp
                                    <div class="row pt-3 pb-2  m-0 border-bottom border-2">
                                        <div class="col-md-6">
                                            <span>Type</span>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <span>Amount</span>
                                        </div>
                                    </div>
                        
                                    <!-- Payment Details Content -->
                                    <div class="d-flex justify-content-between text-dark border-bottom border-2 p-3 m-0 pb-3">
                                        <span class="">
                                            <span>Base Fare</span><br>
                                            <span>Booking Fee & Taxes </span><br>
                                            <span>PSF/Discount </span><br>
                                            <span>Addon Fee</span><br>
                                        </span>
                                        
                                        <span class=" text-end fw-bold">
                                            <span>{{ $CurrencyCode }} {{ number_format($BasePrice) }}</span> <br>
                                            <span>{{ $CurrencyCode }} {{ number_format($Taxes) }}</span><br>
                                            <span>{{ (@$Markup > 0) ? '+' : '-' }} {{ $CurrencyCode }} @if(@$Markup > 0){{ number_format($Markup) }} @else {{ number_format($Discount) }} @endif</span><br>
                                            <span>0</span><br>
                                        </span>
                                    </div>
                        
                                    <!-- Total Fare -->
                                    <div class="row p-2">
                                        <div class="col-md-6">
                                            <span>Total Fare</span>
                                        </div>
                                        <div class="col-md-6 text-end fw-bold">
                                            <span>{{ $CurrencyCode }} {{ number_format($BillablePrice) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a data-bs-toggle="offcanvas" href="#flightSidepanel" aria-controls="flightSidepanel">
                            <b>
                                Fare detail >>
                            </b>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" data-bs-scroll="false" id="flightSidepanel"
        aria-labelledby="flightSidepanelLabel">
        <div class="offcanvas-header">
            <h5 id="flightSidepanelLabel">Fare Details</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">

            <div class="tab-content text-muted">
                
                @php
                    $finalData = json_decode($order->final_data,true);
                @endphp
                <!-- Fare Breakdown -->
                <div class="tab-pane active" id="fareBreakdown" role="tabpanel">
                    <div class="card card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="fw-semibold">Summary</th>
                                        <th class="fw-semibold">Base Fare</th>
                                        <th class="fw-semibold">Fee &amp; Tax</th>
                                        <th class="fw-semibold">Discount/Markup</th>
                                        <th class="fw-semibold">No. Of Pax</th>
                                        <th class="fw-semibold">Total Per Pax</th>
                                        <th class="fw-semibold">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- @dump($passBreak,$finalData['Flights'][0]['Fares'][0]['PassengerFares']) --}}
                                    @php
                                        $Currency = 'PKR';
                                        $TotalFax = 0;
                                        $totalBase = 0;
                                        $totalTax = 0;
                                        $totalMarkup = 0;
                                        $totalDiscount = 0;
                                        $TotalFare = 0;
                                        $subTotal = 0;
                                        $GrandTotal = 0;
                                        $Type = '';
                                        $Percentage = 0;
                                    @endphp
                                    @foreach($passBreak as $fareIndex => $fare)
                                        @php
                                            $TotalPerPax = $fare['TotalPaxPrice'] * $fare['Quantity'];
                                            $totalBase += $fare['BasePrice'];
                                            $totalTax += $fare['TaxPrice'];
                                            if(@$fare['Markup']){
                                                $totalMarkup += @$fare['Markup'] * $fare['Quantity'];
                                            }else{
                                                $totalDiscount += @$fare['Discount'] * $fare['Quantity'];
                                            }
                                            $subTotal += $fare['TotalPaxPrice'];
                                            // $GrandTotal += $TotalPerPax;
                                            $Type = @$fare['Type'];
                                            $Percentage = @$fare['Percentage'];
                                        @endphp
                                        <tr>
                                            <td>{{ $fare['PaxType'] }}</td>
                                            <td>{{ $Currency }} {{ number_format($fare['BasePrice']) }}</td>
                                            <td>{{ $Currency }} {{ number_format($fare['TaxPrice']) }}</td>
                                            <td>{{ (@$fare['Markup'] != 0) ? '+' : '-' }}{{ $Currency }} {{ (@$fare['Markup']) ? $fare['Markup'] * @$fare['Quantity'] : @$fare['Discount'] * $fare['Quantity'] }} @if(@$fare['Type'] == 'Percentage') / ({{$fare['Percentage']}}%) @endif</td>
                                            <td>{{ $fare['Quantity'] }}</td>
                                            <td>{{ $Currency }} {{ number_format($fare['TotalPaxPrice']) }}</td>
                                            <td class="fw-semibold">{{ $Currency }} {{ number_format($TotalPerPax) }}</td>
                                        </tr>
                                    @endforeach
                                    @php
                                        if(@$Markup > 0){
                                            $GrandTotal = $order->userPricingEnginePrice + $totalMarkup;
                                        }else{
                                            $GrandTotal = $order->userPricingEnginePrice - $totalDiscount;
                                        }
                                    @endphp

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th class="fw-bold text-secondary-light">Total</th>
                                        <th class="fw-bold text-secondary-light">{{ $Currency }} {{ number_format($totalBase) }}</th>
                                        <th class="fw-bold text-secondary-light">{{ $Currency }} {{ number_format($totalTax) }}</th>
                                        {{-- <th class="fw-bold text-secondary-light">{{ $Currency }} {{ $totalMarkup }}</th> --}}
                                        <th class="fw-bold text-secondary-light">
                                            {{ (@$totalMarkup != 0) ? '+' : '-' }}{{ $Currency }} {{ (@$totalMarkup) ? $totalMarkup : $totalDiscount }} 
                                            @if(@$Type == 'Percentage') / ({{ $Percentage }}%) @endif
                                        </th>
                                        <th class="fw-bold text-secondary-light"></th>
                                        <th class="fw-bold text-secondary-light">{{ $Currency }} {{ number_format($subTotal) }}</th>
                                        <th class="fw-bold text-secondary-light">{{ $Currency }} {{ number_format($GrandTotal) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>


                    </div>
                    <!-- End Adult -->
                </div>
                <!-- End Fare Breakdown -->


            </div>

        </div>
        <div class="offcanvas-footer row align-items-center bg-white mx-0 p-3 border-top sticky-bottom">
            {{-- @php
                $baicBillablePrice = $finalData['Flights'][0]['Fares'][0]['BillablePrice'];
            @endphp --}}
            <div class="col-6">
                <p class="text-muted mb-0">Total</p>
                <h4 class="fw-semibold text-secondary mb-0" id="billablePrice">PKR {{ number_format($GrandTotal) }}</h4>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function issueTicket(pnr, book_ref_key, button) {
        $(button).attr('disabled', true);
        showSweetAlertDelete(
            "Are you sure, You want to issue ticket",
            '',
            'warning',
            'Yes Issue',
            'No',
            function() {
                $(button).find('.spinner-border').removeClass('d-none');
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.issue.ticket')}}",
                    data: {
                        pnr,book_ref_key
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            // showSweetAlert(data.message,"success","Okay, got it!","btn btn-primary");
                            $('#order_status').text(data.message);
                            $('#passenger_ticket_data').html(data.ticketRenderHtml);
                            $('#not_ticketed').hide();
                            $(button).remove();
                        } else {
                            showSweetAlert(data.message,"warning","Okay, got it!","btn btn-danger");
                        }
                    },
                    complete: function () {
                        $(button).find('.spinner-border').addClass('d-none');
                        $(button).removeAttr('disabled');
                    }
                });
            }
        );
    }
    function updatePNR(pnr, book_ref_key, button) {
        $(button).attr('disabled', true);
        $(button).find('.spinner-border').removeClass('d-none');
        $.ajax({
            type: 'POST',
            url: "{{ route('admin.update.pnr')}}",
            data: {
                pnr,book_ref_key
            },
            success: function(data) {
                if (data.status == 'success') {
                    showSweetAlert(data.message,"success","Okay, got it!","btn btn-primary",location.reload());
                    
                } else {
                    showSweetAlert(data.message,"warning","Okay, got it!","btn btn-danger");
                }
            },
            complete: function () {
                $(button).find('.spinner-border').addClass('d-none');
                $(button).removeAttr('disabled');
            }
        });
    }
    function emailBooking(pnr, book_ref_key, button,f) {
        $(button).attr('disabled', true);
        $('#email_spiner').removeClass('d-none');
        $.ajax({
            type: 'POST',
            url: "{{ route('admin.email.booking')}}",
            data: {
                pnr,book_ref_key,f
            },
            success: function(data) {
                if (data.status == 'success') {
                    showSweetAlert(data.message,"success","Okay, got it!","btn btn-primary");
                    $('#email_spiner').addClass('d-none');
                    
                } else {
                    showSweetAlert(data.message,"warning","Okay, got it!","btn btn-danger");
                    $('#email_spiner').addClass('d-none');
                }
            },
            complete: function () {
                $('#email_spiner').addClass('d-none');
                $(button).attr('disabled', false);
            }
        });
    }
    function cancelBooking(pnr, book_ref_key, button) {
        $(button).attr('disabled', true);
        
        showSweetAlertDelete(
            "Are you sure, You want to cancel booking",
            '',
            'warning',
            'Yes Cancel',
            'No',
            function() {
                $(button).find('.spinner-border').removeClass('d-none');
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.cancel.booking')}}",
                    data: {
                        pnr,book_ref_key
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            showSweetAlert(data.message,"success","Okay, got it!","btn btn-primary",location.reload());
                        } else {
                            showSweetAlert(data.message,"warning","Okay, got it!","btn btn-danger");
                        }
                    },
                    complete: function () {
                        $(button).find('.spinner-border').addClass('d-none');
                        $(button).attr('disabled', false);
                    }
                });
            }
        );
    }
    function voidTicket(pnr, book_ref_key, button) {
        $(button).attr('disabled', true);
        
        showSweetAlertDelete(
            "Are you sure, You want to void ticket",
            '',
            'warning',
            'Yes Void',
            'No',
            function() {
                $(button).find('.spinner-border').removeClass('d-none');
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.void.ticket')}}",
                    data: {
                        pnr,book_ref_key
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            showSweetAlert(data.message,"success","Okay, got it!","btn btn-primary",location.reload());
                        } else {
                            showSweetAlert(data.message,"warning","Okay, got it!","btn btn-danger");
                        }
                    },
                    complete: function () {
                        $(button).find('.spinner-border').addClass('d-none');
                        $(button).attr('disabled', false);
                    }
                });
            }
        );
    }
    function repricePNR(pnr, book_ref_key, button) {
        showSweetAlertDelete(
            "Are you sure, You want to Reprice PNR",
            '',
            'warning',
            'Yes Reprice',
            'No',
            function() {
                $(button).find('.spinner-border').removeClass('d-none');
                $.ajax({
                    type: 'get',
                    url: "{{ route('admin.reprice.pnr')}}",
                    data: {
                        pnr,book_ref_key
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            showSweetAlert(data.message,"success","Okay, got it!","btn btn-primary",location.reload());
                        } else {
                            showSweetAlert(data.message,"warning","Okay, got it!","btn btn-danger");
                        }
                    },
                    complete: function () {
                        $(button).find('.spinner-border').addClass('d-none');
                        $(button).attr('disabled', false);
                    }
                });
            }
        );
    }
    $(document).ready(function() {
        ////////////////////////custom email option hide/show/////////////////////
        var form = $('#emailForm');
        $('#email-to-other').on('click', function() {
            if (form.hasClass('visible')) {
                form.animate({ right: '-100%' }, 500, function() {
                    form.removeClass('visible');
                });
            } else {
                form.addClass('visible').animate({ right: '0' }, 500);
            }
        });
        $('#hide-email-form').on('click', function() {
            form.animate({ right: '-100%' }, 500, function() {
                form.removeClass('visible');
            });
        });
        /////////////////////////////Send custom email////////////////////////////
        $('#send_custom_email').click(function(e) {
            e.preventDefault();
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            var email = $('#input-email').val();
            var booking_agent = $('#hide_agent').is(':checked') ? 1 : 0;
            var f = $('#with_fare').is(':checked') ? 1 : 0;
            var pnr = $(this).data('pnr');
            var book_ref_key = $(this).data('ref');

            if (email === '') {
                $('#input-email').css('border-color', 'red');
            }else if (!emailPattern.test(email)) {
                $('#input-email').css('border-color', 'red');
                alert("Please enter a valid email address.");
            }  else {
                
                $('#input-email').css('border-color', '');
                $('#send_custom_email').attr('disabled', true);
                $('#custom_email_spiner').removeClass('d-none')
                
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.email.booking')}}",
                    data: {
                        pnr,book_ref_key,f,booking_agent,email
                    },
                    success: function(data) {
                        $('#send_custom_email').attr('disabled', false);

                        if (data.status == 'success') {
                            $('#input-email').val('');
                            showSweetAlert(data.message,"success","Okay, got it!","btn btn-primary");
                            $('#custom_email_spiner').addClass('d-none');
                            
                        } else {
                            showSweetAlert(data.message,"warning","Okay, got it!","btn btn-danger");
                            $('#custom_email_spiner').addClass('d-none');
                        }
                    },
                    complete: function () {
                        $('#input-email').val('');
                        $('#custom_email_spiner').addClass('d-none');
                        $('#send_custom_email').attr('disabled', false);
                    }
                });
            }

        });
    });
</script>
@endsection

