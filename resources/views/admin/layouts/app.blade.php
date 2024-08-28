<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Dashboard | {{ config('constants.SITE_TITLE') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/mainLogo.jpg') }}">

    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <!-- nouisliderribute css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/nouislider/nouislider.min.css') }}">
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    {{-- <link href="{{ asset('assets/css/typeahead.css') }}" rel="stylesheet" type="text/css" /> --}}
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    @yield('styles')

    <style>
        body[data-sidebar-size=sm] .vertical-menu #sidebar-menu>ul>li>a i {
            font-size: 14px;
            text-align: center;
            min-width: 1.5rem;
            padding-bottom: 0;
        }
        .icon-demo-content:hover i{
            color: #fff;
            fill: #fff;
            background-color: #3b76e1;
            border-color: #3b76e1;
        }
        .swal2-popup{
            border-radius: 10px !important;
        }
    </style>
    <script>
        var baseUrl = "{{ asset('/') }}";
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
		        headers: {
		            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
		        }
		    });
            // Load saved settings from localStorage
            // loadSettingsFromLocalStorage();

            // // Function to load settings from localStorage
            // function loadSettingsFromLocalStorage() {
            //     const savedSettings = JSON.parse(localStorage.getItem('layout-settings'));

            //     if (savedSettings) {
            //         applyBodyAttributes(savedSettings);

            //         $("input[name='layout'][value='" + savedSettings.layout + "']").prop('checked', true);
            //         $("input[name='layout-mode'][value='" + savedSettings.layoutMode + "']").prop('checked', true);
            //         $("input[name='layout-width'][value='" + savedSettings.layoutWidth + "']").prop('checked', true);
            //         $("input[name='layout-position'][value='" + savedSettings.layoutPosition + "']").prop('checked', true);
            //         $("input[name='topbar-color'][value='" + savedSettings.topbarColor + "']").prop('checked', true);
            //         $("input[name='sidebar-size'][value='" + savedSettings.sidebarSize + "']").prop('checked', true);
            //         $("input[name='sidebar-color'][value='" + savedSettings.sidebarColor + "']").prop('checked', true);

            //     }
            // }
            // // Function to apply changes to body attributes
            // function applyBodyAttributes(settings) {
            //     $('body')
            //         .attr('data-layout', settings.layout)
            //         .attr('data-topbar', settings.topbarColor)
            //         .attr('data-layout-mode', settings.layoutMode)
            //         .attr('data-sidebar', settings.sidebarColor)
            //         .attr('data-layout-size', settings.layoutWidth)
            //         .attr('data-layout-scrollable', settings.layoutPosition === 'scrollable')
            //         .attr('data-sidebar-size', settings.sidebarSize);
            // }

            // // When the first button is clicked
            // $('#collapse-sidebar-sm').on('click', function () {
            //     // console.log('sm');
            //     saveSettingsToLocalStorage('sm');
            // });

            // // When the second button is clicked
            // $('#extend-sidebar-lg').on('click', function () {
            //     // console.log('lg');
            //     saveSettingsToLocalStorage('lg');
            // });

            // // Save settings to localStorage when radio buttons change
            // $("input[name='layout'], input[name='layout-mode'], input[name='layout-width'], input[name='layout-position'], input[name='topbar-color'], input[name='sidebar-size'], input[name='sidebar-color']").change(function() {
            //     var sidebarSize = $("input[name='sidebar-size']:checked").val();
            //     saveSettingsToLocalStorage(sidebarSize);
            // });
            // // Function to save settings to localStorage
            // function saveSettingsToLocalStorage(sidebarSize) {
            //     localStorage.setItem('layout-settings', JSON.stringify({
            //         layout: $("input[name='layout']:checked").val(),
            //         layoutMode: $("input[name='layout-mode']:checked").val(),
            //         layoutWidth: $("input[name='layout-width']:checked").val(),
            //         layoutPosition: $("input[name='layout-position']:checked").val(),
            //         topbarColor: $("input[name='topbar-color']:checked").val(),
            //         sidebarSize: sidebarSize,
            //         sidebarColor: $("input[name='sidebar-color']:checked").val(),
            //         sidebarSizeClass: $("input[name='sidebar-size']:checked").val(),
            //     }));
            // }            
        });
    </script>
