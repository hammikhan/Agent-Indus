@extends('admin.layouts.app')
@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        <a href="{{ route('admin.agency.list') }}">Agencies / </a>
        <span>Travel Agencies Detail</span>
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
                        <div class="card-header">
                            <h4 class="card-title text-muted">Update Agency Details</h4>
                        </div>
                        <div class="card-body">
                            @if(auth('admin')->user()->can('Edit-Travel-Agency'))
                            <form action="{{ route('admin.agency.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="key" value="{{ $agency->id }}">
                            @else
                            <form action="#">
                            @endif
                                <div class="row">
                                    <div class="col-md-6 col-sm-12 d-flex justify-content-between">
                                        <div class="mb-3">
                                            <label class="form-label" for="AddNew-logo">Logo</label>
                                            <input type="file" class="form-control" name="logo" id="AddNew-logo">
                                        </div>
                                        <div class="">
                                            <img src="{{ asset($agency->logo) }}" alt="" class="avatar-lg rounded">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">    
                                    <div class="col-md-3 col-sm-12">
                                        <div class="mb-3">
                                            <label class="form-label" for="AddNew-name">Agency Name</label>
                                            <input type="text" class="form-control" name="name" value="{{ $agency->name}}" placeholder="Enter First Name" required id="AddNew-name">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3 col-sm-12">
                                        <div class="mb-3">
                                            <label class="form-label" for="AddNew-Phone">Phone</label>
                                            <input type="text" class="form-control" name="phone" value="{{ $agency->phone}}" placeholder="Enter Phone" required id="AddNew-Phone">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 col-sm-12">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="group" required>
                                                <option value="">Please select group</option>
                                                @foreach ($groups as $group)
                                                    @if (@$agency->pricingGroup)
                                                        <option value="{{ $group->id }}" {{ ($agency->pricingGroup->id == $group->id) ? 'selected' : '' }}>Group {{ $group->name }}</option>
                                                    @endif
                                                    <option value="{{ $group->id }}">Group {{ $group->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-12">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status" required>
                                                <option value="active" {{ ($agency->status == 'active') ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ ($agency->status == 'inactive') ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="mb-3">
                                            <label class="form-label" for="AddNew-address">Address</label>
                                            <textarea name="address" class="form-control" id="AddNew-address" required rows="5">{{ $agency->address}}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        @if(auth('admin')->user()->can('Edit-Travel-Agency'))
                                            <div class="d-flex align-items-start gap-3 mt-4">
                                                <button type="submit" class="btn btn-primary w-md ms-auto">Update</button>
                                            </div>
                                        @endif
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
@endsection

@section('scripts')

@endsection







