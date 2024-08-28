@extends('admin.auth.layouts.auth-app')


@section('content')
    <div class="row justify-content-center g-0">
        <div class="col-xl-12">
            <div class="py-4">
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="auth-full-page-content rounded d-flex p-3 my-2">
                            <div class="w-100">
                                <div class="d-flex flex-column h-100">
                                    <div class="mb-4 mb-md-5">
                                        <a href="{{ url('admin') }}" class="d-block auth-logo">
                                            <img src="{{ asset('assets/images/mainLogo.jpg') }}" alt="" height="100" class="auth-logo-dark me-start">
                                        </a>
                                    </div>
                                    <div class="auth-content my-auto">
                                        <div class="text-center">
                                            <h5 class="mb-0">Reset Password!</h5>
                                            <p class="text-muted mt-2">you need to complete your profile Password continue to Indus Travel Agent Portal.</p>
                                        </div>
                                        @if(session('error'))
                                            <div class="alert alert-danger mt-2">
                                                {{ session('error') }}
                                            </div>
                                        @endif
                                        <form class="mt-4 pt-2" method="POST" action="{{ route('admin.reset.password.submit') }}">
                                            @csrf
                                            <input type="hidden" name="refkey" value="{{ $refkey }}">
                                            <div class="form-floating form-floating-custom mb-4 auth-pass-inputgroup">
                                                <input type="password" class="form-control pe-5" id="password-input" placeholder="Enter Password" name="password" required>
                                                
                                                <button type="button" class="btn btn-link position-absolute h-100 end-0 top-0" id="password-addon">
                                                    <i class="mdi mdi-eye-outline font-size-18 text-muted"></i>
                                                </button>
                                                <label for="input-password">Password</label>
                                                <div class="form-floating-icon">
                                                    <i data-eva="lock-outline"></i>
                                                </div>
                                            </div>

                                            <div class="form-floating form-floating-custom mb-4 auth-pass-inputgroup">
                                                <input type="password" class="form-control pe-5" id="cpassword-input" placeholder="Enter Confirm Password" name="cpassword" required>
                                                
                                                <button type="button" class="btn btn-link position-absolute h-100 end-0 top-0" id="password-addon">
                                                    <i class="mdi mdi-eye-outline font-size-18 text-muted"></i>
                                                </button>
                                                <label for="input-password">Confirm Password</label>
                                                <div class="form-floating-icon">
                                                    <i data-eva="lock-outline"></i>
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <div class="col">
                                                    <div class="form-check font-size-15">
                                                        <input class="form-check-input" type="checkbox" id="remember-check">
                                                        <label class="form-check-label font-size-13" for="remember-check">
                                                            Remember me
                                                        </label>
                                                    </div>  
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Reset Password</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection