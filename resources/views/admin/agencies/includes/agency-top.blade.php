<div class="card border shadow-none" style="">
    <div class="card-body" style="padding-bottom: 2px;">
        <div class="d-flex align-items-start border-bottom pb-3">
            <div class="me-4">
                @if($agency->logo =='')
                    <img src="{{ asset('assets/admin-images/user2.png') }}" alt="" class="avatar-lg rounded">
                @else
                    <img src="{{ asset($agency->logo) }}" alt="" class="avatar-lg rounded">
                @endif
            </div>
            <div class="flex-grow-1 align-self-center overflow-hidden">
                <div>
                    <h5 class="text-truncate font-size-18">
                        <a href="ecommerce-product-detail-2.html" class="text-dark">{{ $agency->name }}</a>
                    </h5>
                    <p class="text-muted mb-0">
                        <i class="bx bxs-star text-warning"></i>
                        <i class="bx bxs-star text-warning"></i>
                        <i class="bx bxs-star text-warning"></i>
                        <i class="bx bxs-star text-warning"></i>
                        <i class="bx bxs-star-half text-warning"></i>
                    </p>
                    <p class="mb-0 mt-1">Address : <span class="fw-medium">{{ $agency->address}}</span></p>
                </div>
            </div>
        </div>

        <div>
            <div class="row">
                <div class="col-md-6">
                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                        @if(auth('admin')->user()->can('Edit-Travel-Agency'))
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('admin/view-agency/*') ? 'active' : '' }}" href="{{ route('admin.agency.view',[$agency->id]) }}" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                    <span class="d-none d-sm-block">Edit</span> 
                                </a>
                            </li>
                        @endif
                        @if(auth('admin')->user()->can('List-Travel-Agents'))
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/agency-agents/*') ? 'active' : '' }}" href="{{ route('admin.agency.agents',[$agency->id]) }}" role="tab">
                                <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                <span class="d-none d-sm-block">Users</span> 
                            </a>
                        </li>
                        @endif
                        {{-- @if(auth('admin')->user()->can('Read-Booking')) --}}
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#profile1" role="tab">
                                <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                <span class="d-none d-sm-block">Bookings</span> 
                            </a>
                        </li>
                        {{-- @endif --}}
                        @if(auth('admin')->user()->can('List-Credit-Limit'))
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('admin/agency/*/credit-limit') ? 'active' : '' }}" href="{{ route('admin.agency.creditlimit',[$agency->id]) }}" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                    <span class="d-none d-sm-block">Credit Limits</span> 
                                </a>
                            </li>
                        @endif
                        {{-- <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#settings1" role="tab">
                                <span class="d-block d-sm-none"><i class="fas fa-cog"></i></span>
                                <span class="d-none d-sm-block">XYZ</span>    
                            </a>
                        </li> --}}
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>