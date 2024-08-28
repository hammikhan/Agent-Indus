@extends('admin.layouts.app')

@section('styles')
<style>
    .ribbon-box.right.ribbon-box .ribbon-two {
        left: auto;
        right: -5px;
    }
    .ribbon-box .ribbon-two {
        position: absolute;
        left: -5px;
        top: -5px;
        z-index: 1;
        overflow: hidden;
        width: 75px;
        height: 75px;
        text-align: right;
    }
    .ribbon-box.right.ribbon-box .ribbon-two span {
        left: auto;
        right: -21px;
        -webkit-transform: rotate(45deg);
        transform: rotate(45deg);
    }
    .ribbon-box .ribbon-two-booked span {
        background: #3b76e1;
    }
    .ribbon-box .ribbon-two-ticketed span {
        background: #a8cf42;
    }
    .ribbon-box .ribbon-two-booked span:before {
        border-left: 3px solid #3b76e1;
        border-top: 3px solid #3b76e1;
    }
    .ribbon-box .ribbon-two-ticketed span:before {
        border-left: 3px solid #84a62e;
        border-top: 3px solid #84a62e;
    }
    .ribbon-box .ribbon-two-cancel span:before {
        border-left: 3px solid #a6542e;
        border-top: 3px solid #a6542e;
    }
    .ribbon-box .ribbon-two span:before {
        content: "";
        position: absolute;
        left: 0;
        top: 100%;
        z-index: -1;
        border-right: 3px solid transparent;
        border-bottom: 3px solid transparent;
    }
    .ribbon-box .ribbon-two span {
        font-size: 13px;
        color: #fff;
        text-align: center;
        line-height: 20px;
        -webkit-transform: rotate(-45deg);
        transform: rotate(-45deg);
        width: 100px;
        display: block;
        -webkit-box-shadow: 0 0 8px 0 rgba(0,0,0,.06), 0 1px 0 0 rgba(0,0,0,.02);
        box-shadow: 0 0 8px 0 rgba(0,0,0,.06), 0 1px 0 0 rgba(0,0,0,.02);
        position: absolute;
        top: 19px;
        left: -21px;
        font-weight: 600;
    }
    .ribbon-box .ribbon-two {
        position: absolute;
        left: -5px;
        top: -5px;
        z-index: 1;
        overflow: hidden;
        width: 75px;
        height: 75px;
        text-align: right;
    }
    .ribbon-box .ribbon-two-danger span:after {
        border-right: 3px solid #84a62e;
        border-top: 3px solid #84a62e;
    }
    .ribbon-box .ribbon-two span:after {
        content: "";
        position: absolute;
        right: 0;
        top: 100%;
        z-index: -1;
        border-left: 3px solid transparent;
        border-bottom: 3px solid transparent;
    }
    .ribbon-box .ribbon-two span {
        font-size: 13px;
        color: #fff;
        text-align: center;
        line-height: 20px;
        -webkit-transform: rotate(-45deg);
        transform: rotate(-45deg);
        width: 100px;
        display: block;
        -webkit-box-shadow: 0 0 8px 0 rgba(0,0,0,.06), 0 1px 0 0 rgba(0,0,0,.02);
        box-shadow: 0 0 8px 0 rgba(0,0,0,.06), 0 1px 0 0 rgba(0,0,0,.02);
        position: absolute;
        top: 19px;
        left: -21px;
        font-weight: 600;
    }
    /* ------------------------------ */
    .defarture{
        border-radius: 5px;
        display: flex;
        height: 50px;
        text-align: right;
    }

    .defarture .text-left{
        font-size: 15px;
        padding: 10px 120px 10px 10px ;
        background: #89afe4;
        color: #ffffff;
        width: 30%;
        display: flex;
        border-bottom-right-radius: 43px;
    }
    .defarture .text-left img{
        margin-left: 10px;
        height: 20px;
    }
    .defarture .text-right{
        line-height: 0;
        margin: 5px 10px;
    }
    .defarture .text-right p{
        margin: 0;

    }
