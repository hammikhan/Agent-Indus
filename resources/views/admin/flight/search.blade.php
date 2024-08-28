@extends('admin.layouts.app')

@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        {{-- <a href="#">Search Flight</a> --}}
        <span>Search Flight</span>
    </h4>
@endsection

@section('styles')
<!-- Fontawesome Icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<!-- datepicker css -->
<link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">

<link href="{{ asset('assets/css/flight-search.css') }}" rel="stylesheet" type="text/css" />
<style>
    
    .btn-check:focus+.btn, .btn:focus {
        outline: 0;
        -webkit-box-shadow: none;
        box-shadow: none;
    }
    .passengers-dropdown{
        inset: 0px 0px auto auto;
        transform: translate(1.01392px, 52px);
    }
    .flight-search-form .passengers-dropdown input{
        max-width: 50% !important;
    }
    /* ****************Filter Icon*************** */
    .rotate {
        animation: rotate-animation 0.5s linear;
    }

    @keyframes rotate-animation {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
    #resetButton:hover{
        border-radius: 50%;
        box-shadow: 0 2px 9px #b3b5ba;
    }
    #sorting-filter:hover{
        cursor: pointer;
    }
    /***** .swiper-slide.date-swiper-item{
        width: 15% !important;
    } ******/
    .flight-swiper-btn {
        background: transparent !important;
    }
    .flight-swiper-btn.swiper-button-prev{
        font-size: larger;
        left: -5px !important;
    }
    .flight-swiper-btn.swiper-button-next {
        font-size: larger;
        right: -5px !important;
    }
    .flight-swiper-btn::after {
        font-size: larger;
    }
    .dropdown-menu{
        min-width: 15rem !important;
    }
</style>
@endsection

