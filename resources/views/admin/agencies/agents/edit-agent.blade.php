@extends('admin.layouts.app')
@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        <a href="{{ route('admin.agency.list') }}">Agencies / </a>
        <a href="{{ route('admin.agency.agents',[$agency->id]) }}">Agents List / </a>
        <span>Edit Agents</span>
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
                    @include('admin.agencies.includes.agency-top')
                </div>
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between d-flex ">
                            <h4 class="card-title text-muted">Update {{ $agent->first_name }} {{ $agent->last_name }} detail</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 offset-sm-3">
                                    <form action="{{ url('/admin/agency-agents-update') }}" method="post">
                                        @csrf
                                        <div class="row">
                                            <input type="hidden" name="agency_id" value="{{ $agency->id }}">
                                            <input type="hidden" name="agent_id" value="{{ $agent->id }}">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="AddNew-first_name">First Name</label>
                                                    <input type="text" class="form-control" name="first_name" value="{{ $agent->first_name }}" placeholder="Enter First Name" required id="AddNew-first_name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="AddNew-last_name">Last Name</label>
                                                    <input type="text" class="form-control" name="last_name" value="{{ $agent->last_name }}" placeholder="Enter Last Name" required id="AddNew-last_name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Position</label>
                                                    <select class="form-select" name="role_name" required>
                                                        <option value="">Select Role</option>
                                                        @foreach($roles as $role)
                                                            <option value="{{ $role }}" {{ ($agent->roles->pluck('name')[0] == $role) ? 'selected' : ' '}}>{{ $role }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="AddNew-Email">Email</label>
                                                    <input type="text" class="form-control" readonly value="{{ $agent->email}}" placeholder="Enter Email" id="AddNew-Email" required autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Position</label>
                                                    <select class="form-select" name="status" required>
                                                        <option value="1" {{ ($agent->status == 1) ? 'selected' : ''}}>Active</option>
                                                        <option value="0" {{ ($agent->status == 0) ? 'selected' : ''}}>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" for="AddNew-Password">Password</label>
                                                    <input type="text" class="form-control" name="password" placeholder="Enter Password" id="AddNew-Password">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12 text-end">
                                                <a type="button" class="btn btn-danger me-1">
                                                    <i class="bx bx-x me-1"></i> Cancel
                                                </a>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="bx bx-check me-1"></i> Update
                                                </button>
                                            </div>
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

@section('scripts')

@endsection







