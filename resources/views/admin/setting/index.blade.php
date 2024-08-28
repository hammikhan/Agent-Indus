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
<link href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
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
                    <div class="tab-content p-4">
                        <div class="tab-pane active" id="providers" role="tabpanel">
                            <div class="col-lg-12">
                                <div class="">
                                    <div class="table-responsive">
                                        <table class="table project-list-table table-nowrap align-middle table-borderless">
                                            <thead>
                                                <tr>
                                                    <th scope="col" style="width: 100px" class="ps-4">#</th>
                                                    <th scope="col">Name</th>
                                                    {{-- <th scope="col">Balance</th> --}}
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($providers as $provider)
                                                    <tr>
                                                        <td class="ps-4">
                                                            <a href="{{ url('admin/setting/provider',$provider->identifier)}}">
                                                                <img src="{{ asset('assets/providers/'.$provider->identifier.'.png')}}" alt="" class="avatar-sm">
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <h5 class="text-truncate font-size-14">
                                                                <a href="{{ url('admin/setting/provider',$provider->identifier)}}" class="text-dark">{{ $provider->name }}</a>
                                                            </h5>
                                                        </td>
                                                        {{-- <td>
                                                            <ul class="list-inline mb-0">
                                                                <li class="list-inline-item me-3">
                                                                    <i class="fas fa-dollar-sign"></i> 0
                                                                </li>
                                                            </ul>
                                                        </td> --}}
                                                        <td class="pe-5">
                                                            <span class="form-switch">
                                                                <input class="form-check-input" type="checkbox" onclick="changeStatus({{ $provider->id }})" {{ ($provider->status == 1) ? 'checked' : ''}}>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="{{ url('admin/setting/provider',$provider->identifier)}}">
                                                                <i class="bx bx-show-alt font-size-18"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane" id="pgw_tab" role="tabpanel">
                            
                        </div>
                        

                        <div class="tab-pane" id="email_tab" role="tabpanel">
                            
                        </div>

                        <div class="tab-pane" id="post" role="tabpanel">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')
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
    function changeStatus(id) {
        $.ajax({
            type: 'POST',
            url: "{{ route('admin.setting.update.status')}}",
            data: {
                id
            },
            success: function(data) {
                if (data.status == 'success') {
                    showSweetAlert(data.message,"success","Okay, got it!","btn btn-primary");
                } else {
                    showSweetAlert(data.message,"warning","Okay, got it!","btn btn-danger");
                }
            }
        });
    }
</script>
@endsection