@section('content')
<div class="main-content">

    <div class="page-content">

        <!-- Search Form -->
        @include('admin.flight.includes.search-engine')
        <!-- End Search Form -->

        <!-- Recent/Popular Searches -->
        <section class="recent-search d-none">
            <h5>Recent Searches</h5>

            <!-- Swiper -->
            <div class="swiper recent-search-swiper">
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-wrapper">
                    <!-- Slide -->
                    <div class="swiper-slide">
                        <div class="card card-body p-3">
                            <div class="hstack gap-2  mx-auto">
                                <p class="m-0">One Way</p>
                                <div class="vr"></div>
                                <a href="#" class="stretched-link fw-semibold">LHE - KHI</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Slide -->

                    <!-- Slide -->
                    <div class="swiper-slide">
                        <div class="card card-body p-3">
                            <div class="hstack gap-2  mx-auto">
                                <p class="m-0">One Way</p>
                                <div class="vr"></div>
                                <a href="#" class="stretched-link fw-semibold">LHE - KHI</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Slide -->

                    <!-- Slide -->
                    <div class="swiper-slide">
                        <div class="card card-body p-3">
                            <div class="hstack gap-2  mx-auto">
                                <p class="m-0">One Way</p>
                                <div class="vr"></div>
                                <a href="#" class="stretched-link fw-semibold">LHE - KHI</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Slide -->

                    <!-- Slide -->
                    <div class="swiper-slide">
                        <div class="card card-body p-3">
                            <div class="hstack gap-2  mx-auto">
                                <p class="m-0">One Way</p>
                                <div class="vr"></div>
                                <a href="#" class="stretched-link fw-semibold">LHE - KHI</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Slide -->

                    <!-- Slide -->
                    <div class="swiper-slide">
                        <div class="card card-body p-3">
                            <div class="hstack gap-2  mx-auto">
                                <p class="m-0">One Way</p>
                                <div class="vr"></div>
                                <a href="#" class="stretched-link fw-semibold">LHE - KHI</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Slide -->

                    <!-- Slide -->
                    <div class="swiper-slide">
                        <div class="card card-body p-3">
                            <div class="hstack gap-2  mx-auto">
                                <p class="m-0">One Way</p>
                                <div class="vr"></div>
                                <a href="#" class="stretched-link fw-semibold">LHE - KHI</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Slide -->

                    <!-- Slide -->
                    <div class="swiper-slide">
                        <div class="card card-body p-3">
                            <div class="hstack gap-2  mx-auto">
                                <p class="m-0">One Way</p>
                                <div class="vr"></div>
                                <a href="#" class="stretched-link fw-semibold">LHE - KHI</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Slide -->

                    <!-- Slide -->
                    <div class="swiper-slide">
                        <div class="card card-body p-3">
                            <div class="hstack gap-2  mx-auto">
                                <p class="m-0">One Way</p>
                                <div class="vr"></div>
                                <a href="#" class="stretched-link fw-semibold">LHE - KHI</a>
                            </div>
                        </div>
                    </div>
                    <!-- End Slide -->
                </div>
            </div>

        </section>
        <!-- End Recent/Popular Searches -->

        <!-- Search Loader -->
        <section class="search-loader d-none">
            <h5>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>

                Searching Flights
            </h5>
            <div class="progress progress-lg">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 50%;" aria-valuenow="50"
                    aria-valuemin="0" aria-valuemax="100">50%</div>
            </div>
        </section>
        <!-- End Search Loader -->

        <!-- Flights Results -->
        <section class="flight-results">
            <div class="row g-2 gy-3">
                <!-- Filters -->
                <div class="col-lg-2" id="aside_filter">
                    
                </div>

                <!-- Results -->
                <div class="col-lg-8">

                    <section class="card px-3 d-block mb-2" id="date_swiper">
                        <!-- Dates Swiper -->
                        {{-- @include('admin.flight.includes.date-swiper') --}}
                    </section>
                    <section class="card px-3 d-block" id="airlineSlider">
                        
                    </section>

                    <section class="fliter-trigger-mobile text-end mb-3">
                        <a class="fw-medium text-black d-block d-lg-none" data-bs-toggle="collapse"
                            href="#filters" role="button" aria-expanded="false" aria-controls="filters">
                            <i class="fa-solid fa-sliders"></i> Filter
                        </a>
                    </section>
                    <div id="appendAvailability">
                        
                    </div>
                </div>

                <div class="col-lg-2 d-none d-md-block">
                    @include('admin.flight.includes.recent-searches')
                </div>

            </div>
        </section>
        <!-- End Flights Results -->

        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->
</div>

<!-- Modal -->
<!-- Right bar overlay-->
<div class="rightbar-overlay"></div>

<!-- chat offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasActivity" aria-labelledby="offcanvasActivityLabel">
    <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasActivityLabel">Offcanvas right</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        ...
    </div>
</div>

<!-- Flight Detail Sidepanel -->
<div class="offcanvas offcanvas-end" tabindex="-1" data-bs-scroll="false" id="flightSidepanel"
    aria-labelledby="flightSidepanelLabel">
    
</div>
<!-- End Flight Detail Sidepanel -->

<!-- My MOdal -->
@endsection

@section('scripts')

