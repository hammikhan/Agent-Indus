<section class="flight-search-form">
    <div class="card card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="flight-type">
                    <input type="radio" class="btn-check" name="flight-type" value="oneway" id="oneway"
                        autocomplete="off" checked>
                    <label class="btn" for="oneway">One Way</label>

                    <input type="radio" class="btn-check" name="flight-type" value="return" id="roundtrip"
                        autocomplete="off">
                    <label class="btn" for="roundtrip">Round Trip</label>

                    <input type="radio" class="btn-check" name="flight-type" value="multi" id="multicity"
                        autocomplete="off">
                    <label class="btn" for="multicity">Multi City</label>
                </div>
            </div>
            <div class="col-12 mt-3">
                <div class="row g-2 gy-3">
                    <div class="col-12 d-lg-none flight-number">
                        <h6>Flight 1</h6>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6 order-lg-1">
                        <label for="from"
                            class="input-label ms-3 bg-transparent border-0 position-absolute top-0 translate-middle-y">
                            <span class="bg-white px-1">From</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent px-2" id="from">
                                <i class="fa fa-plane-departure"></i>
                            </span>

                            <div class="dropdown">
                                <input type="text" class="form-control" placeholder="City or Airport" aria-label="City or Airport" aria-describedby="from"
                                    data-bs-toggle="dropdown" aria-expanded="false" name="origin" autocomplete="off" id="origin">
                                <input type="hidden" name="origin_hidden" id="origin_hidden">

                                <div class="dropdown-menu airports-dropdown" id="origin_menu">
                                    
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6 order-lg-2">
                        <label for="to"
                            class="input-label ms-3 bg-transparent border-0 position-absolute top-0 translate-middle-y">
                            <span class="bg-white px-1">To</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent px-2" id="to">
                                <i class="fa fa-plane-arrival"></i>
                            </span>
                            <div class="dropdown">

                                <input type="text" class="form-control" placeholder="City or Airport" aria-label="City or Airport" aria-describedby="to"
                                    data-bs-toggle="dropdown" aria-expanded="false" name="destination" autocomplete="off" id="destination">
                                <input type="hidden" name="destination_hidden" id="destination_hidden">

                                <div class="dropdown-menu airports-dropdown" id="destination_menu">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-8 order-lg-3">
                        <div class="row g-2 gy-3">
                            <div class="col">
                                <label for="departure"
                                    class="input-label ms-3 bg-transparent border-0 position-absolute top-0 translate-middle-y">
                                    <span class="bg-white px-1">Departure</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent px-2" id="departure">
                                        <i class="fa-regular fa-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control departure" placeholder="Select Date" id="departure_date" autocomplete="off">
                                </div>
                            </div>
                            <div class="col arrival">
                                <label for="arrival"
                                    class="input-label ms-3 bg-transparent border-0 position-absolute top-0 translate-middle-y">
                                    <span class="bg-white px-1">Arrival</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent px-2" id="arrival">
                                        <i class="fa-regular fa-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control return"  placeholder="Select Date" id="return_date" autocomplete="off">
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-lg-2 col-md-4 order-lg-4">
                        <label for="passenger" class=" input-label ms-3 bg-transparent border-0 position-absolute top-0
                            translate-middle-y">
                            <span class="bg-white px-1">Passenger &amp; Class</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent px-2" id="passenger">
                                <i class=" fa-solid fa-user"></i>
                            </span>
                            <div class="dropdown">
                                <input type="text" class="form-control passenger-count" value="Travelers 1, Economy" placeholder="No. of Pax, Class">
                                <div class="dropdown-menu dropdown-menu-end passengers-dropdown border-0 p-3"
                                    data-popper-placement="bottom-end">
                                    <div class="row align-items-center my-4">
                                        <div class="col-8 d-flex align-items-center">
                                            <div class="icon me-2">
                                                <img src="{{ asset('assets/icons/icon-adult.png') }}" alt="adult">
                                            </div>
                                            <div class="title">
                                                <p class="text-dark fw-semibold mb-0">Adult</p>
                                            </div>
                                        </div>
                                        <div class="col-4 d-flex justify-content-end">
                                            <button class="increase_decrease btn btn-primary d-flex justify-content-center align-items-center" data-field="adult_count" data-type="minus" value="Decrease Value">
                                                <i class="fa-solid fa-minus text-gray-500"></i>
                                            </button>
                                            <input class="adult_count border-0 bg-transparent text-dark fw-semibold text-center p-0" min="1" max="9" value="1" type="number" id="adult_count" disabled="">
                                            <button class="increase_decrease btn btn-primary d-flex justify-content-center align-items-center" data-field="adult_count" data-type="plus" value="Increase Value">
                                                <i class="fa-solid fa-plus text-gray-500"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row align-items-center my-4">
                                        <div class="col-8 d-flex align-items-center">
                                            <div class="icon me-2">
                                                <img src="{{ asset('assets/icons/icon-child.png') }}" alt="child">
                                            </div>
                                            <div class="title">
                                                <p class="text-dark fw-semibold mb-0">Children</p>
                                                <p class="small mb-0">(Aged 2 - 12 yrs)</p>
                                            </div>
                                        </div>
                                        <div class="col-4 d-flex justify-content-end">
                                            <button class="increase_decrease btn btn-primary d-flex justify-content-center align-items-center" data-field="child_count" data-type="minus" value="Decrease Value"><i class="fa-solid fa-minus text-gray-500"></i>
                                            </button>
                                            <input class="child_count border-0 bg-transparent text-dark fw-semibold text-center p-0" min="0" max="8" value="0" type="number" id="child_count" disabled="">
                                            <button class="increase_decrease btn btn-primary d-flex justify-content-center align-items-center" data-field="child_count" data-type="plus" value="Increase Value"><i class="fa-solid fa-plus text-gray-500"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row align-items-center my-4">
                                        <div class="col-8 d-flex align-items-center">
                                            <div class="icon ms-2 me-2">
                                                <img src="{{ asset('assets/icons/icon-infant.png') }}" alt="adult">
                                            </div>
                                            <div class="title">
                                                <p class="text-dark fw-semibold mb-0">Infant</p>
                                                <p class="small mb-0">(Below 2 yrs)</p>
                                            </div>
                                        </div>
                                        <div class="col-4 d-flex justify-content-end">
                                            <button class="increase_decrease btn btn-primary d-flex justify-content-center align-items-center" data-field="infant_count" data-type="minus" value="Decrease Value">
                                                <i class="fa-solid fa-minus text-gray-500"></i>
                                            </button>
                                            <input class="infant_count border-0 bg-transparent text-dark fw-semibold text-center p-0" min="0" max="4" value="0" type="number" id="infant_count" disabled="">
                                            <button class="increase_decrease btn btn-primary d-flex justify-content-center align-items-center" data-field="infant_count" data-type="plus" value="Increase Value">
                                                <i class="fa-solid fa-plus text-gray-500"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row align-items-center my-4">
                                        <div class="col-12">
                                            <select class="form-control form-select" id="cabin_class">
                                                <option value="Economy" selected>Economy</option>
                                                <option value="Premium Economy">Premium Economy</option>
                                                <option value="Business">Business</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-12 flight-row order-lg-last">
                        <div class="row g-2 gy-3">
                            <div class="col-12 d-lg-none flight-number">
                                <h6>Flight 2</h6>
                            </div>
                            <div class="col-lg-3 col-md-6 col-6">
                                <label for="from"
                                    class="input-label ms-3 bg-transparent border-0 position-absolute top-0 translate-middle-y">
                                    <span class="bg-white px-1">From</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent px-2" id="from">
                                        <i class="fa fa-plane-departure"></i>
                                    </span>
        
                                    <div class="dropdown">
                                        <input type="text" class="form-control" placeholder="City or Airport" aria-label="City or Airport" aria-describedby="from"
                                            data-bs-toggle="dropdown" aria-expanded="false" name="origin2" autocomplete="off" id="origin2">
                                        <input type="hidden" name="origin_hidden2" id="origin_hidden2">
        
                                        <div class="dropdown-menu airports-dropdown" id="origin_menu2">
                                            
                                        </div>
                                    </div>
        
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 col-6">
                                <label for="to"
                                    class="input-label ms-3 bg-transparent border-0 position-absolute top-0 translate-middle-y">
                                    <span class="bg-white px-1">To</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent px-2" id="to">
                                        <i class="fa fa-plane-arrival"></i>
                                    </span>
                                    <div class="dropdown">
        
                                        <input type="text" class="form-control" placeholder="City or Airport" aria-label="City or Airport" aria-describedby="to"
                                            data-bs-toggle="dropdown" aria-expanded="false" name="destination2" autocomplete="off" id="destination2">
                                        <input type="hidden" name="destination_hidden2" id="destination_hidden2">
        
                                        <div class="dropdown-menu airports-dropdown" id="destination_menu2">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label for="departure"
                                    class="input-label ms-3 bg-transparent border-0 position-absolute top-0 translate-middle-y">
                                    <span class="bg-white px-1">Departure</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent px-2" id="departure2">
                                        <i class="fa-regular fa-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control departure2" placeholder="Select Date" id="departure_date2" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-1 remove-flight align-self-center">
                                <button type="button" class="btn-close" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-1 order-lg-5">
                        <button class="btn btn-search w-100" id="btnAvailability">
                            <i class="fa-solid fa-search"></i>
                            <span class="d-lg-none"> Search</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>