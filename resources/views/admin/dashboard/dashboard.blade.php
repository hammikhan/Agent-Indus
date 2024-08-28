@extends('admin.layouts.app')

@section('breadecrum')
    <h4 class="page-title">
        <i class="fas fa-home"></i> Dashboard
        {{-- <a href="#">
            <i class="fas fa-home"></i>
        </a> 
        <a href="#">sdafsdaf</a> --}}
    </h4>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-xxl-12">
                        {{-- -----------------Status, Price and Count Cards----------- --}}
                        <div class="row">
                            @foreach ($data as $key => $item)
                                @php
                                    $textStatus = match($key) {
                                        'Not Ticketed' => 'primary',
                                        'Ticketed' => 'success',
                                        'Voided' => 'warning',
                                        default => 'danger',
                                    };
                                @endphp
                                <div class="col-xl-3 col-lg-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar">
                                                        <div class="avatar-title rounded bg-primary bg-gradient">
                                                            <i data-eva="shopping-bag" class="fill-white"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <p class="text-{{ $textStatus }} mb-1"><b>{{ $key }}</b></p>
                                                    <h6 class="mb-0">PKR {{ number_format($item['revenue']) }}</h6>
                                                </div>

                                                <div class="flex-shrink-0 align-self-end ms-2">
                                                    <div class="badge rounded-pill font-size-18 badge-soft-{{ $textStatus }}">{{ $item['count'] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <!-- end card -->
                                </div>
                            @endforeach              
                        </div>
                        <div class="row">
                            {{-- -------------------Recent Bookings-------------- --}}
                            <div class="col-xl-12 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-3">Recent Bookings</h5>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="dropdown">
                                                    <a class="dropdown-toggle text-reset" href="#" data-bs-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false">
                                                        <span class="fw-semibold">Show By:</span>
                                                        <span class="text-muted">
                                                            <span class="select_status">All</span>
                                                            <i class="mdi mdi-chevron-down ms-1"></i>
                                                        </span>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item" href="#" onclick="showBooking('Ticketed')">Ticketed</a>
                                                        <a class="dropdown-item" href="#" onclick="showBooking('Not Ticketed')">Not Ticketed</a>
                                                        <a class="dropdown-item" href="#" onclick="showBooking('Voided')">Voided</a>
                                                        <a class="dropdown-item" href="#" onclick="showBooking('Cancelled')">Cancelled</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
        
                                        <div class="table-responsive">
                                            <table class="table align-middle table-nowrap mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="align-middle">Pnr No</th>
                                                        <th class="align-middle">From/To</th>
                                                        <th class="align-middle">Fly Date & Time</th>
                                                        <th class="align-middle">Created By</th>
                                                        <th class="align-middle">Booking Date</th>
                                                        <th class="align-middle">Issued Date</th>
                                                        <th class="align-middle">Provider</th>
                                                        <th class="align-middle">Ticket Status</th>
                                                        <th class="align-middle">Segment Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="recent-booking">
                                                    @include('admin.dashboard.includes.recent-booking')
        
                                                </tbody>
                                            </table>
                                            <!-- end table -->
                                        </div>
                                        <!-- end table responsive -->
                                    </div>
                                    <!-- end card body -->
                                </div>
                                <!-- end card -->
                            </div>
                            {{-- ----------------------Pie Chart------------------ --}}
                            <div class="col-xl-6 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-3">Order Status</h5>
                                            </div>
                                            
                                        </div>

                                        <div id="chart-donut" data-colors='["#3b76e1", "#63ad6f", "#f9c256", "#f56e6e"]' class="mt-2"></div>

                                        <div class="text-center mt-4 border-top">
                                            <div class="row">
                                                @foreach ($data as $key => $item)
                                                    @php
                                                        $textStatus = match($key) {
                                                            'Not Ticketed' => 'primary',
                                                            'Ticketed' => 'success',
                                                            'Voided' => 'warning',
                                                            default => 'danger',
                                                        };
                                                    @endphp
                                                    <div class="col-3">
                                                        <div class="pt-3">
                                                            <p class="text-{{ $textStatus }} text-truncate mb-2"><strong>{{ $key }}</strong></p>
                                                            <h5 class="font-size-16 mb-0">{{ $item['count'] }}</h5>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- ---------------------Top Routes----------------- --}}
                            <div class="col-xl-6 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-3">Top Routes</h5>
                                            </div>
                                            
                                        </div>
        
                                        <div class="mx-n4" data-simplebar style="max-height: 296px;">
                                            <ul class="list-unstyled mb-0 row">
                                                @foreach ($top_5_formatted as $recentKey => $recent)
                                                    <li class="px-4 py-2 col-3">
                                                        <div class="d-flex align-items-center bg-light rounded-3">
                                                            <div class="flex-shrink-0 me-3">
                                                                <div class="avatar-sm">
                                                                    <div
                                                                        class="avatar-title bg-primary bg-gradient rounded p-3">
                                                                        {{ $recent }}%
                                                                        {{-- #1 --}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 overflow-hidden">
                                                                <p class="text-muted mb-1 text-truncate">{{ $recentKey }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                                
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>
                            
                            
                        </div>
                    </div>
                    
                </div>
                <!-- end row -->

                {{-- <div class="row">
                    <div class="col-xl-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-3">User Activity</h5>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="dropdown">
                                            <a class="dropdown-toggle text-muted" href="#"
                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                                Weekly<i class="mdi mdi-chevron-down ms-1"></i></span>
                                            </a>

                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="#">Yearly</a>
                                                <a class="dropdown-item" href="#">Monthly</a>
                                                <a class="dropdown-item" href="#">Weekly</a>
                                                <a class="dropdown-item" href="#">Today</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-muted mb-1">This Month</p>
                                    <h4>16,543</h4>
                                </div>

                                <div class="m-n3">
                                    <div id="chart-area" data-colors='["#3b76e1", "#f56e6e"]' class="apex-charts"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-3">Ratings & Reviews</h5>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="dropdown">
                                            <a class="dropdown-toggle text-muted" href="#"
                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                                <i data-eva="more-horizontal-outline" class="fill-muted"
                                                    data-eva-height="18" data-eva-width="18"></i>
                                            </a>

                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="#">Yearly</a>
                                                <a class="dropdown-item" href="#">Monthly</a>
                                                <a class="dropdown-item" href="#">Weekly</a>
                                                <a class="dropdown-item" href="#">Today</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row gy-4 gx-0">
                                    <div class="col-lg-6">
                                        <div>
                                            <div class="text-center">
                                                <h1>4.3</h1>
                                                <div class="font-size-16 mb-1">
                                                    <i class="mdi mdi-star text-warning"></i>
                                                    <i class="mdi mdi-star text-warning"></i>
                                                    <i class="mdi mdi-star text-warning"></i>
                                                    <i class="mdi mdi-star text-warning"></i>
                                                    <i class="mdi mdi-star-half-full text-warning"></i>
                                                </div>
                                                <div class="text-muted">(14,254 Based)</div>
                                            </div>

                                            <div class="mt-4">
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <div class="p-1 py-2">
                                                            <h5 class="font-size-16 mb-0">5 <i
                                                                    class="mdi mdi-star"></i></h5>
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <div class="p-1 py-2">
                                                            <div
                                                                class="progress animated-progess custom-progress">
                                                                <div class="progress-bar bg-gradient bg-primary"
                                                                    role="progressbar" style="width: 90%"
                                                                    aria-valuenow="90" aria-valuemin="0"
                                                                    aria-valuemax="90">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="p-1 py-2">
                                                            <h5 class="font-size-16 mb-0">50%</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end row -->

                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <div class="p-1 py-2">
                                                            <h5 class="font-size-16 mb-0">4 <i
                                                                    class="mdi mdi-star"></i></h5>
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <div class="p-1 py-2">
                                                            <div
                                                                class="progress animated-progess custom-progress">
                                                                <div class="progress-bar bg-gradient bg-primary"
                                                                    role="progressbar" style="width: 75%"
                                                                    aria-valuenow="75" aria-valuemin="0"
                                                                    aria-valuemax="75">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="p-1 py-2">
                                                            <h5 class="font-size-16 mb-0">20%</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end row -->

                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <div class="p-1 py-2">
                                                            <h5 class="font-size-16 mb-0">3 <i
                                                                    class="mdi mdi-star"></i></h5>
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <div class="p-1 py-2">
                                                            <div
                                                                class="progress animated-progess custom-progress">
                                                                <div class="progress-bar bg-gradient bg-primary"
                                                                    role="progressbar" style="width: 60%"
                                                                    aria-valuenow="60" aria-valuemin="0"
                                                                    aria-valuemax="60">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="p-1 py-2">
                                                            <h5 class="font-size-16 mb-0">15%</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end row -->

                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <div class="p-1 py-2">
                                                            <h5 class="font-size-16 mb-0">2 <i
                                                                    class="mdi mdi-star"></i></h5>
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <div class="p-1 py-2">
                                                            <div
                                                                class="progress animated-progess custom-progress">
                                                                <div class="progress-bar bg-gradient bg-warning"
                                                                    role="progressbar" style="width: 50%"
                                                                    aria-valuenow="50" aria-valuemin="0"
                                                                    aria-valuemax="50">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-auto">
                                                        <div class="p-1 py-2">
                                                            <h5 class="font-size-16 mb-0">10%</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end row -->

                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <div class="p-1 py-2">
                                                            <h5 class="font-size-16 mb-0">1 <i
                                                                    class="mdi mdi-star"></i></h5>
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <div class="p-1 py-2">
                                                            <div
                                                                class="progress animated-progess custom-progress">
                                                                <div class="progress-bar bg-gradient bg-danger"
                                                                    role="progressbar" style="width: 20%"
                                                                    aria-valuenow="20" aria-valuemin="0"
                                                                    aria-valuemax="20">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="p-1 py-2">
                                                            <h5 class="font-size-16 mb-0">5%</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end row -->
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end col -->

                                    <div class="col-lg-6">
                                        <div class="ps-lg-4">
                                            <div class="d-flex flex-wrap align-items-start gap-3">
                                                <h5 class="font-size-15">Reviews: </h5>
                                                <p class="text-muted">(14,254 Based)</p>
                                            </div>

                                            <div class=" me-lg-n3 pe-lg-3" data-simplebar style="max-height: 266px;">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item">
                                                        <div>
                                                            <div class="d-flex align-items-start">
                                                                <div class="flex-grow-1">
                                                                    <div class="badge bg-success bg-gradient mb-2"><i class="mdi mdi-star"></i> 4.1</div>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    <p class="text-muted font-size-13">12 Jul, 21</p>
                                                                </div>
                                                            </div>
                                                            
                                                            <p class="text-muted mb-4">It will be as simple as in fact, It will seem like simplified</p>
                                                            <div class="d-flex align-items-start">
                                                                <div class="flex-grow-1">
                                                                    <h5 class="font-size-14 mb-0">Samuel</h5>
                                                                </div>
            
                                                                <div class="flex-shrink-0">
                                                                    <div class="hstack gap-3">
                                                                        <div data-bs-toggle="tooltip" data-bs-placement="top" title="Like">
                                                                            <a href="#" class="text-muted"><i class="mdi mdi-thumb-up-outline"></i></a>
                                                                        </div>
                                                                        <div class="vr"></div>
                                                                        <div data-bs-toggle="tooltip" data-bs-placement="top" title="Comment">
                                                                            <a href="#" class="text-muted"><i class="mdi mdi-comment-text-outline"></i></a>
                                                                        </div>
                                                                        <div class="vr"></div>
                                                                        <div class="dropdown">
                                                                            <a class="text-muted dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                <i class="mdi mdi-dots-horizontal"></i>
                                                                            </a>
                                                                        
                                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                            <a class="dropdown-item" href="#">Action</a>
                                                                            <a class="dropdown-item" href="#">Another action</a>
                                                                            <a class="dropdown-item" href="#">Something else here</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <div>
                                                            <div class="d-flex align-items-start">
                                                                <div class="flex-grow-1">
                                                                    <div class="badge bg-success bg-gradient mb-2"><i class="mdi mdi-star"></i> 4.0</div>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    <p class="text-muted font-size-13">06 Jul, 21</p>
                                                                </div>
                                                            </div>
                                                            <p class="text-muted mb-4">Sed ut perspiciatis iste error sit</p>
                                                            <div class="d-flex align-items-start">
                                                                <div class="flex-grow-1">
                                                                    <h5 class="font-size-14 mb-0">Joseph</h5>
                                                                </div>
            
                                                                <div class="flex-shrink-0">
                                                                    <div class="hstack gap-3">
                                                                        <div data-bs-toggle="tooltip" data-bs-placement="top" title="Like">
                                                                            <a href="#" class="text-muted"><i class="mdi mdi-thumb-up-outline"></i></a>
                                                                        </div>
                                                                        <div class="vr"></div>
                                                                        <div data-bs-toggle="tooltip" data-bs-placement="top" title="Comment">
                                                                            <a href="#" class="text-muted"><i class="mdi mdi-comment-text-outline"></i></a>
                                                                        </div>
                                                                        <div class="vr"></div>
                                                                        <div class="dropdown">
                                                                            <a class="text-muted dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                <i class="mdi mdi-dots-horizontal"></i>
                                                                            </a>
                                                                        
                                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                            <a class="dropdown-item" href="#">Action</a>
                                                                            <a class="dropdown-item" href="#">Another action</a>
                                                                            <a class="dropdown-item" href="#">Something else here</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
    
                                                    <li class="list-group-item">
                                                        <div>
                                                            <div class="d-flex align-items-start">
                                                                <div class="flex-grow-1">
                                                                    <div class="badge bg-success bg-gradient mb-2"><i class="mdi mdi-star"></i> 4.2</div>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    <p class="text-muted font-size-13">26 Jun, 21</p>
                                                                </div>
                                                            </div>
                                                            <p class="text-muted mb-4">Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet</p>
                                                            <div class="d-flex align-items-start">
                                                                <div class="flex-grow-1">
                                                                    <h5 class="font-size-14 mb-0">Paul</h5>
                                                                </div>
            
                                                                <div class="flex-shrink-0">
                                                                    <div class="hstack gap-3">
                                                                        <div data-bs-toggle="tooltip" data-bs-placement="top" title="Like">
                                                                            <a href="#" class="text-muted"><i class="mdi mdi-thumb-up-outline"></i></a>
                                                                        </div>
                                                                        <div class="vr"></div>
                                                                        <div data-bs-toggle="tooltip" data-bs-placement="top" title="Comment">
                                                                            <a href="#" class="text-muted"><i class="mdi mdi-comment-text-outline"></i></a>
                                                                        </div>
                                                                        <div class="vr"></div>
                                                                        <div class="dropdown">
                                                                            <a class="text-muted dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                <i class="mdi mdi-dots-horizontal"></i>
                                                                            </a>
                                                                        
                                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                            <a class="dropdown-item" href="#">Action</a>
                                                                            <a class="dropdown-item" href="#">Another action</a>
                                                                            <a class="dropdown-item" href="#">Something else here</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
    
                                                    <li class="list-group-item">
                                                        <div>
                                                            <div class="d-flex align-items-start">
                                                                <div class="flex-grow-1">
                                                                    <div class="badge bg-success bg-gradient mb-2"><i class="mdi mdi-star"></i> 4.1</div>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    <p class="text-muted font-size-13">24 Jun, 21</p>
                                                                </div>
                                                            </div>
                                                            <p class="text-muted mb-4">Ut enim ad minima veniam, quis nostrum ullam corporis suscipit consequatur nisi ut</p>
                                                            <div class="d-flex align-items-start">
                                                                <div class="flex-grow-1">
                                                                    <h5 class="font-size-14 mb-0">Patrick</h5>
                                                                </div>
            
                                                                <div class="flex-shrink-0">
                                                                    <div class="hstack gap-3">
                                                                        <div data-bs-toggle="tooltip" data-bs-placement="top" title="Like">
                                                                            <a href="#" class="text-muted"><i class="mdi mdi-thumb-up-outline"></i></a>
                                                                        </div>
                                                                        <div class="vr"></div>
                                                                        <div data-bs-toggle="tooltip" data-bs-placement="top" title="Comment">
                                                                            <a href="#" class="text-muted"><i class="mdi mdi-comment-text-outline"></i></a>
                                                                        </div>
                                                                        <div class="vr"></div>
                                                                        <div class="dropdown">
                                                                            <a class="text-muted dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                <i class="mdi mdi-dots-horizontal"></i>
                                                                            </a>
                                                                        
                                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                            <a class="dropdown-item" href="#">Action</a>
                                                                            <a class="dropdown-item" href="#">Another action</a>
                                                                            <a class="dropdown-item" href="#">Something else here</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end col -->
                                </div>
                                <!-- end row -->
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                </div> --}}
                <div class="card">
                    <div class="card-body pb-0">
                        <div class="d-flex align-items-start">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-3">Overview</h5>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="dropdown">
                                    <a class="dropdown-toggle text-reset" href="#" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <span class="fw-semibold">Sort By:</span> <span
                                            class="text-muted">Yearly<i
                                                class="mdi mdi-chevron-down ms-1"></i></span>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#">Yearly</a>
                                        <a class="dropdown-item" href="#">Monthly</a>
                                        <a class="dropdown-item" href="#">Weekly</a>
                                        <a class="dropdown-item" href="#">Today</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row gy-4">
                            <div class="col-xxl-3">
                                <div>
                                    <div class="mt-3 mb-3">
                                        <p class="text-muted mb-1">This Month</p>

                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <h2 class="mb-0">$24,568</h2>
                                            <div class="badge rounded-pill font-size-13 badge-soft-success">+
                                                2.65%</div>
                                        </div>
                                    </div>

                                    <div class="row g-0">
                                        <div class="col-sm-6">
                                            <div class="border-bottom border-end p-3 h-100">
                                                <p class="text-muted text-truncate mb-1">Ticketed</p>
                                                <h5 class="text-truncate mb-0">5,643</h5>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="border-bottom p-3 h-100">
                                                <p class="text-muted text-truncate mb-1">Booked</p>
                                                <h5 class="text-truncate mb-0">16,273</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-0">
                                        <div class="col-sm-6">
                                            <div class="border-bottom border-end p-3 h-100">
                                                <p class="text-muted text-truncate mb-1">Order Value</p>
                                                <h5 class="text-truncate mb-0">12.03 %</h5>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="border-bottom p-3 h-100">
                                                <p class="text-muted text-truncate mb-1">Cancel</p>
                                                <h5 class="text-truncate mb-0">21</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-0">
                                        <div class="col-sm-6">
                                            <div class="border-end p-3 h-100">
                                                <p class="text-muted text-truncate mb-1">Income</p>
                                                <h5 class="text-truncate mb-0">$35,652</h5>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="p-3 h-100">
                                                <p class="text-muted text-truncate mb-1">Expenses</p>
                                                <h5 class="text-truncate mb-0">$12,248</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xxl-9">
                                <div>
                                    <div id="chart-column" class="apex-charts" data-colors='["#f1f3f7", "#3b76e1"]'></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end card body -->
                </div>
                <!-- end row -->
            </div>
            <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
    </div>
@endsection

@section('scripts')
<script>
    var orderStatus = {!! json_encode($data) !!};
    var series = [];
    var labels = [];
    for (const [key, value] of Object.entries(orderStatus)) {
        series.push(value.count);
        labels.push(key);
    }
    // console.log(series);  // [233027, 137949, 25080, 147394]
    // console.log(labels);
</script>
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/dashboard.init.js') }}"></script>

<script>
    function showBooking(status) {
        $('.select_status').text(status);
        $.ajax({
            type: "POST",
            url: "{{ route('admin.dashboard.recent.booking') }}",
            data: {
                status: status 
            },
            dataType: "json",
            success: function(data) {
                if(data.message == 'success'){
                    $('#recent-booking').html(data.html);
                }else{
                    Swal.fire({
                        text: data.message,
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
                
            }
        });
    }
</script>
@endsection