</head>
<body>
    <div id="layout-wrapper">
        <div id="notification-container" style="position: fixed; top: 90px; right: 20px; z-index: 1010;">
                                
            @if(session('error'))
                <div class="toast align-items-center text-white bg-danger  border-0 show" role="alert" aria-live="assertive" id="toast-error" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            {{ session('error') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            @endif
            @if(session('success'))
                <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" id="toast" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            {{ session('success') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>

                <div id="success-btn" class="modal fade" tabindex="-1" aria-labelledby="success-btnLabel" aria-hidden="true" data-bs-scroll="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body">
                               <div class="text-center">
                                   <i class="bx bx-check-circle display-1 text-success"></i>
                                   <h3 class="mt-3">User Added Successfully</h3>
                               </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
        </div>

        @include('admin.layouts.includes.top-header')
        <!-- ========== Left Sidebar Start ========== -->
        @include('admin.layouts.includes.sidebar')
        <!-- Left Sidebar End -->
        <header id="page-topbar" class="ishorizontal-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="index.html" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/mainLogo.jpg') }}" alt="" height="22">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/mainLogo.jpg') }}" alt="" height="22">
                            </span>
                        </a>

                        <a href="index.html" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/mainLogo.jpg') }}" alt="" height="22">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/mainLogo.jpg') }}" alt="" height="22">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 d-lg-none header-item" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>

                    <div class="d-none d-sm-block ms-2 align-self-center">
                        <h4 class="page-title">Dashboard</h4>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="dropdown">
                        <button type="button" class="btn header-item"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-sm" data-eva="search-outline"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-md p-0">
                            <form class="p-2">
                                <div class="search-box">
                                    <div class="position-relative">
                                        <input type="text" class="form-control bg-light border-0" placeholder="Search...">
                                        <i class="search-icon" data-eva="search-outline" data-eva-height="26" data-eva-width="26"></i>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>


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
                            @if(adminUser()->type != 'admin')
                                <div class="p-2 border-bottom">
                                    <p class="mb-0 font-size-11 text-primary fw-bold d-flex justify-content-between">
                                        <span>Used</span>
                                        <span>PKR 10000</span>
                                    </p>
                                    <p class="mb-0 font-size-11 text-primary fw-bold d-flex justify-content-between">
                                        <span>Remaining</span>
                                        <span>PKR {{ adminUser()->credit_limit - 10000 }}</span>
                                    </p>
                                    <p class="mb-0 font-size-11 text-primary fw-bold d-flex justify-content-between">
                                        <span>Total</span>
                                        <span>PKR {{ adminUser()->credit_limit }}</span>
                                    </p>
                                </div>
                            @endif
                            <a class="dropdown-item" href="{{ route('admin.users.profile') }}">
                                <i class="mdi mdi-account-circle text-muted font-size-16 align-middle me-1"></i> 
                                <span class="align-middle">Profile</span>
                            </a>
                            <a class="dropdown-item" href="{{ url('admin/logout') }}">
                                <i class="mdi mdi-logout text-muted font-size-16 align-middle me-1"></i>
                                <span class="align-middle">Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="topnav">
                <div class="container-fluid">
                    <nav class="navbar navbar-light navbar-expand-lg topnav-menu">
        
                        <div class="collapse navbar-collapse" id="topnav-menu-content">
                            <ul class="navbar-nav">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle arrow-none" href="{{ route('admin.dashboard') }}" id="topnav-dashboard" role="button"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="icon nav-icon" data-eva="grid-outline"></i>
                                        <span data-key="t-dashboards">Dashboards</span> <div class="arrow-down"></div>
                                    </a>
                                </li>
                                {{-- <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-dashboard" role="button"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="icon nav-icon" data-eva="grid-outline"></i>
                                        <span data-key="t-dashboards">Dashboards</span> <div class="arrow-down"></div>
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
                                       <a href="index.html" class="dropdown-item" data-key="t-ecommerce">Ecommerce</a>
                                       <a href="dashboard-saas.html" class="dropdown-item" data-key="t-saas">Saas</a>
                                       <a href="dashboard-crypto.html" class="dropdown-item" data-key="t-crypto">Crypto</a>
                                    </div>
                                </li> --}}
        
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </header>

        @yield('content')

        <!-- Right Sidebar -->
        <div class="right-bar">
            <div data-simplebar class="h-100">
                <div class="rightbar-title d-flex align-items-center bg-dark p-3">

                    <h5 class="m-0 me-2 text-white">Theme Customizer</h5>

                    <a href="javascript:void(0);" class="right-bar-toggle-close ms-auto">
                        <i class="mdi mdi-close noti-icon"></i>
                    </a>
                </div>

                <!-- Settings -->
                <hr class="m-0" />

                <div class="p-4">
                    <h6 class="mb-3">Layout</h6>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout"
                            id="layout-vertical" value="vertical">
                        <label class="form-check-label" for="layout-vertical">Vertical</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout"
                            id="layout-horizontal" value="horizontal">
                        <label class="form-check-label" for="layout-horizontal">Horizontal</label>
                    </div>

                    <h6 class="mt-4 mb-3">Layout Mode</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-mode"
                            id="layout-mode-light" value="light">
                        <label class="form-check-label" for="layout-mode-light">Light</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-mode"
                            id="layout-mode-dark" value="dark">
                        <label class="form-check-label" for="layout-mode-dark">Dark</label>
                    </div>

                    <h6 class="mt-4 mb-3">Layout Width</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-width"
                            id="layout-width-fluid" value="fluid" onchange="document.body.setAttribute('data-layout-size', 'fluid')">
                        <label class="form-check-label" for="layout-width-fluid">Fluid</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-width"
                            id="layout-width-boxed" value="boxed" onchange="document.body.setAttribute('data-layout-size', 'boxed')">
                        <label class="form-check-label" for="layout-width-boxed">Boxed</label>
                    </div>

                    <h6 class="mt-4 mb-3">Layout Position</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-position"
                            id="layout-position-fixed" value="fixed" onchange="document.body.setAttribute('data-layout-scrollable', 'false')">
                        <label class="form-check-label" for="layout-position-fixed">Fixed</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="layout-position"
                            id="layout-position-scrollable" value="scrollable" onchange="document.body.setAttribute('data-layout-scrollable', 'true')">
                        <label class="form-check-label" for="layout-position-scrollable">Scrollable</label>
                    </div>

                    <h6 class="mt-4 mb-3">Topbar Color</h6>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="topbar-color"
                            id="topbar-color-light" value="light" onchange="document.body.setAttribute('data-topbar', 'light')">
                        <label class="form-check-label" for="topbar-color-light">Light</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="topbar-color"
                            id="topbar-color-dark" value="dark" onchange="document.body.setAttribute('data-topbar', 'dark')">
                        <label class="form-check-label" for="topbar-color-dark">Dark</label>
                    </div>

                    <div id="sidebar-setting">
                        <h6 class="mt-4 mb-3 sidebar-setting">Sidebar Size</h6>

                        <div class="form-check sidebar-setting">
                            <input class="form-check-input" type="radio" name="sidebar-size"
                                id="sidebar-size-default" value="lg" onchange="document.body.setAttribute('data-sidebar-size', 'lg')">
                            <label class="form-check-label" for="sidebar-size-default">Default</label>
                        </div>
                        <div class="form-check sidebar-setting">
                            <input class="form-check-input" type="radio" name="sidebar-size"
                                id="sidebar-size-compact" value="md" onchange="document.body.setAttribute('data-sidebar-size', 'md')">
                            <label class="form-check-label" for="sidebar-size-compact">Compact</label>
                        </div>
                        <div class="form-check sidebar-setting">
                            <input class="form-check-input" type="radio" name="sidebar-size"
                                id="sidebar-size-small" value="sm" onchange="document.body.setAttribute('data-sidebar-size', 'sm')">
                            <label class="form-check-label" for="sidebar-size-small">Small (Icon View)</label>
                        </div>

                        <h6 class="mt-4 mb-3 sidebar-setting">Sidebar Color</h6>

                        <div class="form-check sidebar-setting">
                            <input class="form-check-input" type="radio" name="sidebar-color"
                                id="sidebar-color-light" value="light" onchange="document.body.setAttribute('data-sidebar', 'light')">
                            <label class="form-check-label" for="sidebar-color-light">Light</label>
                        </div>
                        <div class="form-check sidebar-setting">
                            <input class="form-check-input" type="radio" name="sidebar-color"
                                id="sidebar-color-dark" value="dark" onchange="document.body.setAttribute('data-sidebar', 'dark')">
                            <label class="form-check-label" for="sidebar-color-dark">Dark</label>
                        </div>
                        <div class="form-check sidebar-setting">
                            <input class="form-check-input" type="radio" name="sidebar-color"
                                id="sidebar-color-brand" value="brand" onchange="document.body.setAttribute('data-sidebar', 'brand')">
                            <label class="form-check-label" for="sidebar-color-brand">Brand</label>
                        </div>
                    </div>
                </div>

            </div> <!-- end slimscroll-menu-->
        </div>
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <script>document.write(new Date().getFullYear())</script> &copy; Indus Travel. Design <a href="www.hamidafridi.info">&<a> Develop by
                    </div>
                </div>
            </div>
        </footer>
    </div>

    
    <!-- Add your common JavaScript files here -->

    <!-- JAVASCRIPT -->
        <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    
        <script src="{{ asset('assets/libs/metismenujs/metismenujs.min.js') }}"></script>
        <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/libs/eva-icons/eva.min.js') }}"></script>
        <!-- materialdesign icon js-->
        {{-- <script src="{{ asset('assets/js/pages/materialdesign.init.js') }}"></script> --}}
        <!-- apexcharts -->
        <script src="{{ asset('assets/js/app.js') }}"></script>
        <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
        <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
        {{-- <script src="{{ asset('assets/js/pages/sweet-alerts.init.js') }}"></script> --}}

        <script>
            $(document).ready(function() {
                // Automatically hide the toast after 5 seconds
                setTimeout(function() {
                    $('#toast,#toast-error').toast('hide');
                }, 5000); // 5000 milliseconds = 5 seconds
            });
            // ************Sweet Alert Delete****************
            function showSweetAlertDelete(title, text, icon, confirmButtonText, cancelButtonText, confirmCallback, cancelCallback) {
                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: cancelButtonText,
                    confirmButtonClass: "btn btn-success mt-2",
                    cancelButtonClass: "btn btn-danger ms-2 mt-2",
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed && confirmCallback) {
                        confirmCallback();
                    } else if (result.dismiss === Swal.DismissReason.cancel && cancelCallback) {
                        cancelCallback();
                    }
                });
            }
            function showSweetAlert(text,icon,confirmButtonText,btnClass,confirmCallback){
                Swal.fire({
                    text: text,
                    icon: icon,
                    buttonsStyling: false,
                    confirmButtonText: confirmButtonText,
                    customClass: {
                        confirmButton: btnClass
                    }
                }).then(function(result) {
                    if (result.isConfirmed && confirmCallback) {
                        confirmCallback();
                    } else if (result.dismiss === Swal.DismissReason.cancel && cancelCallback) {
                        cancelCallback();
                    }
                });
            }
        </script>
        <script>
            // window.addEventListener('beforeunload', function (event) {
            //     navigator.sendBeacon('', JSON.stringify({
            //         _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            //     }));
            // });
        </script>
    @yield('scripts')
</body>
</html>