@if($results['status'] == '200')
    @php
        $apisArray = array();
        $airlineArray = array();
        $stopsArray = array();
        $minPrice = PHP_INT_MAX;
        $maxPrice = PHP_INT_MIN;
        foreach($results['msg'] as $flight){
            $total_price = $flight['Flights'][0]['Fares'][0]['TotalFare'];
            $minPrice = min($minPrice, $total_price);
            $maxPrice = max($maxPrice, $total_price);
            $api = $flight['api'];
            $airline = $flight['MarketingAirline']['Airline'];

            // Check if the value already exists in the arrays
            if (!in_array($api, $apisArray)) {
                $apisArray[] = $api;
            }

            if (!in_array($airline, $airlineArray)) {
                $airlineArray[] = $airline;
            }
            foreach ($flight['Flights'] as $key => $segments) {
                $stops = count($segments['Segments']) - 1;
                if (!in_array($stops, $stopsArray)) {
                    $stopsArray[] = $stops;
                }
            }
        }
        
    @endphp
    <div class="collapse collapse-horizontal filters d-lg-block card card-body" id="filters">
        <form>
            <div class="row g-3">
                <div class="col-5 col-lg-6">
                    <h5 class="mb-0">Filter</h5>
                </div>
                <div class="col-5 col-lg-6 text-end">
                    {{-- <button type="reset" class="fw-bold border-0 p-1 py-0 rounded-5" id="resetButton"> --}}
                    <button type="reset" class="fw-bold bg-transparent text-primary border-0 p-1 py-0 rounded-5" id="resetButton">
                        <i class="fa-solid fa-arrows-rotate rounded-5 " id="rotateIcon"></i>
                    </button>
                </div>
                <div class="col-2 d-block d-lg-none text-end">
                    <button type="button" class="btn-close" data-bs-toggle="collapse"
                        data-bs-target="#filters" aria-expanded="false"
                        aria-controls="filters"></button>
                </div>
            </div>
            <hr class="border-3">
            <!-- Departure Time -->
            
            <!-- Sort By -->
            <div class="price-filter">
                {{-- <select name="sort_by" id="sort_by" class="form-control">
                    <option value="">Sort By</option>
                    <option value="price_low_high">Price Low - High</option>
                    <option value="price_high_low">Price High - Low</option>
                </select> --}}
                <select onchange="Sorting()" id="sorting-filter" class="form-control">
                    <option style="display: none;">Flight Sorting</option>
                    <option>Price (Lowest)</option>
                    <option>Price (Highest)</option>
                    <option>Duration (Shortest)</option>
                    <option>Duration (Longest)</option>
                </select>
                
            </div>
            <hr class="border-0">
            <!-- Price -->
            <div class="price-filter">
                <h6>Price</h6>
                <div class="row">
                    <div class="col-6">
                        <div class="price-value">
                            <p class="min-price">PKR 44995</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="price-value">
                            <p class="max-price text-end">PKR 47865</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div id="price-range"></div>
                    </div>
                </div>
            </div>
            <hr class="border-0">
            <!-- Stops -->
            <div class="api-filter">
                <h6>APIs</h6>
                <div class="row g-3">
                    <div class="col-12">
                        @foreach ($apisArray as $api)
                            <div class="form-check">
                                <input class="form-check-input api" type="checkbox" id="{{ $api }}" value="{{ $api }}">
                                <label class="form-check-label d-flex justify-content-between" for="{{ $api }}">
                                    <span class="text-blackish-gray">{{ $api }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <hr class="border-0">
            <div class="stops-filter">
                <h6>Stops</h6>
                <div class="row g-3">
                    <div class="col-12">
                        @foreach ($stopsArray as $stop)
                            <div class="form-check">
                                <input class="form-check-input stops" type="checkbox" id="stop-{{ $stop }}" value="{{ $stop }}">
                                <label class="form-check-label d-flex justify-content-between" for="stop-{{ $stop }}">
                                    <span class="text-blackish-gray">{{ ($stop == 0) ? "Direct" : (($stop == 1) ? "1 Stop" : $stop . ' Stops') }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <hr class="border-0">
            <!-- Airline -->
            <div class="airline-filter">
                <h6>Airlines</h6>
                <div class="row g-3">
                    <div class="col-12">
                        @foreach ($airlineArray as $air)
                            <div class="form-check">
                                <input class="form-check-input airlines" type="checkbox" id="{{ $air }}" value="{{ $air }}">
                                <label class="form-check-label d-flex justify-content-between" for="{{ $air }}">
                                    <span class="text-blackish-gray">
                                        @if (strlen(AirlineNameByAirlineCode($air)) > 20)
                                            {{ substr(AirlineNameByAirlineCode($air), 0, 20) . '...' }} ({{ $air }})
                                        @else
                                            {{ AirlineNameByAirlineCode($air) }} ({{ $air }})
                                        @endif
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>
    </div>

@endif
<script src="{{ asset('assets/libs/nouislider/nouislider.min.js') }}"></script>
<script>
    $(document).ready(function () {
        // Event listener for filter changes
        $('input[type="checkbox"]').on('change', function () {
            applyFilters();
        });

        /////////////////////////////////Reset Filter/////////////////////////////////////////
        $('#resetButton').click(function() {
            
            $('.api, .stops, .airlines').prop('checked', false);
            
            // Reset the price slider (assuming you are using noUiSlider)
            slider.noUiSlider.set([slider.noUiSlider.options.range.min, slider.noUiSlider.options.range.max]);

            // Reapply filters
            applyFilters();

            var icon = $('#rotateIcon');
            icon.addClass('rotate');
            setTimeout(function() {
                icon.removeClass('rotate');
            }, 1000);
        });
        applyFilters();
        
    });
    /////////////////////////////////Sort By/////////////////////////////////////////
    function Sorting(){
        var filterType = $('#sorting-filter').val();
        console.log(filterType);
        if(filterType == 'Price (Lowest)'){
            var contacts = $('#appendAvailability'),
             cont = contacts.children('a');
             cont.detach().sort(function(a, b) {
                var astts = $(a).data('price');
                var bstts = $(b).data('price')
                return (astts > bstts) ? (astts > bstts) ? 1 : 0 : -1;
            });
            contacts.append(cont);
        }
        else if(filterType == 'Price (Highest)'){
            var contacts = $('#appendAvailability'),
             cont = contacts.children('a');
             cont.detach().sort(function(a, b) {
                var astts = $(a).data('price');
                var bstts = $(b).data('price')
                return (astts < bstts) ? (astts < bstts) ? 1 : 0 : -1;
            });
            contacts.append(cont);
        }
        else if(filterType == 'Duration (Shortest)'){
            var contacts = $('#appendAvailability'),
             cont = contacts.children('a');
             cont.detach().sort(function(a, b) {
                var astts = $(a).data('duration');
                var bstts = $(b).data('duration')
                return (astts > bstts) ? (astts > bstts) ? 1 : 0 : -1;
            });
            contacts.append(cont);
        }
        else if(filterType == 'Duration (Longest)'){
            var contacts = $('#appendAvailability'),
             cont = contacts.children('a');
             cont.detach().sort(function(a, b) {
                var astts = $(a).data('duration');
                var bstts = $(b).data('duration')
                return (astts < bstts) ? (astts < bstts) ? 1 : 0 : -1;
            });
            contacts.append(cont);
        }
    }
    function applyFilters() {
        
        // Get selected filter values
        var selectedApis = $('.api:checked').map(function () {
            return $(this).val();
        }).get();

        var selectedStops = $('.stops:checked').map(function () {
            return parseInt($(this).val());
        }).get();

        var selectedAirlines = $('.airlines:checked').map(function () {
            return $(this).val();
        }).get();

        var priceRange = slider.noUiSlider.get();

        var minPrice = parseInt(priceRange[0]);
        var maxPrice = parseInt(priceRange[1]);
        
        // Loop through flight cards
        $('.flight-card').each(function () {
            var api = $(this).data('api');
            var stops = $(this).data('stops');
            var airline = $(this).data('airline');
            var price = $(this).data('price');
            // Check if the flight card should be shown or hidden based on filters
            var show = true;
            if (selectedApis.length > 0 && !selectedApis.includes(api)) {
                show = false;
            }

            if (selectedStops.length > 0 && !selectedStops.includes(stops)) {
                show = false;
            }

            if (selectedAirlines.length > 0 && !selectedAirlines.includes(airline)) {
                show = false;
            }

            if (price < minPrice || price > maxPrice) {
                show = false;
            }

            // Toggle the visibility of the flight card
            if (show) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    
</script>
<!-- Price Range -->
<script>

    var slider = document.getElementById('price-range');
    var minPrices = document.getElementsByClassName('min-price');
    var maxPrices = document.getElementsByClassName('max-price');

    noUiSlider.create(slider, {
        start: [{{$minPrice}}, {{$maxPrice}}],
        connect: true,
        range: {
            'min': {{$minPrice}},
            'max': {{$maxPrice}}
        }
    });

  
    slider.noUiSlider.on('update', function (values, handle) {
        applyFilters();
        for (var i = 0; i < minPrices.length; i++) {
            if (handle === 0) {
                minPrices[i].textContent = 'PKR ' + formatCurrency(Math.floor(values[handle]));
            } else {
                maxPrices[i].textContent = 'PKR ' + formatCurrency(Math.floor(values[handle]));
            }
        }
    });
    function formatCurrency(amount) {
        return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
</script>
