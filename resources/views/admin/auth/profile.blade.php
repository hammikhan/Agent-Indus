@extends('admin.layouts.app')
@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        <span>Profie Setting</span>
    </h4>
@endsection
@section('styles')
<style>
    .nav-tabs .nav-link{
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }
    .edit-user-icon {
        position: absolute;
        right: 35%;
        top: 85%;
        border-radius: 3rem;
        background: #fff;
        padding: 5px;
        cursor: pointer;
        box-shadow: 0 4px 3px #e4e8f0;
    }
    .error-input{
        border-color: #f56e6e;
        padding-right: calc(1.5em + 0.94rem);
        background-image: url(data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23f56e6e'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23f56e6e' stroke='none'/%3e%3c/svg%3e);
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.235rem) center;
        background-size: calc(0.75em + 0.47rem) calc(0.75em + 0.47rem);
    }
    .error-input-text{
        color: #f56e6e;
    }
</style>
@endsection

@section('content')
<div id="layout-wrapper">
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="user-sidebar">
                            <div class="card">
                                <div class="card-body p-0">
                                    <div class="user-profile-img">
                                        <img src="{{ asset('assets/images/pattern-bg.jpg') }}" class="profile-img profile-foreground-img rounded-top" style="height: 120px;" alt="">
                                        <div class="overlay-content rounded-top" style="background: rgb(2 109 66 / 65%)"></div>
                                    </div>
                                    <!-- end user-profile-img -->
                                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist" style="background: rgb(179 179 179 / 39%); border-bottom:2px solid #e1e1e1;">
                                        <li class="nav-item"></li>
                                        <li class="nav-item"></li>
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#profile" role="tab">
                                                <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                                <span class="d-none d-sm-block">Profile</span> 
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">
                                                <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                                <span class="d-none d-sm-block">Change Password</span>   
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#activity" role="tab">
                                                <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                                <span class="d-none d-sm-block">Activity</span> 
                                            </a>
                                        </li>
                                        {{-- <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#settings1" role="tab">
                                                <span class="d-block d-sm-none"><i class="fas fa-cog"></i></span>
                                                <span class="d-none d-sm-block">XYZ</span>    
                                            </a>
                                        </li> --}}
                                    </ul>
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="position-relative text-center" style="margin-top: -6rem;">
                                                <i class="bx bx-camera edit-user-icon" data-bs-toggle="modal" data-bs-target=".profile-picture-modal"></i>
                                                @if(adminUser()->profile_image =='')
                                                    <img src="{{ asset('assets/admin-images/user2.png') }}" alt="" class="rounded-circle img-thumbnail" style="height: 10rem; width: 10rem;">
                                                @else
                                                    <img src="{{ asset(adminUser()->profile_image) }}" alt="" class="rounded-circle img-thumbnail" style="height: 10rem; width: 10rem;">
                                                @endif
                                            </div>
                                            <div class="mt-3 mt-xl-3">
                                                <h4 class="font-size-20 mb-1 text-center">{{ adminUser()->first_name .' '. adminUser()->last_name }}</h4>
                                                @if(Auth::guard('admin')->user()->type != 'admin' && Auth::guard('admin')->user()->type != 'Admin User')
                                                    <div class="text-center">
                                                        <div class="text-muted">
                                                            Status
                                                            <span class="badge bg-success font-size-14 me-1">{{ App\Models\Admin::$status[adminUser()->status] }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="p-4 rounded mt-2 ms-3" style="border: 2px solid #eff0f2">
                                                        <h6 class="d-flex justify-content-between text-muted">
                                                            <span class="me-2">
                                                                Total Credit
                                                            </span>
                                                            <span>PKR {{ number_format((int)$totalCredit) }}</span>
                                                        </h6>
                                                        <h6 class="d-flex justify-content-between text-muted">
                                                            <span class="me-2">
                                                                Used Credit
                                                            </span>
                                                            <span>PKR {{ number_format((int)$usedCredit) }}</span>
                                                        </h6>
                                                        <hr>
                                                        <h5 class="d-flex justify-content-between text-muted">
                                                            <span class="me-2">
                                                                Remaining:
                                                            </span>
                                                            <span>
                                                                @php
                                                                    $remaining = $totalCredit - $usedCredit;
                                                                @endphp
                                                                PKR {{ number_format((int)$remaining) }}
                                                            </span>
                                                        </h5>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-lg-8 px-4 pt-5 pb-5 border-start">
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="profile" role="tabpanel">
                                                    @include('admin.auth.includes.update-profile-form')
                                                </div>
                                                <div class="tab-pane" id="changePassword" role="tabpanel">
                                                    @include('admin.auth.includes.change-password-form')
                                                </div>
                                                <div class="tab-pane" id="activity" role="tabpanel">
                                                    <ul>
                                                        <li>Last login on 0n 12:00 18-09-2023</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade profile-picture-modal" tabindex="-1" aria-labelledby="success-btnLabel" aria-hidden="true" data-bs-scroll="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
               <div class="text-center">
                    <form action="{{ route('admin.users.profile.image.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="position-relative text-center">
                            @if(adminUser()->profile_image =='')
                                <img id="imgPreview" src="{{ asset('assets/admin-images/user2.png') }}" alt="" class="rounded-circle img-thumbnail border-2" style="height: 20rem; width: 20rem;">
                            @else
                                <img id="imgPreview" src="{{ asset(adminUser()->profile_image) }}" alt="" class="rounded-circle img-thumbnail border-2" style="height: 20rem; width: 20rem;">
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-start">
                                <div class="mb-3">
                                    <input type="file" id="photo" name="image">
                                </div>
                            </div>
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary w-md">Save</button>
                            </div>
                        </div>
                    </form>
               </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        $(document).ready(() => {
            // Image preview in model to change profile image
            $("#photo").change(function () {
                const file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function (event) {
                        $("#imgPreview")
                            .attr("src", event.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });
            //  Check old password
            $('#old_password').on('blur', function() {
                var oldPassword = $(this).val();

                $.ajax({
                    url: "{{ route('admin.users.check.oldPassword')}}", // Update with your actual route
                    method: 'GET',
                    data: {
                        old_password: oldPassword,
                    },
                    success: function(response) {
                        if(response.status == 200){
                            $('#old_password').removeClass('error-input');
                            $('.oldPawwordErrorText').text('');
                        }else{
                            $('#old_password').addClass('error-input');
                            $('.oldPawwordErrorText').text(response.message);
                        }
                    },
                    error: function(error) {
                        // Handle any errors
                        console.error(error);
                    }
                });
            });
        });
    </script>
@endsection

