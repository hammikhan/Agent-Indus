
@if (@$finaldata)
    @if (@$fareRuleHtml)
        
    @endif
    <div class="d-grid gap-2">
        <button class="btn btn-sm waves-effect waves-light pt-2" data-bs-toggle="offcanvas" href="#fareRuleSidepanel" aria-controls="fareRuleSidepanel" style="background: #789ce0;">
            <h4 class="text-white">
                Fare Rules
            </h4>
        </button>
        <div class="offcanvas offcanvas-end" tabindex="-1" data-bs-scroll="false" id="fareRuleSidepanel"
        aria-labelledby="fareRuleSidepanelLabel">
            <div class="offcanvas-header">
                <h5 id="fareRuleSidepanelLabel">Fare Rules</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                {{-- {!! nl2br($fareRuleHtml) !!} --}}
                <div class="tab-pane active" id="fare_rules" role="tabpanel">
                    <div class="card card-body">
                        <div class="flight-itenaries">
                            @foreach ($finaldata['Flights'] as $flightIndex => $flights)
                                @php
                                    if($flightIndex == 0){
                                        $trip = "Onward";
                                    }else{
                                        $trip = "Return";
                                    }
                                    $segments = collect($flights['Segments']);
                                    $firstSegment = $segments->first();
                                    $lastSegment = $segments->last();
                                    $originCode = $firstSegment['Departure']['LocationCode'];
                                    $arrivalCode = $lastSegment['Arrival']['LocationCode'];
                                @endphp
                                <div class="flight">
                                    <div class="flight-summary d-flex justify-content-between" data-bs-toggle="collapse"
                                        data-bs-target="#flight-{{ $flightIndex }}" aria-expanded="false" aria-controls="flight-{{ $flightIndex }}">
                                        <div class="flight-info">
                                            <h5 class="mb-0">
                                                {{ CityNameByAirportCode($originCode) }} - {{ CityNameByAirportCode($arrivalCode) }}
                                                <span class="badge rounded-pill bg-primary p-2">{{ strtolower($trip) }}</span>
                                            </h5>
                                        </div>
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </div>
                                    <div class="flight-detail collapse show pt-3 text-start" id="rule-{{ strtolower($trip) }}">
                                        @if (@$fareRuleHtml != "")
                                            {!! $fareRuleHtml[$flightIndex] !!}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- ----------------Itinerary detail--------------- --}}
    @if(@$finaldata['Flights'])
        @php
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
        @foreach ($finaldata['Flights'] as $flightIndex => $flights)
            @php
                if($flightIndex == 0){
                    $trip = "Onward";
                }else{
                    $trip = "Return";
                }

                $segments = collect($flights['Segments']);
                $firstSegment = $segments->first();
                $lastSegment = $segments->last();
                $originCode = $firstSegment['Departure']['LocationCode'];
                $arrivalCode = $lastSegment['Arrival']['LocationCode'];

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
                {{-- @dump($passBreak) --}}
            <div class="card mb-2">
                <div class="accordion" id="flight_detail_{{ $flightIndex }}">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFlight">
                            <button class="accordion-button fw-semibold bg-light-blue" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFlight-{{ $flightIndex }}" aria-expanded="true" aria-controls="collapseFlight-{{ $flightIndex }}">
                                <span class="justify-content-between pe-2" style="width: 100%; display:flex;">
                                    <span>
                                        {{ $originCode }} - {{ $arrivalCode }}&nbsp;
                                        <i class="route-icon fa fa-plane" style="{{ ($trip == 'Return') ? 'transform: rotate(180deg);' : '' }}"></i>
                                    </span>
                                    @if (@$flightBrands['Name'])
                                        <span class="badge badge-outline-warning ms-2 rounded-1 text-white">
                                            <i class="fas fa-check-double"></i>
                                            {{ $flightBrands['Name'] }}
                                            {{-- {{ $flightBrands['BrandID'] }} --}}
                                        </span>
                                    @endif
                                </span>
                            </button>
                        </h2>
                        <div id="collapseFlight-{{ $flightIndex }}" class="accordion-collapse collapse show" aria-labelledby="headingFlight" data-bs-parent="#flight_detail_{{ $flightIndex }}">
                            @foreach ($flights['Segments'] as $segKey => $segment)
                                @php
                                    $flightCode = $segment['MarketingAirline']['Code'];
                                    $FlightNumber = $segment['MarketingAirline']['FlightNumber'];
                                    
                                    $DepartureCode = $segment['Departure']['LocationCode'];
                                    $ArrivalCode = $segment['Arrival']['LocationCode'];
                                    $originTerminal = @$segment['Departure']['Terminal'];
                                    $arrivalTerminal = @$segment['Arrival']['Terminal'];

                                    $departure_date =  date('d M Y',strtotime($segment['Departure']['DepartureDateTime']));
                                    $dapartTime = date('H:i', strtotime($segment['Departure']['DepartureDateTime']));
                                    $arrtivelTime = date('H:i', strtotime($segment['Arrival']['ArrivalDateTime']));


                                    $airName = AirlineNameByAirlineCode($flightCode);
                                    $airName = strlen($airName) > 20 ? substr($airName, 0, 13) . "..." : $airName;
                                    $Cabin = $segment['Cabin'];
                                    // ---------------------------------------
                                    $layover = null;
                                    if ($segKey > 0) {
                                        $currentDeparture = new DateTime($segment['Departure']['DepartureDateTime']);
                                        $previousArrival = new DateTime($flights['Segments'][$segKey - 1]['Arrival']['ArrivalDateTime']);
                                        $layover = $currentDeparture->diff($previousArrival)->format('%H:%I');
                                    }                               
                                @endphp
                                @if ($layover)
                                    <div class="mx-3 px-2 p-1 text-center" style="background: linear-gradient(to right, #ffa50029, transparent);">
                                        @php
                                            list($hours, $minutes) = explode(':', $layover);
                                            $hours = (int) $hours;
                                            $minutes = (int) $minutes;
                                        @endphp
                                        <strong>Layover Time: {{ $hours.' Hours '.$minutes.' Minutes' }}</strong>
                                    </div>
                                @endif
                                <div class="accordion-body">
                                    <div class="modal-baggage bg-soft-primary-2 d-flex justify-content-between rounded px-2 pt-2 pb-2 mb-2" style="margin: 0 -10px;">
                                        <div class="flight-fare w-50">
                                            <span>({{$DepartureCode}}-{{$ArrivalCode}})</span> 
                                        </div>
                                        <div class="flight-fare w-50 text-end">
                                            <span>{{ $departure_date }}</span> 
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="w-50">
                                            <img src="{{ asset('assets/airlines/'.$flightCode.'.png') }}" class="avatar-md" alt="{{ $flightCode }}" style="height: 20px; width: 20px; margin-top: 0;">
                                            <span>{{ $airName }}</span>
                                            
                                        </div>
                                        <div class="w-50 text-end">
                                            <i class="fas fa-plane font-size-10"></i>
                                            <span><b>{{ $flightCode }} {{ $FlightNumber }}</b></span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="w-50">
                                            Aircraft: {{ AirCraftByCode($segment['EquipType']) }}
                                        </div>
                                        @if ($segment['MarketingAirline']['Code'] != $segment['OperatingAirline']['Code'])
                                            <div class="w-50 text-end">
                                                <p class="small mb-3 text-primary">Operated By: <b>{{ AirlineNameByAirlineCode($segment['OperatingAirline']['Code']) }}</b></p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="w-33">
                                            <span>({{ $dapartTime }})</span>
                                            <i class="fa fa-plane-departure font-size-10" style="margin-right: 0;"></i>
                                        </div>
                                        <div class="w-33 text-center">
                                            <i class="fa fa-clock font-size-10"></i>
                                            <span>
                                                @php
                                                    $seghours = floor($segment['Duration'] / 60);
                                                    $segminutes = $segment['Duration'] % 60;
                                                @endphp
                                                {{ $seghours }}H {{ $segminutes }}M
                                            </span>
                                        </div>
                                        <div class="w-33 text-end">
                                            <i class="fa fa-plane-arrival font-size-10 mr-0" style="margin-right: 0;"></i>
                                            <span>({{ $arrtivelTime }})</span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="w-50">
                                            <i class="fas fa-person-seat"></i>
                                            <span>Cabin</span>
                                        </div>
                                        <div class="w-50 text-end">
                                            <span>{{ @$segment['CabinClass'] }}({{ @$flightBrands['FareBases'][$segKey]['BookingCode'] }})</span>
                                            {{-- <span>{{ $segment['CabinClass'] }}({{ $Cabin }})</span> --}}
                                        </div>
                                    </div>
                                    @if (@$originTerminal)
                                        <div class="d-flex justify-content-between mb-2">
                                            <div class="w-50">
                                                <i class="fas fa-person-seat"></i>
                                                <span>{{ $DepartureCode }}-Terminal</span>
                                            </div>
                                            <div class="w-50 text-end">
                                                <span>{{ $originTerminal }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    @if (@$arrivalTerminal)
                                        <div class="d-flex justify-content-between mb-2">
                                            <div class="w-50">
                                                <i class="fas fa-person-seat"></i>
                                                <span>{{ $ArrivalCode }}-Terminal</span>
                                            </div>
                                            <div class="w-50 text-end">
                                                <span>{{ $arrivalTerminal }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
    {{-- ----------------Baggage detail--------------- --}}
    <div class="card mb-2">
        <div class="accordion" id="baggage_detail">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingBaggage">
                    <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBaggage" aria-expanded="true" aria-controls="collapseBaggage">
                    Baggage Detail
                    </button>
                </h2>
                <div id="collapseBaggage" class="accordion-collapse collapse show" aria-labelledby="headingBaggage" data-bs-parent="#baggage_detail">
                    @if(@$finaldata['Flights'])
                        @foreach ($finaldata['Flights'] as $flightIndex => $flights)
                            @php
                                if($flightIndex == 0){
                                    $trip = "Onward";
                                }else{
                                    $trip = "Return";
                                }
                
                                $segments = collect($flights['Segments']);
                                $firstSegment = $segments->first();
                                $lastSegment = $segments->last();
                                $originCode = $firstSegment['Departure']['LocationCode'];
                                $arrivalCode = $lastSegment['Arrival']['LocationCode'];
                                if(@$brand_ref_key){
                                    $brand_ref = $brand_ref_key[$flightIndex];
                                    if (isset($flights['Fares']) && is_array($flights['Fares'])) {
                                        $filteredFares = array_filter($flights['Fares'], function ($fare) use ($brand_ref) {
                                            if($fare['RefID'] == $brand_ref){
                                                return $fare['Name'];
                                            }
                                        });
                                        if (!empty($filteredFares)) {
                                            foreach ($filteredFares as $filteredFare) {
                                                $flightBrands = $filteredFare;
                                            }
                                        }
                                    }
                                }
                            @endphp
                            
                            <div class="accordion-body">
                                <div class="modal-baggage bg-soft-primary-2 fill-muted d-flex justify-content-between rounded px-2 pt-2 pb-2 mb-2" style="margin: 0 -10px;">
                                    <div class="flight-fare w-100">
                                        <span>
                                            {{ CityNameByAirportCode($originCode) }} - {{ CityNameByAirportCode($arrivalCode) }}
                                            ({{ $trip }})
                                        </span>
                                    </div>
                                </div>
                                
                                @php
                                    if(@$brand_ref_key){
                                        $BaggagePolicy = $flightBrands['BaggagePolicy'];
                                    }else{                                        
                                        $BaggagePolicy = $flights['Fares'][0]['BaggagePolicy'];
                                    }
                                @endphp
                                @foreach ($BaggagePolicy as $key => $baggage)
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="w-50">
                                            @php
                                                $passIcon = 'male';
                                                if($baggage['PaxType'] == 'Adult'){
                                                    $passIcon = 'male';
                                                }
                                                if($baggage['PaxType'] == 'Child'){
                                                    $passIcon = 'child';
                                                }
                                                if($baggage['PaxType'] == 'Infant'){
                                                    $passIcon = 'baby';
                                                }
                                            @endphp

                                            <i class="fs-5 fa fa-{{$passIcon}}" aria-hidden="true"></i>
                                            <span>&nbsp;{{ $baggage['PaxType'] }}</span>
                                        </div>
                                        <div class="w-50 text-end">
                                            <span>{{ $baggage['Weight'] }} {{ $baggage['Unit']}}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- -----------------Price detail--------------- --}}
    <div class="card mb-2">
        <div class="accordion" id="price_detail">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingPrice">
                    <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrice" aria-expanded="true" aria-controls="collapsePrice">
                    Price Summary
                    </button>
                </h2>
                <div id="collapsePrice" class="accordion-collapse collapse show" aria-labelledby="headingPrice" data-bs-parent="#price_detail">
                    <div class="accordion-body">
                        @if(@$finaldata['Flights'])
                            @php
                                $CurrencyCode = "PKR";
                                $BasePrice = 0;
                                $Taxes = 0;
                                $BillablePrice = 0;
                                $Markup = 0;
                                $Discount = 0;
                                $Percentage = 0;
                                $RuleType = '';
                            @endphp
                            <div class="modal-fares border-0 mb-0">
                                <div class="modal-left">
                                    <!-- =================Base ========================= -->
                                    @foreach ($passBreak as $key => $base)
                                        @if ($base['BasePrice'] > 0)
                                            <p class="d-flex justify-content-between mb-0">
                                                <span>{{ $base['Quantity'] }} {{ $base['PaxType'] }} base fare:</span>
                                                <span>PKR {{ (int)$base['BasePrice'] * (int)$base['Quantity'] }}</span>
                                            </p>
                                            @php
                                                $BasePrice += (int)$base['BasePrice'] * (int)$base['Quantity'];
                                                $Markup += $base['Markup'] * $base['Quantity'];
                                                $Discount += @$base['Discount'] * $base['Quantity'];
                                                $RuleType = $base['RuleType'];
                                                $Percentage = $base['Percentage'];
                                            @endphp
                                        @endif
                                    @endforeach

                                    <h5 class="d-flex justify-content-between mt-1">
                                        @php
                                            $BasePrice = $BasePrice;
                                        @endphp
                                        <span>Total Base Fare:</span>
                                        <span>PKR {{ number_format($BasePrice) }}</span>
                                    </h5>
                                    <br>
                                    <!-- ===============Tax=============== -->
                                    @foreach ($passBreak as $tax)
                                        @if ($tax['TaxPrice'] > 0)
                                            <p class="d-flex justify-content-between mb-0">
                                                <span>{{ $base['Quantity'] }} {{ $tax['PaxType'] }} tax amount</span>
                                                <span>PKR {{ (int)$tax['TaxPrice'] * (int)$base['Quantity'] }}</span>
                                            </p>
                                            @php
                                                $Taxes += (int)$tax['TaxPrice'] * (int)$base['Quantity'];
                                            @endphp
                                        @endif
                                    @endforeach
                                    <h5 class="d-flex justify-content-between mt-1">
                                        @php
                                            $Taxes = $Taxes;
                                        @endphp
                                        <span>Total Taxes and fees:</span>
                                        <span>PKR {{ number_format($Taxes) }}</span>
                                    </h5>
                                </div>
                                <div class="modal-botom">
                                    @php
                                        if($Markup > 0){
                                            $BillablePrice = $BasePrice + $Taxes + $Markup;
                                        }else{
                                            $BillablePrice = $BasePrice + $Taxes - $Discount;
                                        }
                                    @endphp
                                    <h5 class="text-secondary-light">Total Amount:</h5>
                                    <h5 class="text-secondary-light">PKR <span class="grand_total_span">{{ number_format($BillablePrice) }}</span></h5>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif