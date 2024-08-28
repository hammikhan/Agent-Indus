<div class="card border shadow-none" style="">
    <div class="card-body" style="padding-bottom: 2px;">
        <div class="d-flex align-items-start border-bottom pb-3">
            <div class="me-4">
                @if($user->profile_image =='')
                    <img src="{{ asset('assets/admin-images/user2.png') }}" alt="" class="avatar-lg rounded">
                @else
                    <img src="{{ asset($user->profile_image) }}" alt="" class="avatar-lg rounded">
                @endif
            </div>
            <div class="flex-grow-1 align-self-center overflow-hidden">
                <div>
                    <h5 class="text-truncate font-size-18">
                        <a href="ecommerce-product-detail-2.html" class="text-dark">{{ $user->first_name }} {{ $user->last_name }}</a>
                    </h5>
                    <p class="text-muted mb-0">
                        <i class="bx bxs-star text-warning"></i>
                        <i class="bx bxs-star text-warning"></i>
                        <i class="bx bxs-star text-warning"></i>
                        <i class="bx bxs-star text-warning"></i>
                        <i class="bx bxs-star-half text-warning"></i>
                    </p>
                    <p class="mb-0 mt-1">Role : <span class="fw-medium">{{ $user->role}}</span></p>
                </div>
            </div>
            
        </div>

        <div>
            <div class="row">
                <div class="col-md-6">
                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                        @if(auth('admin')->user()->can('Edit-Users'))
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('admin/view-user/*') ? 'active' : '' }}" href="{{ route('admin.user.view',[$user->id]) }}" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                    <span class="d-none d-sm-block">Edit</span> 
                                </a>
                            </li>
                        @endif

                        {{-- <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/pricing-engine/*') ? 'active' : '' }}" href="{{ route('admin.pricingEngine.list',[$user->id]) }}" role="tab">
                                <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                <span class="d-none d-sm-block">Pricing Engine</span>   
                            </a>
                        </li> --}}

                        {{-- @if(auth('admin')->user()->can('Read-Booking')) --}}
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#profile1" role="tab">
                                    <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                    <span class="d-none d-sm-block">Bookings</span> 
                                </a>
                            </li>
                        {{-- @endif --}}
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