</style>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="card-title">Total Bookings <span class="text-muted fw-normal ms-2">({{ count($bookings)}})</span></h5>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        @foreach ($bookings as $key => $item)
                        @php
                                $customer = json_decode($item->customer_data, true);
                                
                                $flight = json_decode($item->final_data, true);
                                $LowFareSearch = $flight['LowFareSearch'];
                                $Segments = $LowFareSearch[0]['Segments'];
                                $DepartureDateTime = $Segments[0]['Departure']['DepartureDateTime'];
                                $origin = $Segments[0]['Departure']['LocationCode'];
                                $destination = '';
                                foreach ($Segments as $key => $seg) {
                                    $destination = $seg['Arrival']['LocationCode'];
                                    
                                }
                                $fares = $flight['Fares'];
                                $fareBreakDown = $fares['fareBreakDown'];
                                $total_passenger = $fareBreakDown['ADT']['Quantity'] + @$fareBreakDown['CNN']['Quantity'] + @$fareBreakDown['INF']['Quantity'];
                            @endphp
                        <div class="card ribbon-box right rounded-0 mb-2">
                            <div class="ribbon-two ribbon-two-{{ strtolower($item->status)}}">
                                <span>{{ $item->status }}</span>
                            </div>
                            <div class="card-header px-3 py-2" style="background: linear-gradient(to right, #ffa50029, transparent);">
                                <h4>
                                    <span>
                                        PNR# {{ $item->pnrCode }}
                                    </span>
                                    <div class="float-end font-size-15" style="padding-right: 50px;">
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="mdi mdi-dots-horizontal font-size-18"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end" style="">
                                                <a class="dropdown-item" href="#">View Booking</a>
                                                <a class="dropdown-item" href="#">Cancel Booking</a>
                                                <a class="dropdown-item" href="#">Issue Ticket</a>
                                            </div>
                                        </div>
                                    </div>
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="invoice-title d-flex justify-content-between">
                                    <div class="text-muted">
                                        <p class="mb-1"><strong>Customer</strong></p>
                                        <p class="mb-1"> 
                                            Name: {{ $customer['passengers'][0]['passenger_title'].' '.$customer['passengers'][0]['name'].' '.$customer['passengers'][0]['sur_name'] }}
                                        </p>
                                        <p class="mb-1">Email: {{ $customer['customer_email'] }}</p>
                                        <p class="mb-1">Total Passenger: {{ count($customer['passengers']) }}</p>
                                        <p><i class="mdi mdi-phone-outline me-1"></i> Phone: {{ $customer['customer_phone'] }}</p>
                                    </div>
                                    <div class="defarture bg-light justify-content-between">
                                        <div class="text-left">Departure <img src="assets/images/icon/fly.png" alt="">
                                        </div>
                                        {{-- @foreach ($flight['LowFareSearch'] as $segments)
                                            @dump($segments['Segments'])
                                        @endforeach       --}}
                                        <div class="text-right">
                                            <span>
                                                <h6>{{ CityNameByAirportCode($origin) }}-{{ CityNameByAirportCode($destination) }}</h6>
                                                <p>{{ date('D, d M, Y', strtotime($DepartureDateTime)) }}</p>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="price-detail" style="width: 30%">
                                        <p class="mb-1"><strong>Price Detail</strong></p>
                                        @foreach ($fares['fareBreakDown'] as $key => $item)
                                            @php
                                                if($key == 'ADT')
                                                    $passType = 'Adult';
                                                elseif($key == 'CNN')
                                                    $passType = 'Child';
                                                else
                                                    $passType = 'Infant';
                                            @endphp
                                            <p class="mb-1">
                                                {{ $passType }} X {{ $item['Quantity'] }}: <span class="text-right">{{ $fares['CurrencyCode'] }} {{ $item['Quantity'] * $item['TotalFare'] }}</span>
                                            </p>
                                        @endforeach
                                        <p class="fs-5">
                                          Total: <span class="text-right">{{ $fares['CurrencyCode'] }} {{ ($fares['TotalPrice']) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                
            </div>

        </div>
    </div>
@endsection

@section('scripts')

@endsection
