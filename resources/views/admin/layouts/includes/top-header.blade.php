<header id="page-topbar" class="isvertical-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/mainLogo.png') }}" alt="" height="22">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ asset('assets/images/mainLogo.png') }}" alt="" height="22">
                    </span>
                </a>

                <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
                    <span class="logo-lg">
                        <img src="{{ asset('assets/images/mainLogo.png') }}" alt="" height="22">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('assets/images/mainLogo.png') }}" alt="" height="22">
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item vertical-menu-btn topnav-hamburger" id="extend-sidebar-lg">
                <div class="hamburger-icon open">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>

            <div class="d-none d-sm-block ms-3 align-self-center">
                @yield('breadecrum')
            </div>

        </div>

        <div class="d-flex">
            @if (Request::is('admin/booking/*'))
                @if(adminUser()->type != 'admin')
                    <span class="text-danger fw-bold me-2 fs-5" style="line-height: 70px;">
                        <div class="spinner-grow text-danger m-1" role="status" style="vertical-align: -0.3em; width: 1rem; height:1rem">
                            <span class="sr-only">Loading...</span>
                        </div>
                        {{checkCreditLimit($order->total)}}
                    </span>
                @endif
            @endif
            @if(auth('admin')->user()->can('Search-Flights'))
                <div class="dropdown d-inline-block">
                    <div class="" style="line-height: 70px;">
                        <a href="{{ route('admin.flight.search') }}" class="btn btn-primary w-md">Search Flight</a>
                    </div>
                </div>
            @endif
            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon right-bar-toggle" id="right-bar-toggle">
                    <i class="icon-sm" data-eva="settings-outline"></i>
                </button>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item user text-start d-flex align-items-center" id="page-header-user-dropdown"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    @if(adminUser()->profile_image =='')
                        <img class="rounded-circle header-profile-user" src="{{ asset('assets/admin-images/user2.png') }}" alt="Header Avatar">
                    @else
                        <img class="rounded-circle header-profile-user" src="{{ asset(adminUser()->profile_image) }}" alt="Header Avatar">
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end pt-0">
                    <div class="p-3 border-bottom">
                        <h6 class="mb-0">{{ auth()->guard('admin')->user()->first_name }} {{ auth()->guard('admin')->user()->last_name }}</h6>
                        <p class="mb-2 font-size-11 text-muted">{{ auth()->guard('admin')->user()->email }}</p>
                    </div>
                    @if(Auth::guard('admin')->user()->type != 'admin' && Auth::guard('admin')->user()->type != 'Admin User')
                        <div class="p-2 border-bottom">
                            <p class="mb-0 font-size-11 text-primary fw-bold d-flex justify-content-between">
                                <span>Total</span>
                                <span>PKR {{ creditLimitAssigned()['totalCredit'] }}</span>
                            </p>
                            <p class="mb-0 font-size-11 text-info fw-bold d-flex justify-content-between">
                                <span>Used</span>
                                <span>PKR {{ creditLimitAssigned()['usedCredit'] }}</span>
                            </p>
                            <p class="mb-0 font-size-11 text-warning fw-bold d-flex justify-content-between">
                                <span>Remaining</span>
                                <span>PKR {{ creditLimitAssigned()['remaining'] }}</span>
                            </p>
                        </div>
                    @endif
                    <a class="dropdown-item" href="{{ route('admin.users.profile') }}">
                        <i class="mdi mdi-account-circle text-muted font-size-16 align-middle me-1"></i> 
                        <span class="align-middle">Profile</span>
                    </a>
                    {{-- <a class="dropdown-item" href="#">
                        <i class="mdi mdi-account-circle text-muted font-size-16 align-middle me-1"></i> 
                        <span class="align-middle">Profile</span>
                    </a> --}}
                    {{-- <a class="dropdown-item" href="apps-chat.html"><i class="mdi mdi-message-text-outline text-muted font-size-16 align-middle me-1"></i> <span class="align-middle">Messages</span></a>
                    <a class="dropdown-item" href="pages-faqs.html"><i class="mdi mdi-lifebuoy text-muted font-size-16 align-middle me-1"></i> <span class="align-middle">Help</span></a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#"><i class="mdi mdi-wallet text-muted font-size-16 align-middle me-1"></i> <span class="align-middle">Balance : <b>$6951.02</b></span></a>
                    <a class="dropdown-item d-flex align-items-center" href="#"><i class="mdi mdi-cog-outline text-muted font-size-16 align-middle me-1"></i> <span class="align-middle">Settings</span><span class="badge badge-soft-success ms-auto">New</span></a>
                    <a class="dropdown-item" href="auth-lock-screen.html"><i class="mdi mdi-lock text-muted font-size-16 align-middle me-1"></i> <span class="align-middle">Lock screen</span></a> --}}
                    <a class="dropdown-item" href="{{ url('admin/logout') }}">
                        <i class="mdi mdi-logout text-muted font-size-16 align-middle me-1"></i>
                        <span class="align-middle">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>