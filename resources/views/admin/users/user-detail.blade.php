@extends('admin.layouts.app')
@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        <a href="{{ route('admin.users') }}">Admin Users List / </a>
        <span>Users Detail</span>
    </h4>
@endsection
@section('styles')
    <style>
        .nav-tabs .nav-link {
            margin-bottom: -1px;
            background: 0 0;
            border: 1px solid transparent;
            border-top-left-radius: 0.3rem;
            border-top-right-radius: 0.3rem;
        }
        .nav-tabs-custom {
            border-bottom: none;
        }
    </style>
@endsection

@section('content')
<div class="main-content">

    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-xl-12">
                    @include('admin.users.includes.user-top')
                </div>
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title text-muted">Edit User Details</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.user.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $user->id }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="formrow-firstname-input" class="form-label text-muted">First Name</label>
                                            <input type="text" class="form-control" name="first_name" value="{{ $user->first_name}}" id="formrow-firstname-input">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="formrow-lastname-input" class="form-label text-muted">Last Name</label>
                                            <input type="text" class="form-control" name="last_name" value="{{ $user->last_name}}" id="formrow-lastname-input">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="formrow-email-input" class="form-label text-muted">Email</label>
                                            <input type="email" class="form-control" value="{{ $user->email}}" id="formrow-email-input" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="formrow-inputRole" class="form-label text-muted">Role</label>
                                            <select id="formrow-inputRole" class="form-select" name="role">
                                                @foreach($roles as $role)
                                                    <option value="{{ $role }}" {{ ($user->roles->pluck('name')[0] == $role) ? 'selected' : '' }}>{{ $role }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label for="formrow-inputState" class="form-label text-muted">Status</label>
                                            <select id="formrow-inputState" class="form-select" name="status">
                                                <option value="active">active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    
                                </div>
                                @if(auth('admin')->user()->can('Update-Users'))
                                <div class="d-flex align-items-start gap-3 mt-4">
                                    <button type="submit" class="btn btn-primary w-md ms-auto">Submit</button>
                                </div>
                                @endif
                            </form>
                        </div>
                        
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')

@endsection