<!-- datepicker js -->
<script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $('.passenger-count').click(function () {
            $('.passengers-dropdown').slideToggle();
        });

        $(document).click(function (e) {
            if (!$(e.target).closest('.passengers-dropdown').length && !$(e.target).closest('.passenger-count').length) {
                $('.passengers-dropdown').slideUp();
            }
        });

        function updateTotalPassengerCount() {
            var adultCount = parseInt($("#adult_count").val()) || 1;
            var childCount = parseInt($("#child_count").val()) || 0;
            var infantCount = parseInt($("#infant_count").val()) || 0;
            var cabin = $("#cabin_class").val();

            var totalPassengers = adultCount + childCount + infantCount;
            $(".passenger-count").val("Travelers "+totalPassengers+", "+cabin);

            if (infantCount >= adultCount) {
                $('.increase_decrease[data-field="infant_count"][data-type="plus"]').prop('disabled', true);
            } else {
                $('.increase_decrease[data-field="infant_count"][data-type="plus"]').prop('disabled', false);
            }
        }

        $(".increase_decrease").click(function () {
                var fieldName = $(this).attr('data-field');
                var type = $(this).attr('data-type');
                var inputElement = $("#" + fieldName);
                var currentValue = parseInt(inputElement.val());
                if (!isNaN(currentValue)) {
                    if (type === 'minus') {
                        if (currentValue > inputElement.attr('min')) {
                            inputElement.val(currentValue - 1);
                        }
                    } else if (type === 'plus') {
                        if (currentValue < inputElement.attr('max')) {
                            inputElement.val(currentValue + 1);
                        }
                    }
                }
            
            updateTotalPassengerCount();
        });
        $('#cabin_class').change(function() {
            updateTotalPassengerCount();
        });
    });
</script>

