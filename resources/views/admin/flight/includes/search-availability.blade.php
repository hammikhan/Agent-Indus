
@if($results['status'] == '200')
    @foreach($results['msg'] as $flight)
        {{-- @dump($flight) --}}
        @php
            $FareType = (int) $flight['Flights'][0]['MultiFares'];
            $airCode = $flight['MarketingAirline']['Airline'];
            $airName = AirlineNameByAirlineCode($airCode);
            $airName = strlen($airName) > 20 ? substr($airName, 0, 20) . "..." : $airName;
            $stopss = count($flight['Flights'][0]['Segments']) - 1;
            
            $currency = $flight['Flights'][0]['Fares'][0]['Currency'];
            $totalPrice = $flight['Flights'][0]['Fares'][0]['BillablePrice'];
            $NonRefundable = $flight['Flights'][0]['NonRefundable'];
            $TotalDuration = $flight['Flights'][0]['TotalDuration'];
            $index_for_all_seg = 0;
        @endphp
        <a data-bs-toggle="offcanvas" href="#flightSidepanel" aria-controls="flightSidepanel" class="flight-card card text-reset" 
                data-price="{{ $totalPrice }}" 
                data-airline="{{ $airCode }}" 
                data-api="{{ $flight['api'] }}" 
                data-stops="{{ $stopss }}" 
                data-ref-key="{{$flight['itn_ref_key']}}" 
                data-fare-type="{{ $FareType }}"
                data-duration="{{ $TotalDuration }}"
                >
            <div class="card-body">
                @foreach($flight['Flights'] as $key => $segments)  
                    <!-- Segment -->
                    @php
                        ///////////////////////////////////////////
                        $stops = '';
                        $origin = '';
                        $destination = '';
                        $AvailableSeats = '';
                        $Cabin = '';
                        
                        $departureTime = strtotime($segments['Segments'][0]['Departure']['DepartureDateTime']);
                        $arrivalTime = strtotime($segments['Segments'][0]['Arrival']['ArrivalDateTime']);
                        $totalDuration = $segments['TotalDuration'];
                        
                        $stops = count($segments['Segments']) - 1;
                        $EquipType = $segments['Segments'][0]['EquipType'];
                        $origin = $segments['Segments'][0]['Departure']['LocationCode'];
                        $departureDateTime = $segments['Segments'][0]['Departure']['DepartureDateTime'];
                        $dapartTime = date('H:i', strtotime($departureDateTime));
                        $dapartDay = date('d M', strtotime($departureDateTime));
                        $baggageWeight = $segments['Fares'][0]['BaggagePolicy'][0]['Weight'];
                        $baggageUnit = $segments['Fares'][0]['BaggagePolicy'][0]['Unit'];
                        $StopOvers = array();
                        foreach ($segments['Segments'] as $key2 => $seg) {
                            $destination = $seg['Arrival']['LocationCode'];
                            $arrtivelTime = date('H:i', strtotime($seg['Arrival']['ArrivalDateTime']));
                            $arrtivelDay = date('d M', strtotime($seg['Arrival']['ArrivalDateTime']));
                            $AvailableSeats = @$seg['AvailableSeats'];
                            $CabinClass = @$seg['CabinClass'];
                            // $Cabin = @$seg['Cabin'];
                            if ($key2 != 0) {
                                $origin_code = $seg['Departure']['LocationCode'];
                                $StopOvers[] = $origin_code;
                            }
                            $StopOversString = implode(', ', $StopOvers);

                            if(@$seg['Baggage']){
                                $baggageWeight = $seg['Baggage']['ADT']['Weight'];
                                $baggageUnit = $seg['Baggage']['ADT']['Unit'];
                            }
                            $index_for_all_seg ++;
                        }
                        $Cabin = @$segments['Fares'][0]['FareBases'][0]['BookingCode'];
                    @endphp
                    @if ($key !=0)
                        <hr>
                    @endif
                    
                    <div class="row g-3 align-items-center">
                        <div class="col-md-12">
                            <div class="row g-3 gy-4 gy-md-3">
                                <div class="col-lg-6 col-md-6 col-12 order-md-2">
                                    <div class="row g-2 align-items-center">
                                        <div class="col">
                                            <h4 class="fw-semibold mb-0">
                                                {{ $dapartTime }}
                                            </h4>
                                            <p class="text-muted mb-0">
                                                {{ $dapartDay }}
                                            </p>
                                            <h6 class="mb-0">{{ $origin }}</h6>
                                        </div>
                                        <div class="col-6 col-lg-7 route d-flex flex-column justify-content-center align-items-center">
                                            <p class="small mb-0">
                                                {{ $stops }} {{ ($stops <= 1) ? 'Stop' : 'Stops' }}
                                                @if (!empty($StopOvers))
                                                    via <b>{{ $StopOversString }}</b>
                                                @endif
                                            </p>
                                            <div class="route-line-wrapper w-100 d-flex justify-content-between align-items-center">
                                                <i class="route-icon fa-regular fa-circle"></i>
                                                <div class="route-line w-100"></div>
                                                <i class="route-icon fa fa-plane"></i>
                                            </div>
                                            <div class="hstack gap-2 mx-auto">
                                                @php
                                                    $hours = floor($totalDuration / 60);
                                                    $minutes = $totalDuration % 60;
                                                @endphp
                                                <p class="small lh-sm m-0">
                                                    <i class="fa-solid fa-clock" style="color: #9f9494;"></i>
                                                    {{ $hours }}H {{ $minutes }}M
                                                </p>
                                                <div class="vr"></div>
                                                <p class="small lh-sm mb-0">
                                                    <i class="fas fa-suitcase" style="color: #9f9494;"></i>
                                                    @if (@$baggageWeight == '')
                                                        not Included
                                                    @else
                                                        {{ $baggageWeight }} {{ $baggageUnit }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col text-end">
                                            <h4 class="fw-semibold mb-0">
                                                {{ $arrtivelTime }}
                                            </h4>
                                            <p class="text-muted mb-0">
                                                {{ $arrtivelDay }}
                                            </p>
                                            <h6 class="mb-0">
                                                {{ $destination }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-8 d-flex align-items-center order-md-1">
                                    <div class="airline-logo me-lg-4 me-2">
                                        <img src="{{asset('assets/airlines/'.$airCode.'.png')}}" alt="{{ $airName }}"
                                            class="img-fluid">
                                    </div>
                                    <div class="airline-detail">
                                        <h5 class="mb-0">{{ $airName }}</h5>
                                        @foreach ($flight['Flights'][$key]['Segments'] as $airNum)
                                            <p class="small mb-0">
                                                {{ $airNum['MarketingAirline']['Code'] }}-{{ $airNum['MarketingAirline']['FlightNumber'] }}
                                                @if ($airNum['MarketingAirline']['Code'] != $airNum['OperatingAirline']['Code'])
                                                    <span class="text-primary fw-bold"> ({{ $airNum['OperatingAirline']['Code'] }}-{{ $airNum['OperatingAirline']['FlightNumber'] }})</span>
                                                @endif
                                            </p>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-3 col-4 text-end order-md-3">
                                    <h6 class="mb-0">{{ $CabinClass }} ({{ $Cabin }})</h6>
                                    <p class="mb-0">
                                        <i class="mdi mdi-seat-passenger"></i>
                                        {{ $AvailableSeats }} {{ (@$AvailableSeats > 1 ) ? 'seats' : 'seat' }}
                                    </p>
                                    <p class="mb-0">Aircraft: {{ $EquipType }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Segment -->
                @endforeach
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between py-2">
                <span class="alert alert-{{ ($NonRefundable) ? 'danger' : 'info' }} p-1 px-3 rounded-1 mb-0">
                    {{ ($NonRefundable) ? 'Non Refundable' : 'Refundable' }}
                </span>
                <div class="price text-end mt-2 mt-sm-0">
                    <label class="md-block d-block d-sm-inline">Source: {{ $flight['api'] }}</label>
                    <h4 class="fw-semibold text-secondary mb-0 fs-50 md-block d-block d-sm-inline ms-lg-3">{{ $currency }} {{ number_format((int)$totalPrice) }}</h4>
                </div>
                {{-- <div class="price text-end">
                    <label for="">Source: {{ $flight['api'] }}</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <h4 class="fw-semibold text-secondary mb-0 fs-50" style="display: inline;">{{ $currency }} {{ number_format((int)$totalPrice) }}</h4>
                </div> --}}

            </div>
        </a>
    @endforeach
@endif



