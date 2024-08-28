@extends('admin.layouts.app')

@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        <span>Settings</span>
    </h4>
@endsection
@section('styles')
<link href="{{ asset('assets/libs/choices/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
<style>
    .form-check-input:checked {
        background-color: #15b715 !important;
    }
</style>
    
@endsection
@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="card">
                    <!-- Nav tabs -->
                    @include('admin.setting.includes.setting-top-nav')
                    <!-- Tab content -->
                    <ul class="nav nav-tabs mt-2">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#air_discount" role="tab">
                                <span class="d-none d-sm-block">Airline Discount</span> 
                            </a>
                        </li>
                        @if ($provider->type == 'GDS')
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#exclude_air" role="tab">
                                    <span class="d-none d-sm-block">Exclude Airline</span> 
                                </a>
                            </li>
                        @endif
                    </ul>
                    <div class="tab-content p-4">
                        <div class="tab-pane active" id="air_discount" role="tabpanel">
                            <div class="row">
                                <div class="col-xl-6 col-sm-6">
                                    <h5 class="font-size-16 me-3 mb-0">{{ $provider->name }} Discount</h5>
                                </div>
                                <div class="col-xl-6 col-sm-6">
                                    <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                                        <a href="#" data-bs-toggle="modal" data-bs-target=".add-new-discount" class="btn btn-primary">
                                            Add Airline %
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                @foreach ($provider->airlineDiscount as $discount)
                                <div class="col-xl-4 col-sm-6">
                                    <div class="card shadow-none border">
                                        <div class="card-body p-3">
                                            <div class="">
                                                <div class="dropdown float-end">
                                                    <a href="javascript:void(0)" class="text-info" data-bs-toggle="modal" data-bs-target=".edit-discount-{{ $discount->id }}" class="btn btn-primary">
                                                        <i class="bx bx-pencil font-size-20 bg-light rounded p-2"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" onclick="deleteDiscount({{$discount->id}})" class="px-2 text-danger">
                                                        <i class="bx bx-trash-alt font-size-20 bg-light rounded p-2"></i>
                                                    </a>
                                                    
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar align-self-center me-3">
                                                        <div class="avatar-title rounded bg-soft-primary text-primary font-size-24">
                                                            <img src="{{ asset('assets/airlines/'.$discount->airline.'.png') }}" class="avatar-lg" alt="" style="height: 45px; width: 45px;">
                                                        </div>
                                                    </div>

                                                    <div class="flex-1">
                                                        <h5 class="font-size-15 mb-1">{{ AirlineNameByAirlineCode($discount->airline) }} ({{ $discount->airline }})</h5>
                                                        <span class="form-switch">
                                                            <input class="form-check-input activeInactive" type="checkbox" id="{{ $discount->airline }}" {{ ($discount->status == 1) ? 'checked' : ''}}>
                                                        </span>
                                                        &nbsp;&nbsp;&nbsp;
                                                        <span>Discount: {{ $discount->discount }}%</span>
                                                    </div>
                                                </div>
                                                <div class="mt-3 pt-1">
                                                    <div class="d-flex justify-content-between">
                                                        <p class="text-muted font-size-13 mb-1">
                                                            <span><strong>From</strong></span><br>
                                                            {{ ($discount->departure_codes != '') ? $discount->departure_codes : "Anywhere" }}
                                                        </p>
                                                        <p class="text-muted font-size-13 mb-1">
                                                            <span><strong>To</strong></span><br>
                                                            Anywhere
                                                        </p>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- *******************Edit Modal***************************** --}}
                                    <div class="modal fade edit-discount-{{ $discount->id }}" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" style="position: absolute">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myExtraLargeModalLabel">Edit Descount %</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ url('/admin/setting/update-airline-discount') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $discount->id }}">
                                                        <input type="hidden" name="source" value="{{ $provider->name }}">
                                                        <div class="row">
                                                            <div class="col-lg-4 col-md-6">
                                                                <div class="mb-3">
                                                                    <label for="choices-single-groups" class="form-label font-size-13 text-muted">Airline</label>
                                                                    <select class="form-select" name="airline" required>
                                                                        <option value="">Select Airline</option>
                                                                        @foreach (AllAirlines() as $air)
                                                                            <option value="{{ $air['code'] }}" {{ ($discount->airline == $air['code']) ? 'Selected' : ''}}>{{ $air['name'] }} ({{ $air['code'] }})</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4 col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label" for="AddNew-discount">Discount</label>
                                                                    <input type="number" class="form-control" value="{{ $discount->discount }}" name="discount" id="AddNew-discount" required>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="col-lg-6 col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label" for="AddNew-From">From</label>
                                                                    <input type="text" class="form-control" name="from" value="{{ $discount->departure_codes }}" required placeholder="Enter origin comma seperated" id="AddNew-From" autocomplete="off">
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label" for="AddNew-Phone">To</label>
                                                                    <input type="text" class="form-control" name="to" placeholder="Enter Destination comma seperated" id="AddNew-Phone">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2">
                                                            <div class="col-12 text-end">
                                                                <button type="button" class="btn btn-danger me-1" data-bs-dismiss="modal">
                                                                    <i class="bx bx-x me-1"></i> Cancel
                                                                </button>
                                                                <button type="submit" class="btn btn-success" id="btn_update_discount">
                                                                    <i class="bx bx-check me-1"></i> Updated
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @if ($provider->type == 'GDS')
                            @php
                                $exclude_airlines = json_decode($provider->exclude_airlines,true);
                            @endphp
                            <div class="tab-pane" id="exclude_air" role="tabpanel">
                                <form class="row" action="{{ route('admin.provider.exclude.airline') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="provider" value="{{ $provider->id }}">
                                    <div class="col-md-10">
                                        <div class="mb-3">
                                            <select class="form-control" name="exclude_airlines[]" id="choices-multiple-remove-button" placeholder="This is a placeholder" multiple>
                                                @foreach (AllAirlines() as $air)
                                                    <option value="{{ $air['code'] }}" @if(@$exclude_airlines) {{ (in_array($air['code'],$exclude_airlines)) ? 'selected' : ''}} @endif>{{ $air['code'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-info">Save</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- *******************Add Modal***************************** --}}
<div class="modal fade add-new-discount" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myExtraLargeModalLabel">Add Descount %</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ url('/admin/setting/store-airline-discount') }}" method="POST">
                    @csrf
                    <input type="hidden" name="source" value="{{ $provider->identifier }}">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label font-size-13 text-muted">Airline</label>
                                <select class="form-select" name="airline" required>
                                    <option value="">Select Airline</option>
                                    @foreach (AllAirlines() as $air)
                                        <option value="{{ $air['code'] }}">{{ $air['name'] }} ({{ $air['code'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="AddNew-discount">Discount</label>
                                <input type="number" class="form-control" name="discount" id="AddNew-discount">
                            </div>
                        </div>
                        
                        <div class="col-lg-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="AddNew-From">From</label>
                                <input type="text" class="form-control" name="from" placeholder="Enter origin comma seperated" id="AddNew-From" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="AddNew-Phone">To</label>
                                <input type="text" class="form-control" name="to" placeholder="Enter Destination comma seperated" id="AddNew-Phone">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-danger me-1" data-bs-dismiss="modal"><i class="bx bx-x me-1"></i> Cancel</button>
                            <button type="submit" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#success-btn" id="btn-save-event"><i class="bx bx-check me-1"></i> Confirm</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('assets/libs/choices/public/assets/scripts/choices.min.js') }}"></script>
<script>
    function deleteDiscount(id) {
        showSweetAlertDelete(
            "Are you sure, You want to delete this",
            '',
            'warning',
            'yes Delete',
            'Cancell',
            function() {
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.setting.delete.discount')}}",
                    data: {
                        id
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            showSweetAlert(data.message,"success","Okay, got it!","btn btn-primary");
                        } else {
                            showSweetAlert(data.message,"warning","Okay, got it!","btn btn-danger");
                        }
                    },
                    complete: function () {
                        location.reload();
                    }
                });
            }
        );
    }
    ///////////////////////////Choice////////////////////
    document.addEventListener("DOMContentLoaded", function() {
        var e = document.querySelectorAll("[data-trigger]");
        for (i = 0; i < e.length; ++i) {
            var a = e[i];
            new Choices(a, {
                placeholderValue: "This is a placeholder set in the config",
                searchPlaceholderValue: "This is a search placeholder"
            })
        }
        new Choices("#choices-multiple-remove-button", {
            removeItemButton: !0
        })
    });
</script>
@endsection
