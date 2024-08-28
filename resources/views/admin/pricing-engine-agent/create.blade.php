@extends('admin.layouts.app')

@section('styles')
<link href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
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
        /* *********Multy select style*********** */
        .choices__inner {
            padding: 0.25rem 2.5rem 0.25rem 0.5rem;
            background-color: #fff;
            vertical-align: middle;
            border-radius: 0.75rem;
            border: 1px solid #e2e5e8;
            min-height: 38px;
        }
        .choices__input {
            background-color: #fff;
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
<div class="main-content">

    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="text-muted">Create Agent Pricing Rule</h4>
                        </div>
                        <div class="card-body">
                            

                            <form action="{{ route('admin.pricingEngine.store') }}" method="POST" class="needs-validation" novalidate>
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="formrow-inputRule" class="form-label text-muted">Rule Purpose</label>
                                            <select id="formrow-inputRule" name="rulePurpose" class="form-select">
                                                @foreach(\App\Models\PricingEngineTravelAgent::$rulePurpose as $key => $val)
                                                    <option value="{{ $key }}">{{ $val }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="formrow-inputApi" class="form-label text-muted">API</label>
                                            <select id="formrow-inputApi" name="api_id" required class="form-select">
                                                <option value="">Select API</option>
                                                @foreach ($apis as $val)
                                                    <option value="{{$val->id}}">{{$val->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="formrow-inputType" class="form-label text-muted">Type</label>
                                            <select id="formrow-inputType" name="type" required class="form-select">
                                                <option value="Fixed">Fixed</option>
                                                <option value="Percentage">Percentage</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="formrow-firstname-input" class="form-label text-muted">Amount</label>
                                            <input type="number" class="form-control" required name="amount" id="formrow-firstname-input">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="formrow-inputStatus" class="form-label text-muted">Status</label>
                                            <select id="formrow-inputStatus" name="status" class="form-select">
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="formrow-desription-input" class="form-label text-muted">Description</label>
                                            <input type="text" class="form-control" name="description" id="formrow-desription-input">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="all_origin" id="all_origin" checked="">
                                            <label class="form-check-label" for="all_origin">All Orogin</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="all_destination" id="all_destination" checked="">
                                            <label class="form-check-label" for="all_destination">All Destinations</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="origins_list" style="display: none;">
                                        <div class="mb-3">
                                            <label for="choices-multiple-origin" class="form-label font-size-13 text-muted">Origin</label>
                                            <select class="form-control" name="origin[]" id="choices-multiple-origin"
                                                placeholder="Select Origin" multiple>
                                                @foreach ($airports as $airport)
                                                    <option value="{{ $airport->code }}">{{ $airport->city }} ({{ $airport->code }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="destinations_list" style="display: none;">
                                        <div class="mb-3">
                                            <label for="choices-multiple-destination" class="form-label font-size-13 text-muted">Destination</label>
                                            <select class="form-control" name="destination[]" id="choices-multiple-destination"
                                                placeholder="Select Destination" multiple>
                                                <option value="ISB">Islamabad</option>
                                                <option value="DXB">Dubai</option>
                                                @foreach ($airports as $airport)
                                                    <option value="{{ $airport->code }}">{{ $airport->city }} ({{ $airport->code }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-start gap-3 mt-4">
                                    <button type="submit" class="btn btn-primary w-md ms-auto">Submit</button>
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
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@2.10.2/dist/umd/popper.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var e = document.querySelectorAll("[data-trigger]");
        new Choices("#choices-multiple-origin", {
            removeItemButton: !0
        })
        new Choices("#choices-multiple-destination", {
            removeItemButton: !0
        })
    });
    $(document).ready(function() {
        $("#all_origin").change(function() {
            if ($(this).is(":checked")) {
                $("#origins_list").hide();

            } else {
                $("#origins_list").show();
            }
        });
        $("#all_destination").change(function() {
            if ($(this).is(":checked")) {
                $("#destinations_list").hide();

            } else {
                $("#destinations_list").show();
            }
        });
    });

</script>

    
@endsection

