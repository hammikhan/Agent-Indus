@extends('admin.layouts.app')
@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        <a href="{{ route('admin.agent.pricing.group') }}">Pricing Group / </a>
        <span>Rule List</span>
    </h4>
@endsection
@section('styles')
<link href="{{ asset('assets/libs/choices/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
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
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="text-muted">Group {{ $pricing_group->name }} Rules</h4>
                            @if(Auth::guard('admin')->user()->type == 'admin')
                            <a href="#" data-bs-toggle="modal" data-bs-target=".add-new" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Add New Rule
                            </a>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table project-list-table table-nowrap align-middle table-borderless">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="ps-4" style="width: 50px;">
                                                S.No
                                            </th>
                                            <th scope="col">Rule</th>
                                            <th scope="col">API</th>
                                            <th scope="col">Airline</th>
                                            <th scope="col">Type</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Status</th>
                                            <th scope="col" style="width: 200px;">Action</th>
                                          </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($pricing_group->pricingEngineTravelAgents as $item)
                                            <tr>
                                                <td scope="col" class="ps-4" style="width: 50px;">
                                                    {{-- {{ $index+1 }} --}} 
                                                    1
                                                </td>
                                                <td>
                                                    <a href="#" class="text-body">{{ $item->rule }}</a>
                                                </td>
                                                <td>{{ $item->api->name }}</td>
                                                <td>{{ AirlineNameByAirlineCode($item->airline) }} ({{ $item->airline }})</td>
                                                <td>{{ $item->type }}</td>
                                                <td>{{ $item->amount }}</td>
                                                <td>
                                                    @if (App\Models\PricingEngineCustomer::$status[$item->status] == "Active")
                                                        <span class="badge badge-soft-success mb-0">
                                                            {{ App\Models\PricingEngineCustomer::$status[$item->status] }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-soft-danger mb-0">
                                                            {{ App\Models\PricingEngineCustomer::$status[$item->status] }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(Auth::guard('admin')->user()->type == 'admin')
                                                    <ul class="list-inline mb-0">
                                                            <li class="list-inline-item">
                                                                <a href="#" onclick="editRule({{$item->id}})" class="px-2 text-primary">
                                                                    <i class="bx bx-edit-alt font-size-18"></i>
                                                                </a>
                                                            </li>
                                                        <li class="list-inline-item">
                                                            <a href="{{ route('admin.agent.pricing.group.engine.rule.delete',$item->id)}}" onclick="return  confirm('Are you sure yout want to delete this Rule')" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" class="px-2 text-danger">
                                                                <i class="bx bx-trash-alt font-size-18"></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@if(Auth::guard('admin')->user()->type == 'admin')
<div class="modal fade edit-rule" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

        </div>
    </div>
</div>
@include('admin.pricing-group.includes.create-rule-modal')
@endif
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/choices/public/assets/scripts/choices.min.js') }}"></script>
<script>
    function editRule(id){
        $('.edit-rule').modal('show');
        
        $.ajax({
            url: "{{ url('admin/pricing-groups/get-rule-details') }}/" + id,
            method: 'GET',
            success: function(data) {
                $('.edit-rule .modal-content').html(data);
                new Choices("#choices-multiple-origin-edit", {
                    removeItemButton: !0
                })
                new Choices("#choices-multiple-destination-edit", {
                    removeItemButton: !0
                })
                showHideEvent();
            },
            error: function(xhr, status, error) {
                console.error('Failed to fetch rule details:', error);
            }
        });
    }
    ///////////////////////////Choice////////////////////
    document.addEventListener("DOMContentLoaded", function() {
        multiChoice();
        showHideEvent();
    });
    function multiChoice(){
        var e = document.querySelectorAll("[data-trigger]");
        for (i = 0; i < e.length; ++i) {
            var a = e[i];
            new Choices(a, {
                placeholderValue: "This is a placeholder set in the config",
                searchPlaceholderValue: "This is a search placeholder"
            })
        }
        new Choices("#choices-multiple-origin", {
            removeItemButton: !0
        })
        new Choices("#choices-multiple-destination", {
            removeItemButton: !0
        })
        
    }
    function showHideEvent(){
        $("#all_origin").change(function() {
            // console.log('origin.....');
            if ($(this).is(":checked")) {
                $("#origins_list").hide();

            } else {
                $("#origins_list").show();
            }
        });
        $("#all_destination").change(function() {
            // console.log('destination.....');
            if ($(this).is(":checked")) {
                $("#destinations_list").hide();

            } else {
                $("#destinations_list").show();
            }
        });
        $('#all_airline, #all_airline2').change(function(){
            // console.log('airline.....');
            if ($('#all_airline').is(':checked') || $('#all_airline2').is(':checked')) {
                $('#airline').attr('disabled', 'disabled');
                $('#airline2').attr('disabled', 'disabled');
            } else {
                $('#airline').removeAttr('disabled');
                $('#airline2').removeAttr('disabled');
            }
        });
    }
</script>
@endsection

