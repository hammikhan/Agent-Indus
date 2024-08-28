@extends('admin.layouts.app')
@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        {{-- <a href="{{ route('admin.bookings') }}">Booking List / </a> --}}
        <span>Pricing Groups</span>
    </h4>
@endsection
@section('styles')

@endsection

@section('content')
<div id="layout-wrapper">
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="card-title">Group List <span class="text-muted fw-normal ms-2"></span></h5>
                        </div>
                    </div>
                    @if(Auth::guard('admin')->user()->type == 'admin')
                    <div class="col-md-6">
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                            <div>
                                <a href="#" data-bs-toggle="modal" data-bs-target=".add-new" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Add New Group
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="row">
                    @if (@$pricingGroups)
                        @foreach ($pricingGroups as $group)
                            <div class="col-lg-3">
                                <div class="card">
                                    <a href="{{ url('admin/pricing-groups/pricing-rules',$group->id) }}" class="d-inline-block">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar">
                                                    <span class="avatar-title rounded-circle bg-success text-white font-size-16">
                                                        {{ $group->name }}
                                                    </span>
                                                </div>
                                                <h4 class="font-size-20 text-truncate ms-2 mb-0">
                                                    Pricing Group {{ $group->name }}
                                                </h4>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@if(Auth::guard('admin')->user()->type == 'admin')
@include('admin.pricing-group.includes.create-rule-modal')
@endif
@endsection

@section('scripts')

@endsection

