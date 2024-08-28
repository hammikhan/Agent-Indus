@if($results['status'] == '200')
    @php
        $airlineArray = array();
        foreach($results['msg'] as $flight){
            $total_price = $flight['Flights'][0]['Fares'][0]['BillablePrice'];
            $Currency = $flight['Flights'][0]['Fares'][0]['Currency'];
            $airline = $flight['MarketingAirline']['Airline'];

            if (!array_key_exists($airline, $airlineArray) || $total_price < $airlineArray[$airline]['lowest_price']) {
                $airlineArray[$airline] = [
                    'lowest_price' => $total_price,
                    'Currency' => $Currency,
                ];
            }
        }
    @endphp
    <div class="swiper-container">
        <div class="swiper-button-next flight-swiper-btn"></div>
        <div class="swiper-button-prev flight-swiper-btn"></div>
        <div class="swiper flight-swiper">
            <div class="swiper-wrapper">
                @php
                    $i = 0;
                @endphp
                @foreach ($airlineArray as $airline => $lowestPrice)
                    <div class="swiper-slide lowest-price" data-airline="{{ $airline }}">
                        <a href="#" class="date-item text-center text-reset {{ ($i == 0) ? "active" : "" }}">
                            <p class="m-0 fw-medium">
                                @if (strlen(AirlineNameByAirlineCode($airline)) > 15)
                                    {{ substr(AirlineNameByAirlineCode($airline), 0, 10) . '...' }}
                                @else
                                    {{ AirlineNameByAirlineCode($airline) }}
                                @endif
                            </p>
                            <div class="airline-icon">
                                <img class="rounded-1" src="{{ asset('assets/airlines/'.$airline.'.png') }}"
                                    alt="{{ $airline }}" style="max-height: 30px;">
                            </div>
                            <p class="m-0 text-muted">{{ $lowestPrice['Currency'] }} {{ number_format($lowestPrice['lowest_price']) }}</p>
                        </a>
                    </div>
                    @php
                        $i++;
                    @endphp
                @endforeach
                
            </div>
        </div>
    </div>
<!-- Dates Swiper -->
<script>
    $(function () {
        var swiper = new Swiper(".flight-swiper", {
            autoHeight: true,
            // Responsive breakpoints
            breakpoints: {
                // when window width is >= 320px
                320: {
                    slidesPerView: 3,
                },
                // when window width is >= 480px
                480: {
                    slidesPerView: 5,
                },
                // when window width is >= 640px
                1000: {
                    slidesPerView: 6,
                }
            },
            navigation: {
                nextEl: ".swiper-button-next.flight-swiper-btn",
                prevEl: ".swiper-button-prev.flight-swiper-btn",
            },
        });
    })
    
    $(document).ready(function() {
        $('.lowest-price').click(function(event) {
            event.preventDefault();
            selectedAirline = $(this).data('airline');
            // console.log(airline);
            $('.swiper-slide.lowest-price').removeClass('swiper-slide-active');
            $('.swiper-slide.lowest-price').removeClass('swiper-slide-next');
            $('.date-item').removeClass('active');

            $(this).addClass('swiper-slide-active');
            
            var swiperWrapper = $(this).closest('.swiper-wrapper');
            var index = swiperWrapper.find('.lowest-price').index(this);
            swiperWrapper.find('.date-item').eq(index).addClass('active');
            applyAirlineFilters(selectedAirline);
        });
    });

    function applyAirlineFilters(selectedAirline) {
        $('.flight-card').each(function () {
            var airline = $(this).data('airline');

            var show = true;
            if (selectedAirline.length > 0 && selectedAirline !=airline) {
                show = false;
            }

            if (show) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    };


</script>
@endif