<script>
    $(document).ready(function() {
        const futureDate = new Date();
        futureDate.setDate(futureDate.getDate() + 5);
        const defaultDate = futureDate.toISOString().split('T')[0];

        const departureDatePicker = flatpickr("#departure_date", {
            defaultDate: defaultDate,
            minDate: "today",
            dateFormat: "d-m-Y",
            onClose: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const selectedDepartureDate = selectedDates[0];
                    const nextDay = new Date(selectedDepartureDate);
                    nextDay.setDate(nextDay.getDate() + 1);
                    const formattedNextDay = instance.formatDate(nextDay, "d-m-Y");
                    returnDatePicker.set("minDate", formattedNextDay);
                }
            }
        });

        const returnDatePicker = flatpickr("#return_date", {
            dateFormat: "d-m-Y"
        });
        const departureDatePicker2 = flatpickr("#departure_date2", {
            defaultDate: defaultDate,
            minDate: "today",
            dateFormat: "d-m-Y",
        });

        // Store instances for later use
        $('#departure_date').data('flatpickrInstance', departureDatePicker);
        $('#departure_date2').data('flatpickrInstance', departureDatePicker2);
        $('#return_date').data('flatpickrInstance', returnDatePicker);
    });

    $(document).on('click', '.search-item', function() {
        $('#origin').val($(this).data('originairport'));
        $('#origin_hidden').val($(this).data('originairport'));
        $('#destination').val($(this).data('destinationairport'));
        $('#destination_hidden').val($(this).data('destinationairport'));

        const departureDate = $(this).data('departdate');
        const departureDateSet = new Date(departureDate).toLocaleDateString('en-GB');
        const returnDateMin = new Date(departureDate).toISOString().split('T')[0];

        const returnDate = $(this).data('returndate');
        const returnDateSet = new Date(returnDate).toLocaleDateString('en-GB');

        const departureDatePicker = $('#departure_date').data('flatpickrInstance');
        const returnDatePicker = $('#return_date').data('flatpickrInstance');

        departureDatePicker.setDate(departureDateSet);
        returnDatePicker.setDate(returnDateSet);
        returnDatePicker.set("minDate", returnDateMin);

        var trip = $(this).data('trip');
        ShowHideWayTabs(trip)
    });
    function ShowHideWayTabs(trip){
        if(trip == 'return'){
            $('#roundtrip').prop('checked', true);
            $('.flight-row').hide();
            $('.flight-number').hide();
            $('.add-flight').hide();
            $('.arrival').show();
        }
        else if(trip == 'oneway'){
            $('#oneway').prop('checked', true);
            $('.flight-row').hide();
            $('.flight-number').hide();
            $('.add-flight').hide();
            $('.arrival').hide();
        }else{
            $('#multicity').prop('checked', true);
            $('.flight-row').show();
            $('.flight-number').show();
            $('.add-flight').show();
        }
    }

    ///////////////////////Search Flight Itinarary/////////////////////////////////////
    $('#btnAvailability').click(function(){
        Availability();
    });
    ////////////////////////////Validation/////////////////////////////////////////
    function validateForm(){
        var isValidated = true;

        var origin = $('#origin').val();
        if(origin == ''){
            $('#origin').addClass('error-main');
            isValidated = false;
        }else{
            $('#origin').removeClass('error-main');
        }
        var destination = $('#destination').val();
        if(destination == ''){
            $('#destination').addClass('error-main');
            isValidated = false;
        }else{
            $('#destination').removeClass('error-main');
        }

        if(origin == destination){
            Swal.fire({
                text: "Origin and Destination must be different",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "Okay, got it!",
                customClass: {
                confirmButton: "btn btn-primary"
                }
            });
            isValidated = false;
        }
        
        
        
        var departure_date = $('#departure_date').val();
        if(departure_date == ''){
            $('#departure_date').addClass('error-main');
            isValidated = false;
        }else{
            $('#departure_date').removeClass('error-main');
        }
        
        // var dapart_date = $('#dapart_date').val();
        // if(dapart_date == ''){
        //     $('.depart-label').addClass('error-label');
        //     $('.depart-main').addClass('error-main');
        //     $('.depart-icon').addClass('error-icon');
        //     isValidated = false;
        // }els{
        //     $('.depart-label').removeClass('error-label');
        //     $('.depart-main').removeClass('error-main');
        //     $('.depart-icon').removeClass('error-icon');
        //     isValidated = true; 
        // }

        return isValidated;
    }
    // ////////////////////////Itenerary Detail////////////////////
    $(document).ready(function() {
        // Attach click event handler to the link using event delegation
        $(document).on('click', '.flight-card', function(event) {
            event.preventDefault();
            $('#flightSidepanel').html('');
            ref_key = $(this).data('ref-key');
            fare_type = $(this).data('fare-type');
            $.ajax({
                type:'post',
                url:"{{route('admin.flight.detail')}}",
                data:{ref_key},
                success:function(data) {
                    var obj = JSON.parse(data);
                    
                    if(obj.message == 'success'){
                        $('#flightSidepanel').append(obj.flightDetailHtml);
                    }else{
                        Swal.fire({
                            text: obj.message,
                            icon: "warning",
                            buttonsStyling: false,
                            confirmButtonText: "Okay, got it!",
                            customClass: {
                            confirmButton: "btn btn-primary"
                            }
                        }) 
                    }
                },
                error:function(data){
                    var data = JSON.parse(data.responseText);
                    var message = '';
                    data.errors.permissionArray ? message = data.errors.permissionArray[0]  :  message = data.errors.roleName[0];
                    Swal.fire({
                        text: message,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Okay, got it!",
                        customClass: {
                        confirmButton: "btn btn-primary"
                        }
                    });
                }
            });
            if(fare_type == 0){
                /////////////////////////Fare Rules Request/////////////////////////
                $.ajax({
                    type:'post',
                    url:"{{route('admin.flight.fare.rule')}}",
                    data:{ref_key},
                    success:function(data) {
                        var obj = JSON.parse(data);
                        if(obj.message == 'success'){
                            $('#rule-onward').append(obj.ruleHtml1);
                            $('#rule-return').append(obj.ruleHtml2);
                        }else{
                            console.log('Fare Rule Error...');
                        }
                    }
                });
            }
        });
    });
    function Availability(){
        if(validateForm()){
            $('#appendAvailability').html('');
            $('#aside_filter').html('');
            $('#airlineSlider').html('');

            var originInput = $('#origin').val();
            var originCode = originInput.match(/\(([A-Z]{3})\)$/);
            var destinationInput = $('#destination').val();
            var destinationCode = destinationInput.match(/\(([A-Z]{3})\)$/);

            var originInput2 = $('#origin2').val();
            var originCode2 = originInput2.match(/\(([A-Z]{3})\)$/);
            var destinationInput2 = $('#destination2').val();
            var destinationCode2 = destinationInput2.match(/\(([A-Z]{3})\)$/);

            var tripType = $("input[name='flight-type']:checked").val();

            var origin2 = null;
            var destination2 = null;
            var departureDate2 = null;
            if(tripType == 'multi'){
                origin2 = originCode2[1];
                destination2 = destinationCode2[1];
                departureDate2 = $('#departure_date2').val();
            }

            var origin = originCode[1];
            var destination = destinationCode[1];
            var departureDate = $('#departure_date').val();
            var returnDate = $('#return_date').val();
            var adults = $('#adult_count').val();
            var children = $('#child_count').val();
            var infants = $('#infant_count').val();
            var cabin = $("#cabin_class").val().replace(/\s+/g, '');

            var blinking = <?php echo json_encode(flightCardBlinking(5)); ?>;
            var asideBlinking = <?php echo json_encode(flightFilterBlinking()); ?>;
            $('#appendAvailability').html(blinking);
            $('#aside_filter').html(asideBlinking);

            var apiArray = @json($apis);

            $.ajax({
                type:'get',
                url:"{{ route('admin.flight.search.empty.search') }}",
                success:function(data) {
                    var obj1 = JSON.parse(data);
                    if(obj1.status == 'success'){
                        ///////////////////////////////////Availibility///////////////////////////////////
                        $.each(apiArray, function(index, apiItem) {
                            var api = apiItem;
                            $.ajax({
                                type:'get',
                                url:"{{ route('admin.flight.search.availability') }}",
                                data:{api,tripType,origin,destination,departureDate,origin2,destination2,departureDate2,returnDate,adults,children,infants,cabin},
                                success:function(data) {
                                    var obj = JSON.parse(data);
                                    if(obj.message == 'success'){
                                        $('#appendAvailability').html('');
                                        $('#aside_filter').html('');
                                        $('#airlineSlider').html('');

                                        $('#appendAvailability').append(obj.html);
                                        $('#aside_filter').html(obj.filter);
                                        $('#airlineSlider').html(obj.airSlider);
                                        $('#date_swiper').html(obj.dateSwiper);
                                    }else{
                                        Swal.fire({
                                            text: obj.message,
                                            icon: "warning",
                                            buttonsStyling: false,
                                            confirmButtonText: "Okay, got it!",
                                            customClass: {
                                            confirmButton: "btn btn-primary"
                                            }
                                        }) 
                                    }
                                },
                                error:function(data){
                                    var data = JSON.parse(data.responseText);
                                    console.log(data);return false;
                                    var message = '';
                                    data.errors.permissionArray ? message = data.errors.permissionArray[0]  :  message = data.errors.roleName[0];
                                    Swal.fire({
                                        text: message,
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: "Okay, got it!",
                                        customClass: {
                                        confirmButton: "btn btn-primary"
                                        }
                                    });
                                }
                            });
                        });
                        /////////////////////////////////End Availibility/////////////////////////////////
                    }else{
                        console.log(obj1);
                    }
                },
                error:function(data){
                    var data = JSON.parse(data.responseText);
                    console.log(data);return false;
                    var message = '';
                    // Swal.fire({
                    //     text: message,
                    //     icon: "error",
                    //     buttonsStyling: false,
                    //     confirmButtonText: "Okay, got it!",
                    //     customClass: {
                    //     confirmButton: "btn btn-primary"
                    //     }
                    // });
                }
            });            
        }
    }
</script>

<!-- Recent Search Swiper -->
<script>
    $(function () {
        var swiper = new Swiper(".recent-search-swiper", {
            autoHeight: true,
            slidesPerView: 5,
            spaceBetween: 10,
            // Responsive breakpoints
            breakpoints: {
                // when window width is >= 320px
                320: {
                    slidesPerView: 2,
                    spaceBetween: 10
                },
                // when window width is >= 480px
                480: {
                    slidesPerView: 4,
                    spaceBetween: 10
                },
                // when window width is >= 640px
                1000: {
                    slidesPerView: 5,
                    spaceBetween: 10
                }
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });
    })

</script>
<!-- Flight Type -->
<script>
    $(document).ready(function () {
        $('input[name="flight-type"]').change(handleFlightVisibility);
        function handleFlightVisibility() {
            var roundtripChecked = $('#roundtrip').is(':checked');
            var onewayChecked = $('#oneway').is(':checked');
            var multicityChecked = $('#multicity').is(':checked');

            $('.flight-row, .flight-number, .add-flight').hide();
            if (roundtripChecked || multicityChecked) {
                $('.arrival').show();
            } else {
                $('.arrival').hide();
            }
            if (multicityChecked) {
                $('.arrival').hide();
                $('.flight-row, .flight-number, .add-flight').show();
            }
        }
        handleFlightVisibility();
    });
</script>
<script>
    $(document).ready(function () {
        function handleInput2(inputName, menuId) {
            $(`input[name="${inputName}"]`).on('focus', function () {
                $('airports-dropdown').show();
                var inputValue = $(this).val().trim();
                var airportCode = inputValue.match(/\(([A-Z]{3})\)$/);
                if (airportCode) {
                    $(this).val(airportCode[1].toLowerCase());
                }
            }).on('focusout', function () {
                var inputValue = $(`input[name="${inputName}_hidden"]`).val().trim()
                if (inputValue === '') {
                } else {
                    $(this).val(inputValue);
                }
            });

            $(document).on('click', `#${menuId} .list-item`, function () {
                var selectedText = $(this).find('.detail h6').text().trim();
                var selectedCode = $(this).find('.airport-code').text().trim();
                $(`input[name="${inputName}"]`).val(selectedText + ' (' + selectedCode + ')');
                $(`input[name="${inputName}_hidden"]`).val(selectedText + ' (' + selectedCode + ')');
                $(this).closest('.dropdown').find('.airports-dropdown').removeClass('show');
            });
        }

        handleInput2("origin", "origin_menu");
        handleInput2("destination", "destination_menu");
        handleInput2("origin2", "origin_menu2");
        handleInput2("destination2", "destination_menu2");
    });
    // /////////////////////////////Typing origin///////////////////////////////////

    $(document).ready(function () {
        var airportCache = {};
        var airportOriginList = [
            {
                "code": "ISB",
                "airport_name": "Islamabad International Airport",
                "city_name": "Islamabad"
            },
            {
                "code": "PEW",
                "airport_name": "Peshawar Airport",
                "city_name": "Peshawar"
            },
            {
                "code": "LHE",
                "airport_name": "Lahore Airport",
                "city_name": "Lahore"
            },
            {
                "code": "KHI",
                "airport_name": "Quaid E Azam Karachi Airport",
                "city_name": "Karachi"
            },
        ];
        var airportDestinationList = [
            {
                "code": "DXB",
                "airport_name": "Dubai International Airport",
                "city_name": "Dubai"
            },
            {
                "code": "AUH",
                "airport_name": "Abu Dhabi International Airport",
                "city_name": "Abu Dhabi"
            },
            {
                "code": "JED",
                "airport_name": "King Abdul Aziz International Airport",
                "city_name": "Jeddah"
            },
            {
                "code": "RUH",
                "airport_name": "King Khalid International Airport",
                "city_name": "Riyadh"
            }
        ];

        function handleInput(inputName, menuId) {
            var debounceTimer;

            $(`input[name="${inputName}"]`).on('input', function () {
                var inputValue = $(this).val().trim();
                // console.log(inputValue);
                $(this).closest('.dropdown').find('.airports-dropdown').addClass('show');
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    if (inputValue === '') {
                        if(menuId == 'origin_menu' || menuId == 'origin_menu2'){
                            initializeDefaultAirport(airportOriginList,menuId);
                        }
                        if(menuId == 'destination_menu' || menuId == 'destination_menu2'){
                            initializeDefaultAirport(airportDestinationList,menuId);
                        }
                    }else{
                        if (airportCache[inputValue]) {
                            populateDropdown(menuId, airportCache[inputValue], inputValue);
                        } else {
                            fetchAirports(inputValue, function (response) {
                                airportCache[inputValue] = response;
                                populateDropdown(menuId, response, inputValue);
                            });
                        }
                    }
                }, 300);
            });
        }

        
        initializeDefaultAirport(airportOriginList,"origin_menu");
        initializeDefaultAirport(airportOriginList,"origin_menu2");
        initializeDefaultAirport(airportDestinationList,"destination_menu");
        initializeDefaultAirport(airportDestinationList,"destination_menu2");
        function initializeDefaultAirport(airportList,menuId) {
            var dropdownMenu = $(`#${menuId}`);
            dropdownMenu.empty();
            
            airportList.forEach(function (airport) {
                appendAirportItem(dropdownMenu, airport);
            });
        }
        function fetchAirports(inputValue, callback) {
            $.ajax({
                url: '/getAllAirPortCodes/' + inputValue,
                method: 'GET',
                success: function (response) {
                    callback(response);
                },
                error: function (xhr, status, error) {
                    console.error(xhr, status, error);
                    callback([]);
                }
            });
        }

        function populateDropdown(menuId, airports, inputValue) {
            var dropdownMenu = $(`#${menuId}`);
            dropdownMenu.empty();

            var typedInputResults = airports.filter(function (airport) {
                return airport.code.toLowerCase().startsWith(inputValue.toLowerCase()) ||
                    airport.airport_name.toLowerCase().startsWith(inputValue.toLowerCase()) ||
                    airport.city_name.toLowerCase().startsWith(inputValue.toLowerCase());
            });
            // console.log(typedInputResults);
            var defaultResults = airports.filter(function (airport) {
                return !typedInputResults.includes(airport);
            });

            typedInputResults.forEach(function (airport) {
                appendAirportItem(dropdownMenu, airport);
            });

            if (typedInputResults.length === 0) {
                defaultResults.forEach(function (airport) {
                    appendAirportItem(dropdownMenu, airport);
                });
            }
        }

        function appendAirportItem(dropdownMenu, airport) {
            dropdownMenu.append(`<div class="list-item my-1 position-relative d-flex justify-content-between">
                                    <div class="item rounded-1 p-2 d-flex align-items-center w-100">
                                        <div class="detail text-truncate" title="${airport.city_name}">
                                            <h6 class="fw-semibold text-truncate text-dark mb-0">${airport.city_name}</h6>
                                            <p class="small text-gray-300 mb-0">${airport.airport_name}</p>
                                        </div>
                                        <span class="ms-2 small airport-code ms-auto align-self-start">${airport.code}</span>
                                    </div>
                                </div>`);
        }

        handleInput("origin", "origin_menu");
        handleInput("origin2", "origin_menu2");
        handleInput("destination", "destination_menu");
        handleInput("destination2", "destination_menu2");
    });
    ///////////////////////////////Jump To Next////////////////////////////////////
    $(document).ready(function() {
        $(document).on('click', '.list-item', function() {
            var parentInput = $(this).closest('.dropdown').find('input');

            if (parentInput.attr('id') === 'origin') {
                $('#destination').focus();
                $('#destination_menu').addClass('show');
            }
            else if (parentInput.attr('id') === 'destination') {
                $('#departure_date').focus();
            }
        });
        $('#departure_date').on('change', function() {
            $('#return_date').focus();
        });

        $(document).on('keydown', '.list-item', function(e) {
            if (e.key === 'Enter') {
                $(this).trigger('click');
            }
        });

    });
    
    
    
</script>
@endsection







