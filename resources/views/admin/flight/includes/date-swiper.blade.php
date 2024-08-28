<div class="swiper-container">
    <div class="swiper-button-next dates-swiper-btn"></div>
    <div class="swiper-button-prev dates-swiper-btn"></div>
    @php
        $availabilityDate = isset($departDateSwiper) ? \Carbon\Carbon::createFromFormat('d-m-Y', $departDateSwiper) : \Carbon\Carbon::now();
        $daysBefore = 3;
        $daysAfter = 3;
    @endphp

    <div class="swiper dates-swiper">
        <div class="swiper-wrapper">
            @for ($i = -$daysBefore; $i <= $daysAfter; $i++)
                @php
                    $date = $availabilityDate->copy()->addDays($i);
                    $formattedDate = $date->format('D, M d');
                    $formattedDataDate = $date->format('Y-m-d');
                    $isActive = $i === 0 ? 'active' : '';
                @endphp

                <!-- Slide -->
                <div class="swiper-slide date-swiper-item" data-filter-date="{{ $formattedDataDate }}" style="width: 20%;">
                    <a href="#" class="date-item text-center text-reset {{ $isActive }}">
                        <p class="m-0 fw-medium">{{ $formattedDate }}</p>
                    </a>
                </div>
                <!-- End Slide -->
            @endfor
        </div>
    </div>

</div>
<script>
    $(function () {
        var swiper = new Swiper(".dates-swiper", {
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
                nextEl: ".swiper-button-next.dates-swiper-btn",
                prevEl: ".swiper-button-prev.dates-swiper-btn",
            },
        });
    })
    $('.swiper-slide').on('click', function(e) {
            e.preventDefault();
            $('.swiper-slide .date-item').removeClass('active');
            $(this).find('.date-item').addClass('active');
            
            // Optionally, you can store the selected date in a hidden input
            // var selectedDate = $(this).data('filter-date');
            // $('#selected_date').val(selectedDate);

            const filteDate = $(this).data('filter-date');
            const departureDateSet = new Date(filteDate).toLocaleDateString('en-GB');
            const departureDatePicker = $('#departure_date').data('flatpickrInstance');
            departureDatePicker.setDate(departureDateSet);

            Availability();
        });
</script>