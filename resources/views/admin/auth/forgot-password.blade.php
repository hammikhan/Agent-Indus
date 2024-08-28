@extends('admin.auth.layouts.auth-app')


@section('content')

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
                        <h5 class="mb-0">Welcome Back !</h5>
                        <p class="text-muted mt-2">Sign in to continue to {{ config('constants.SITE_TITLE') }}.</p>
                    </div>
                    @if(session('error'))
                        <div class="alert alert-danger mt-2">
                            {{ session('error') }}
                        </div>
                    @endif
                    <form class="mt-4 pt-2" method="POST" action="{{ route('admin.forgot.password.submit') }}">
                        @csrf
                        <div class="form-floating form-floating-custom mb-4">
                            <input type="email" class="form-control" id="input-email" placeholder="Enter Email" name="email" value="{{ old('email') }}" required >
                            <label for="input-email">Email</label>
                            <div class="form-floating-icon">
                                <i data-eva="email-outline"></i>
                            </div>
                        </div>


                        
                        <div class="mb-3">
                            <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
                            
            
@endsection