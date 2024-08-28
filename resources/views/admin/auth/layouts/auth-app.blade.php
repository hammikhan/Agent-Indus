<!doctype html>
<html lang="en">

    
    <!-- Mirrored from themesbrand.com/borex/layouts/auth-login.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 14 Mar 2023 05:24:42 GMT -->
    <head>
        <meta charset="utf-8" />
        <title>Login | {{ config('constants.SITE_TITLE') }} B2B</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesbrand" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/images/mainLogo.png') }}">

        <!-- Bootstrap Css -->
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
        @yield('styles')
        <style>
            .error{
                color: red;
            }
            .otp-input {
                border: 1px solid #3b76e1 !important;
                text-align: center;
            }
        </style>
    </head>

   <body>
        <div class="auth-page">
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
                @endif
                
            </div>
            <div class="container-fluid p-0">
                <div class="row g-0">
                    <div class="col-xxl-12 col-lg-12 col-md-12 col-sm-12">
                        <div class="row justify-content-center g-0">
                            <div class="col-xl-12 bg-white">
                                <div class="">
                                    <div class="card mb-0 rounded-0 border-end-1" style="box-shadow:unset">
                                        <div class="card-body p-md-5">
                                            <div class="row justify-content-center g-0">
                                                <div class="col-xl-4">
                                                    <div class="p-md-4">
                                                        @yield('content')
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-4 text-center mb-5 ">
                                            <p class="mb-0">© <script>document.write(new Date().getFullYear())</script> {{ config('constants.SITE_TITLE') }} B2B <i class="mdi mdi-heart text-danger"></i> by DevHubx.com</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end auth full page content -->
                    </div>
                    <!-- end col -->
                    {{-- <div class="col-xxl-6 col-lg-6 col-md-6 col-sm-12 d-none d-md-block d-lg-block d-xl-block d-flex align-items-center">
                        @include('admin.auth.includes.auth-right-sidebar')
                    </div> --}}
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container fluid -->
        </div>

        <!-- JAVASCRIPT -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
        <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/libs/metismenujs/metismenujs.min.js') }}"></script>
        <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/libs/eva-icons/eva.min.js') }}"></script>
        <script src="{{ asset('assets/js/pages/pass-addon.init.js') }}"></script>
        <script src="{{ asset('assets/js/pages/eva-icon.init.js') }}"></script>
        <script>
            $(document).ready(function() {
                // Automatically hide the toast after 5 seconds
                setTimeout(function() {
                    $('#toast,#toast-error').toast('hide');
                }, 5000); // 5000 milliseconds = 5 seconds
            });
        </script>
        @yield('scripts')
    </body>

<!-- Mirrored from themesbrand.com/borex/layouts/auth-login.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 14 Mar 2023 05:24:42 GMT -->
</html>