<div class="offcanvas-header">
    <h5 id="flightSidepanelLabel">Flight Details</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
</div>
<div class="offcanvas-body">
    <!-- Tabs -->
    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
        @if ($offerData['finaldata']['Flights'][0]['MultiFares'] == false)
            @php
                $activeTabe = 'active'
            @endphp
        @else
            @php
                $activeTabe = ''
            @endphp
        @endif

        @if ($offerData['finaldata']['Flights'][0]['MultiFares'])
        <li class="nav-item active">
            <a class="nav-link active" data-bs-toggle="tab" href="#fares" role="tab" aria-selected="true">
                Fare Option
            </a>
        </li>
        @endif
        <li class="nav-item {{ $activeTabe }}">
            <a class="nav-link {{ $activeTabe }}" data-bs-toggle="tab" href="#itineraries" role="tab" aria-selected="false">
                Flight Itineraries
            </a>
        </li>
        @if (!$offerData['finaldata']['Flights'][0]['MultiFares'])
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#fare_rules" role="tab" aria-selected="false">
                Fare Rules
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#baggage" role="tab" aria-selected="false">
                Baggage
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#fareBreakdown" role="tab" aria-selected="false">
                Fare Breakdown
            </a>
        </li>
    </ul>
    <div class="tab-content text-muted">
        <!---------------------------------------- Fare Options ------------------------------------------->
        @if ($offerData['finaldata']['Flights'][0]['MultiFares'])
        <div class="tab-pane fare-options active" id="fares" role="tabpanel">
            @php
                $basicFareSingle = $offerData['finaldata']['Flights'][0]['Fares'][0]['SingleFlightFare'];
                $baicBillablePrice = $offerData['finaldata']['Flights'][0]['Fares'][0]['BillablePrice'];
            @endphp
            <div class="card card-body">
                @if(auth('admin')->user()->can('Book-PNR'))
                <form action="{{ route('admin.flight.checkout') }}" method="POST" id="flight-form">
                    @csrf
                    <input type="hidden" name="itn_ref_key" value="{{ $offerData['ref_key'] }}">
                @else
                    <form action="#" method="POST">
                @endif
                    <!-- Tabs -->
                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                        @foreach($offerData['finaldata']['Flights'] as $flightIndex => $flights)
                            @php
                                $origin = $flights['Segments'][0]['Departure']['LocationCode'];
                                $destination = end($flights['Segments'])['Arrival']['LocationCode'];
                            @endphp
                            <li class="nav-item">
                                <a class="nav-link {{ ($flightIndex == 0) ? 'active' : ''}}" data-bs-toggle="tab" href="#flight-{{ $flightIndex }}" role="tab"
                                    aria-selected="true">
                                    {{ $origin }}
                                    <i class="fa-solid fa-plane"></i>
                                    {{ $destination }}
                                    <p class="small mb-0 selected-brand-{{ $flightIndex }}">No package selected</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="tab-content text-muted">
                        @foreach($offerData['finaldata']['Flights'] as $flightIndex => $flights)
                        <div class="tab-pane py-3 {{ ($flightIndex == 0) ? 'active' : ''}}" id="flight-{{ $flightIndex }}" role="tabpanel">
                            <div class="row g-2" >
                                @foreach($flights['Fares'] as $key => $brands)
                                {{-- @dump($brands) --}}
                                <div class="col-md-4 ">
                                    <input type="radio" class="btn-check package-item" value="{{ $brands['RefID'] }}" data-brandname="{{ $brands['Name'] }}" data-baggagepolicy="{{ json_encode($brands['BaggagePolicy']) }}" data-passengerfares="{{ json_encode($brands['PassengerFares']) }}" name="brand_ref_key[{{$flightIndex}}]" data-flightindex="{{ $flightIndex }}" id="{{ $brands['RefID'] }}" data-id="{{ $brands['RefID'] }}" data-flightSingle="{{ $brands['SingleFlightFare'] }}" data-basicSingle="{{ $basicFareSingle }}" data-baicBillablePrice="{{ $baicBillablePrice }}" {{ ($key == 0) ? 'checked' : ''}} autocomplete="off">
                                    <label class="btn btn-package w-100 p-0" for="{{$brands['RefID']}}" tabindex="-1">
                                        <div class="card mb-0">
                                            <div class="card-header py-2">
                                                <h5 class="mb-0">{{ $brands['Name'] }} ({{ @$brands['FareBases'][0]['BookingCode'] }})</h5>
                                            </div>
                                            <div class="card-body py-2">
                                                <div class="baggage-info">
                                                    <h6 class="mb-3">Baggage</h6>
                                                    <p class="mb-1">
                                                        <i class="fa-solid fa-suitcase text-primary"></i> 7 KG cabin
                                                        baggage
                                                    </p>
                                                    <p class="mb-1">
                                                        <i class="fa-solid fa-suitcase-rolling text-primary"></i>
                                                        {{ @$brands['BaggagePolicy'][0]['Weight'] }} {{ @$brands['BaggagePolicy'][0]['Unit'] }}
                                                    </p>
                                                </div>
                                                <hr>
                                                @if (@$brands['BrandFeatures'])
                                                <div class="policies">
                                                    <h6 class="mb-3">Included</h6>
                                                    @foreach (array_reverse($brands['BrandFeatures']) as $included)
                                                    @php
                                                        if($included == 'BEVERAGE'){
                                                            $includeIcon = 'fas fa-hamburger';
                                                        }elseif ($included == 'MEAL VOUCHER') {
                                                            $includeIcon = 'fas fa-hamburger';
                                                        }elseif ($included == 'ENTERTAINMENT') {
                                                            $includeIcon = 'fas fa-music';
                                                        }elseif ($included == 'CHECKED BAGGAGE') {
                                                            $includeIcon = 'fas fa-suitcase-rolling';
                                                        }elseif (preg_match('/\bMILES\b/', $included)) {
                                                            $includeIcon = 'fa fa-star';
                                                        }else{
                                                            $includeIcon = 'fas fa-suitcase-rolling';
                                                        }
                                                    @endphp
                                                    <p class="mb-1">
                                                        <i class="{{ $includeIcon }} text-primary"></i>
                                                        {{ $included }}
                                                    </p>
                                                    @endforeach
                                                </div>
                                                <hr>
                                                @endif
                                                @if (@$brands['AdditionalBrandFeatures'])
                                                <div class="included">
                                                    <h6 class="mb-3">At additional cost</h6>
                                                    @foreach (array_reverse($brands['AdditionalBrandFeatures']) as $additional)
                                                    @php
                                                        if(preg_match('/\bSEAT\b/', $additional)){
                                                            $additionalIcon = 'mdi mdi-seat-passenger';
                                                        }elseif (preg_match('/\bINTERNET\b/', $additional)) {
                                                            $additionalIcon = 'fas fa-wifi';
                                                        }elseif ($additional == 'ENTERTAINMENT') {
                                                            $additionalIcon = 'fas fa-music';
                                                        }elseif ($additional == 'CHECKED BAGGAGE') {
                                                            $additionalIcon = 'fas fa-suitcase-rolling';
                                                        }elseif (preg_match('/\bMILES\b/', $additional)) {
                                                            $additionalIcon = 'fa fa-star';
                                                        }else{
                                                            $additionalIcon = 'fas fa-suitcase-rolling';
                                                        }
                                                    @endphp
                                                    <p class="mb-1">
                                                        <i class="{{ $additionalIcon }} text-primary"></i> {{ $additional }}
                                                    </p>
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                            <div class="card-footer py-2 text-center">
                                                <h5 class="text-primary fw-semibold mb-0">
                                                    {{ $brands['Currency'] }} {{ number_format($brands['BillablePrice']) }}
                                                </h5>
                                            </div>
                                        </div>
                                    </label>

                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
        @else
            @if(auth('admin')->user()->can('Book-PNR'))
                <form action="{{ route('admin.flight.checkout') }}" method="POST" id="flight-form">
                    @csrf
                    <input type="hidden" name="itn_ref_key" value="{{ $offerData['ref_key'] }}">

                </form>
            @endif
        @endif
        <!-- End Fare Option -->
        <!----------------------------------------- Itineraries --------------------------------------------->
        <div class="tab-pane {{ $activeTabe }}" id="itineraries" role="tabpanel">
            <div class="card card-body">
                <div class="flight-itenaries">
                    @foreach($offerData['finaldata']['Flights'] as $flightIndex => $flights)
                        @php
                            if($flightIndex == 0){
                                $trip = "Onward";
                            }else{
                                $trip = "Return";
                                echo '<hr>';
                            }
                        @endphp
                        <div class="flight">
                            @php
                                $segments = collect($flights['Segments']);
                                $firstSegment = $segments->first();
                                $lastSegment = $segments->last();
                                $originCode = $firstSegment['Departure']['LocationCode'];
                                $arrivalionCode = $lastSegment['Arrival']['LocationCode'];
                            @endphp
                            <div class="flight-summary d-flex justify-content-between" data-bs-toggle="collapse"
                                data-bs-target="#flight-{{ $flightIndex }}" aria-expanded="false" aria-controls="flight-{{ $flightIndex }}">
                                <div class="flight-info">
                                    <h5 class="mb-0">
                                        {{ CityNameByAirportCode($originCode) }} - {{ CityNameByAirportCode($arrivalionCode) }}
                                        
                                        @if ($offerData['finaldata']['Flights'][0]['MultiFares'])
                                            @php
                                                $brandName0 = @$offerData['finaldata']['Flights'][0]['Fares'][0]['Name'];
                                            @endphp
                                            <span class="badge badge-outline-warning ms-2 rounded-1">
                                                <i class="fas fa-check-double"></i>
                                                <span class="selected-brand-{{ $flightIndex }}">{{ $brandName0 }}</span>
                                            </span>
                                        @endif
                                        <span class="badge rounded-pill bg-primary p-2">{{ $trip }}</span>
                                    </h5>
                                </div>
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>

                            <div class="flight-detail collapse show" id="flight-{{ $flightIndex }}">

                                @foreach ($flights['Segments'] as $segmentKey => $segment)
                                    @php
                                        $originSeg = $segment['Departure']['LocationCode'];
                                        $arrivalSeg = $segment['Arrival']['LocationCode'];
                                        $originTerminal = @$segment['Departure']['Terminal'];
                                        $arrivalTerminal = @$segment['Arrival']['Terminal'];

                                        $DepartureDateTime = $segment['Departure']['DepartureDateTime'];
                                        $ArrivalDateTime = $segment['Arrival']['ArrivalDateTime'];

                                        $flightCode = $segment['MarketingAirline']['Code'];
                                        $flightNumber = $segment['MarketingAirline']['FlightNumber'];

                                        $destination = end($flights['Segments'])['Arrival']['LocationCode'];
                                        $layover = null;
                                        if ($segmentKey > 0) {
                                            $currentDeparture = new DateTime($segment['Departure']['DepartureDateTime']);
                                            $previousArrival = new DateTime($flights['Segments'][$segmentKey - 1]['Arrival']['ArrivalDateTime']);
                                            $layover = $currentDeparture->diff($previousArrival)->format('%H:%I');
                                        }
                                    @endphp
                                    {{-- @dump($segment) --}}
                                    @if ($layover)
                                        <div class="segment-layover">
                                            <div class="border rounded-1 p-2 my-3 text-center">
                                                <i class="fa-solid fa-clock me-2"></i>
                                                @php
                                                    list($hours, $minutes) = explode(':', $layover);
                                                    $hours = (int) $hours;
                                                    $minutes = (int) $minutes;
                                                @endphp
                                                Stop duration: {{ $hours.' Hours '.$minutes.' Minutes' }}
                                            </div>
                                        </div>
                                    @endif
                                    <div class="flight-segment">
                                        <div class="d-flex mt-3">
                                            <div class="d-flex flex-column justify-content-between">
                                                <div class="departure">
                                                    <h6 class="text-nowrap mb-0">{{ date('H:i', strtotime($DepartureDateTime)) }}</h6>
                                                    <p class="small text-nowrap mb-0">{{ date('M d', strtotime($DepartureDateTime)) }}</p>
                                                </div>
                                                <div class="duration my-4">
                                                    <p class="small">
                                                        @php
                                                        // dd($segment['Duration']);
                                                            $seghours = floor($segment['Duration'] / 60);
                                                            $segminutes = $segment['Duration'] % 60;
                                                        @endphp
                                                        {{ $seghours }}H {{ $segminutes }}M
                                                    </p>
                                                </div>
                                                <div class="arrival">
                                                    <h6 class="text-nowrap mb-0">{{ date('H:i', strtotime($ArrivalDateTime)) }}</h6>
                                                    <p class="small text-nowrap mb-0">{{ date('M d', strtotime($ArrivalDateTime)) }}</p>
                                                </div>
                                            </div>
                                            <div class="route-line-wrapper d-flex flex-column justify-content-between align-items-stretch position-relative mx-2 mx-md-3">
                                                <i class="route-icon fa-regular fa-circle"></i>
                                                <div class="route-line"></div>
                                                <i class="route-icon fa fa-plane-up"></i>
                                            </div>
                                            <div class="mt-0 d-flex flex-column justify-content-between">
                                                <div class="departure">
                                                    <h6 class="mb-0">{{ AirportByCode($originSeg) }}</h6>
                                                    <p class="small mb-0">
                                                        {{ CityNameByAirportCode($originSeg) }}
                                                        @if (@$originTerminal)
                                                            | <b>Terminal: {{ $originTerminal }}</b>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="airline my-4 d-flex align-items-center">
                                                    <div class="airline-icon me-3" style="max-width: 50px;">
                                                        <img class="img-fluid" src="{{ asset('assets/airlines/'.$flightCode.'.png') }}"
                                                            alt="{{ $flightCode }}" style="height: 50px">
                                                    </div>
                                                    <div class="airline-detail">
                                                        <h6 class="mb-0">{{ AirlineNameByAirlineCode($flightCode) }}</h6>
                                                        <p class="small mb-0">
                                                            {{ $flightCode }}-{{ $flightNumber }}
                                                            @if ($segment['MarketingAirline']['Code'] != $segment['OperatingAirline']['Code'])
                                                                <span> | ({{ $segment['OperatingAirline']['Code'] }}-{{ $segment['OperatingAirline']['FlightNumber'] }})</span>
                                                            @endif
                                                            <span> - Economy </span>
                                                        </p>
                                                        <p class="small mb-0">Aircraft: {{ AirCraftByCode($segment['EquipType']) }}</p>
                                                        @if ($segment['MarketingAirline']['Code'] != $segment['OperatingAirline']['Code'])
                                                            <p class="small mb-0 text-primary">Operated By: <b>{{ AirlineNameByAirlineCode($segment['OperatingAirline']['Code']) }}</b></p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="arrival">
                                                    <h6 class="mb-0">{{ AirportByCode($arrivalSeg) }}</h6>
                                                    <p class="small mb-0">
                                                        {{ CityNameByAirportCode($arrivalSeg) }}
                                                        @if (@$arrivalTerminal)
                                                        | <b>Terminal: {{ $arrivalTerminal }}</b>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    
                                @endforeach

                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- End Itineraries -->
        <!----------------------------------------- Fare Rules ------------------------------------------->
        @if (!$offerData['finaldata']['Flights'][0]['MultiFares'])
        <div class="tab-pane" id="fare_rules" role="tabpanel">
            <div class="card card-body">
                <div class="flight-itenaries">
                    @foreach($offerData['finaldata']['Flights'] as $flightIndex => $flights)
                        @php
                            if($flightIndex == 0){
                                $trip = "Onward";
                            }else{
                                $trip = "Return";
                                echo '<hr>';
                            }
                            $segments = collect($flights['Segments']);
                            $firstSegment = $segments->first();
                            $lastSegment = $segments->last();
                            $originCode = $firstSegment['Departure']['LocationCode'];
                            $arrivalionCode = $lastSegment['Arrival']['LocationCode'];
                        @endphp
                        <div class="flight">
                            <div class="flight-summary d-flex justify-content-between" data-bs-toggle="collapse"
                                data-bs-target="#flight-{{ $flightIndex }}" aria-expanded="false" aria-controls="flight-{{ $flightIndex }}">
                                <div class="flight-info">
                                    <h5 class="mb-0">
                                        {{ CityNameByAirportCode($originCode) }} - {{ CityNameByAirportCode($arrivalionCode) }}
                                        <span class="badge rounded-pill bg-primary p-2">{{ strtolower($trip) }}</span>
                                    </h5>
                                </div>
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                            <div class="flight-detail collapse show pt-3" id="rule-{{ strtolower($trip) }}">
                                                                
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <!-- End Adult -->
        </div>
        @endif
        <!------------------------------------------- Baggage ------------------------------------------------>
        <div class="tab-pane" id="baggage" role="tabpanel">
            <div class="card card-body">
                <div class="flight-baggage">

                    @foreach($offerData['finaldata']['Flights'] as $flightIndex => $flights)
                        @php
                            if($flightIndex == 0){
                                $trip = "Departure";
                                $tabShow = 'show';
                            }else{
                                $trip = "Return";
                                $tabShow = '';
                                echo '<hr>';
                            }
                            $segments = collect($flights['Segments']);
                            $firstSegment = $segments->first();
                            $lastSegment = $segments->last();
                            $originCode = $firstSegment['Departure']['LocationCode'];
                            $arrivalionCode = $lastSegment['Arrival']['LocationCode'];
                            $totalDuration = str_replace([" Hours", " Minutes"], ["H", "M"], $flights['TotalDuration']);
                        @endphp
                        <div class="flight">
                            <div class="flight-summary d-flex justify-content-between" data-bs-toggle="collapse"
                                data-bs-target="#baggage-{{ $flightIndex }}" aria-expanded="false" aria-controls="baggage-{{ $flightIndex }}">
                                <div class="flight-info">
                                    <h5 class="mb-0">
                                        {{ CityNameByAirportCode($originCode) }} - {{ CityNameByAirportCode($arrivalionCode) }}
                                        <span class="badge badge-outline-primary ms-2 rounded-1">{{ $trip }}</span>
                                    </h5>
                                    <p class="small mb-0">
                                        @php
                                            $hours = floor($totalDuration / 60);
                                            $minutes = $totalDuration % 60;
                                        @endphp
                                    
                                        Flight duration: {{ $hours }}H {{ $minutes }}M
                                    </p>
                                </div>
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>

                            <div class="flight-detail collapse show" id="baggage-{{ $flightIndex }}">

                                <!-- Baggage Information -->
                                <div class="table-responsive">
                                    <table class="table table-striped" id="bagg-brakdown-{{$flightIndex}}">
                                        <thead>
                                            <tr>
                                                <th>Passenger</th>
                                                <th>Check in</th>
                                                <th>Cabin</th>
                                            </tr>
                                        </thead>
                                        <tbody id="baggage">
                                            @foreach ($flights['Fares'][0]['BaggagePolicy'] as $bagg)
                                                <tr id="baggage-tr">
                                                    <td>{{ $bagg['PaxType']}}</td>
                                                    <td>{{ $bagg['Weight'] }} {{ $bagg['Unit'] }}</td>
                                                    <td>7 KG</td>
                                                </tr>
                                            @endforeach
                                            {{-- @dump($flights['Fares']) --}}
                                        </tbody>
                                    </table>
                                </div>
                                <!-- End Baggage Information  -->

                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- End Baggage -->
        <!----------------------------------------- Fare Breakdown ------------------------------------------->
        <div class="tab-pane" id="fareBreakdown" role="tabpanel">
            <div class="card card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="fare-breakdown">
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
                            @foreach($offerData['finaldata']['Flights'][0]['Fares'][0]['PassengerFares'] as $fareIndex => $fare)
                                @php
                                // dd($fare);
                                    $TotalPerPax = $fare['TotalPrice'] * $fare['Quantity'];
                                    $Currency = $fare['Currency'];
                                    $totalBase += $fare['BasePrice'];
                                    $totalTax += $fare['Taxes'];
                                    if(@$fare['Markup']){
                                        $totalMarkup += @$fare['Markup'] * $fare['Quantity'];
                                    }else{
                                        $totalDiscount += @$fare['Discount'] * $fare['Quantity'];
                                    }
                                    $subTotal += $fare['TotalPrice'];
                                    $GrandTotal += $TotalPerPax;
                                    $Type = @$fare['Type'];
                                    $Percentage = @$fare['Percentage'];
                                @endphp
                                <tr>
                                    <td>{{ $fare['PaxType'] }}</td>
                                    <td>{{ $fare['Currency'] }} {{ number_format($fare['BasePrice']) }}</td>
                                    <td>{{ $fare['Currency'] }} {{ number_format($fare['Taxes']) }}</td>
                                    <td>{{ (@$fare['Markup'] != 0) ? '+' : '-' }}{{ $fare['Currency'] }} {{ (@$fare['Markup']) ? $fare['Markup'] * @$fare['Quantity'] : @$fare['Discount'] * $fare['Quantity'] }} @if(@$fare['Type'] == 'Percentage') / ({{$fare['Percentage']}}%) @endif</td>
                                    <td>{{ $fare['Quantity'] }}</td>
                                    <td>{{ $fare['Currency'] }} {{ number_format($fare['TotalPrice']) }}</td>
                                    <td class="fw-semibold">{{ $fare['Currency'] }} {{ number_format($TotalPerPax) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="fw-bold text-secondary-light">Total</th>
                                <th class="fw-bold text-secondary-light">{{ $Currency }} {{ number_format($totalBase) }}</th>
                                <th class="fw-bold text-secondary-light">{{ $Currency }} {{ number_format($totalTax) }}</th>
                                {{-- <th class="fw-bold text-secondary-light">{{ $Currency }} {{ $totalMarkup }}</th> --}}
                                <th class="fw-bold text-secondary-light">
                                    {{ (@$totalMarkup != 0) ? '+' : '-' }}{{ $fare['Currency'] }} {{ (@$totalMarkup) ? $totalMarkup : $totalDiscount }} 
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
    @php
        $baicBillablePrice = $offerData['finaldata']['Flights'][0]['Fares'][0]['BillablePrice'];
    @endphp
    <div class="col-6">
        <p class="text-muted mb-0">Total</p>
        <h4 class="fw-semibold text-secondary mb-0" id="billablePrice">PKR {{ number_format($baicBillablePrice) }}</h4>
    </div>
    @if(auth('admin')->user()->can('Book-PNR'))
        <div class="col-6 text-end">
            <a class="btn btn-primary" id="book-flight">Continue</a>
        </div>
    @endif
</div>

<script>
    $(document).ready(function() {
        $('input[type="radio"][name^="brand_ref_key"]').change(function() {
            var flightIndex = $(this).data('flightindex');
            
            var basicSingleFare = $('input[name="brand_ref_key[0]"]:checked').data('basicsingle');
            var basicBillablePrice = $('input[name="brand_ref_key[0]"]:checked').data('baicbillableprice');
            var selectedFareBrandOneway = $('input[name="brand_ref_key[0]"]:checked').data('flightsingle');
            var selectedFareBrandRoundTrip = 0;
            var difference2 = 0;

            if ($('input[name="brand_ref_key[1]"]:checked').length > 0) {
                selectedFareBrandRoundTrip = $('input[name="brand_ref_key[1]"]:checked').data('flightsingle');
                difference2 = selectedFareBrandRoundTrip - basicSingleFare;
            }

            var difference = selectedFareBrandOneway - basicSingleFare;
            var totalBillable = (difference + difference2) + basicBillablePrice;

            $('#billablePrice').text(totalBillable.toLocaleString());
            // //////////////////Baggage and Fares change on brand change///////////////
            var brandname = $('input[name="brand_ref_key['+flightIndex+']"]:checked').data('brandname');
            $('.selected-brand-'+flightIndex).text(brandname);
            
            var baggagepolicy = $('input[name="brand_ref_key['+flightIndex+']"]:checked').data('baggagepolicy');
            var baggageBreakdown = '';
            $.each(baggagepolicy, function(baggIndex, bagg) {
                baggageBreakdown += `
                    <tr>
                        <td>${bagg.PaxType}</td>
                        <td>${bagg.Weight} ${bagg.Unit}</td>
                        <td>7 KG</td>
                    </tr>
                    `;
            });
            $('#bagg-brakdown-'+flightIndex+' tbody').html(baggageBreakdown);
            ////////////////////////////////////////////////////////////////////////////////////////
            var currencyCode = "PKR";
            var passBreak = [];

            ['Adult', 'Child', 'Infant'].forEach(function(paxType) {
                passBreak.push({
                    'PaxType': paxType,
                    'Quantity': 0,
                    'BasePrice': 0,
                    'TaxPrice': 0,
                    'TotalPaxPrice': 0
                });
            });

            var passengerFares = $('input[name="brand_ref_key[0]"]:checked').data('passengerfares');
            $.each(passengerFares, function(index, fare) {
                passBreak[index]['Quantity'] += parseInt(fare['Quantity']);

                passBreak[index]['BasePrice'] += parseInt(fare['BasePrice']) / 2;
                passBreak[index]['TaxPrice'] += parseInt(fare['Taxes']) / 2;
                passBreak[index]['TotalPaxPrice'] += parseInt(fare['TotalPrice']) / 2;
                console.log('Brand1----'+parseInt(fare['BasePrice']) / 2);
            });
            
            if ($('input[name="brand_ref_key[1]"]:checked').length > 0) {
                var passengerfares2 = $('input[name="brand_ref_key[1]"]:checked').data('passengerfares');
                $.each(passengerfares2, function(index2, fare2) {
                    passBreak[index2]['BasePrice'] += parseInt(fare2['BasePrice']) / 2;
                    passBreak[index2]['TaxPrice'] += parseInt(fare2['Taxes']) / 2;
                    passBreak[index2]['TotalPaxPrice'] += parseInt(fare2['TotalPrice']) / 2;
                    console.log('Brand2----'+parseInt(fare2['BasePrice']) / 2);
                });
            }
            
            /////////////////////////////////////////////////////////////////////////////////////
            var fareBreakdown = '';

            var PaxBasePrice = 0;
            var TotalBasePrice = 0;
            var TotalTaxPrice = 0;
            var TotalPerPax = 0;
            var SubTotal = 0;
            var GrandTotal = 0;
            $.each(passBreak, function(index, paxfare) {
                PaxBasePrice = paxfare.BasePrice;
                console.log('TotalBase-'+PaxBasePrice);
                TotalPerPax = paxfare.TotalPaxPrice * paxfare.Quantity;

                TotalBasePrice += paxfare.BasePrice;
                TotalTaxPrice += paxfare.TaxPrice;
                SubTotal += paxfare.TotalPaxPrice;
                GrandTotal += TotalPerPax;
                fareBreakdown += `
                    <tr>
                        <td>${paxfare.PaxType}</td>
                        <td>PKR ${PaxBasePrice}</td>
                        <td>PKR ${paxfare.TaxPrice}</td>
                        <td>PKR 0</td>
                        <td>${paxfare.Quantity}</td>
                        <td>PKR ${paxfare.TotalPaxPrice}</td>
                        <td class="fw-semibold">PKR ${TotalPerPax}</td>
                    </tr>
                    `;
            });
            $('#fare-breakdown tbody').html(fareBreakdown);
            var totalBreakdown = `
            <tr>
                <td class="fw-bold text-secondary-light">Total</td>
                <td class="fw-bold text-secondary-light">PKR ${formatCurrency(TotalBasePrice)}</td>
                <td class="fw-bold text-secondary-light">PKR ${formatCurrency(TotalTaxPrice)}</td>
                <td class="fw-bold text-secondary-light">PKR 0</td>
                <td class="fw-bold text-secondary-light"></td>
                <td class="fw-bold text-secondary-light">PKR ${formatCurrency(SubTotal)}</td>
                <td class="fw-bold text-secondary-light">PKR ${formatCurrency(GrandTotal)}</td>
                </tr>
                `;
                
            $('#fare-breakdown tfoot').html(totalBreakdown);

            // console.log(passBreak);
        });
    });
    function formatCurrency(amount) {
        return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
</script>
<script>
    $(document).ready(function() {
        $('#book-flight').click(function() {
            $('#flight-form').submit();
        });
    });
</script>