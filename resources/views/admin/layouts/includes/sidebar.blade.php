<div class="vertical-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/mainLogo.png') }}" alt="" height="70">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/mainLogo.png') }}" alt="" height="70">
            </span>
        </a>

        <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
            <span class="logo-lg">
                <img src="{{ asset('assets/images/mainLogo.png') }}" alt="" height="70">
            </span>
            <span class="logo-sm">
                <img src="{{ asset('assets/images/mainLogo.png') }}" alt="" height="70">
            </span>
        </a>
    </div>

    <button type="button" class="btn btn-sm px-3 header-item vertical-menu-btn topnav-hamburger" id="collapse-sidebar-sm">
        <div class="hamburger-icon">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </button>

    <div data-simplebar class="sidebar-menu-scroll">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" data-key="t-menu">Menu</li>

                <li class="{{ Request::is('admin/dashboard') ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="icon nav-icon" data-eva="grid-outline"></i>
                        <span class="menu-item" data-key="t-dashboards">Dashboards</span>
                    </a>
                </li>
                
                @if(auth('admin')->user()->can('Availability-Search'))
                    <li class="{{ Request::is('admin/flight/search') ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.flight.search') }}">
                            <i class="fas fa-paper-plane"></i>
                            <span class="menu-item" data-key="t-dashboards">Search Flight</span>
                        </a>
                    </li>
                @endif
                {{-- @if(auth('admin')->user()->can('Read-Booking')) --}}
                    <li class="{{ Request::is('admin/bookings') ? 'mm-active' : '' }}">
                        <a href="{{ url('admin/bookings') }}">
                            <i class="icon nav-icon" data-eva="bookmark"></i>
                            <span class="menu-item" data-key="t-contacts">Bookings</span>
                        </a>
                    </li>
                {{-- @endif --}}
                @if(auth('admin')->user()->can('List-Users'))
                    <li class="{{ Request::is('admin/users') ? 'mm-active' : '' }}">
                        <a href="{{ url('admin/users') }}">
                            <i class="icon nav-icon" data-eva="person-done-outline"></i>
                            <span class="menu-item" data-key="t-contacts">Admin Users</span>
                        </a>
                    </li>
                @endif
                @if(auth('admin')->user()->can('List-Of-Travel-Agencies'))
                    <li class="{{ Request::is('admin/agency-list') ? 'mm-active' : '' }}">
                        <a href="{{ url('admin/agency-list') }}">
                            <i class="icon nav-icon" data-eva="person-done-outline"></i>
                            <span class="menu-item" data-key="t-contacts">Travel Agencies</span>
                        </a>
                    </li>
                @endif

                {{-- <li class="{{ Request::is('admin/agents') ? 'mm-active' : '' }}">
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="icon nav-icon" data-eva="people-outline"></i>
                        <span class="menu-item" data-key="t-contacts">Travel Agents</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li>
                            <a href="{{ url('admin/agents') }}" data-key="t-inbox">Travel Agents List</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.pricingEngine.list') }}" data-key="t-read-email">Pricing Engine</a>
                        </li> 
                    </ul>
                </li> --}}

                @if(auth('admin')->user()->can('List-Of-Roles'))
                    <li class="{{ Request::is('admin/roles') ? 'mm-active' : '' }}">
                        <a href="{{ url('admin/roles') }}">
                            <i class="fas fa-user-lock"></i>
                            <span class="menu-item" data-key="t-contacts">Roles & Permisstions</span>
                        </a>
                    </li>
                @endif
                @if(auth('admin')->user()->can('List-Of-Roles'))
                    <li class="{{ Request::is('admin/pricing-groups') ? 'mm-active' : '' }}">
                        <a href="{{ url('admin/pricing-groups') }}">
                            <i class="fas fa-user-lock"></i>
                            <span class="menu-item" data-key="t-contacts">Pricing Groups</span>
                        </a>
                    </li>
                @endif
                
                @if(auth('admin')->user()->can('Read-Settings'))
                    <li class="{{ Request::is('admin/setting') ? 'mm-active' : '' }}">
                        <a href="{{ url('admin/setting') }}">
                            <i class="fas fa-cog"></i>
                            <span class="menu-item" data-key="t-contacts">Settings</span>
                        </a>
                    </li>
                @endif

                <li class="{{ Request::is('/faqs') ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.faqs')}}">
                        <i class="fas fa-question"></i>
                        <span class="menu-item" data-key="t-contacts">FAQs</span>
                    </a>
                </li>

                <li class="{{ Request::is('/news') ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.news')}}">
                        <i class="far fa-newspaper"></i>
                        <span class="menu-item" data-key="t-contacts">News</span>
                    </a>
                </li>
                <li class="{{ Request::is('/bank-accounts') ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.bank.accounts')}}">
                        <i class="fas fa-credit-card"></i>
                        <span class="menu-item" data-key="t-contacts">Bank Accounts</span>
                    </a>
                </li>

            </ul>
        </div>
        <!-- Sidebar -->

        {{-- <div class="p-3 px-4 sidebar-footer">
            <p class="mb-1 main-title"><script>document.write(new Date().getFullYear())</script> &copy; Borex.</p>
            <p class="mb-0">Design & Develop by Royal Tech</p>
        </div> --}}
    </div>
</div>