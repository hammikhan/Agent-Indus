<section class="card">
    <div class="card-header">
        <h5>Recent Search</h5>
    </div>
    <div class="card-body p-3">
        @foreach ($RecentSearch as $key => $item)
            @if($key != 0)
            <hr>
            @endif
            <div class="search-item row g-3 gy-4" data-trip="{{ $item->data['tripType'] }}"  data-originairport="{{ CityNameByAirportCode($item->data['origin']) }} ({{ $item->data['origin'] }})" data-origin="{{ $item->data['origin'] }}" data-departdate="{{ $item->data['departureDate'] }}" data-destinationairport="{{ CityNameByAirportCode($item->data['destination']) }} ({{ $item->data['destination'] }})" data-destination="{{ $item->data['destination'] }}" data-returndate="{{ $item->data['returnDate'] }}">
                <div class="col-12">
                    <div class="hstack gap-1 mx-auto text-center">
                        <p class="m-0">{{ $item->data['tripType'] }}</p>
                        <div class="vr"></div>
                        <a href="#" class="stretched-link fw-semibold">{{ $item->data['origin'] }} - {{ $item->data['destination'] }}</a>
                    </div>
                </div>
            </div>

        @endforeach
        <hr>
        
    </div>




</section>