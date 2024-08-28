@extends('admin.layouts.app')

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
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="text-muted">Agent Pricing Rule List</h4>
                            <a href="{{ route('admin.pricingEngine.create') }}" class="btn btn-primary">Create New</a>
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
                                            <th scope="col">Type</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Status</th>
                                            <th scope="col" style="width: 200px;">Action</th>
                                          </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($rulesList as $item)
                                            <tr>
                                                <td scope="col" class="ps-4" style="width: 50px;">
                                                    {{-- {{ $index+1 }} --}} 
                                                    1
                                                </td>
                                                <td>
                                                    <a href="#" class="text-body">{{ $item->rule }}</a>
                                                </td>
                                                <td>{{ $item->api->name }}</td>
                                                <td>{{ $item->data['type'] }}</td>
                                                <td>{{ $item->data['amount'] }}</td>
                                                <td>
                                                    @if (App\Models\PricingEngineTravelAgent::$status[$item->status] == "Active")
                                                        <span class="badge badge-soft-success mb-0">
                                                            {{ App\Models\PricingEngineTravelAgent::$status[$item->status] }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-soft-danger mb-0">
                                                            {{ App\Models\PricingEngineTravelAgent::$status[$item->status] }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <ul class="list-inline mb-0">
                                                        {{-- @if(auth('admin')->user()->can('Read-Users')) --}}
                                                            <li class="list-inline-item">
                                                                <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" class="px-2 text-primary">
                                                                    <i class="bx bx-show-alt font-size-18"></i>
                                                                </a>
                                                            </li>
                                                        {{-- @endif --}}
                                                        {{-- @if(auth('admin')->user()->can('Delete-Users')) --}}
                                                        <li class="list-inline-item">
                                                            <a href="{{ route('admin.pricingEngine.delete',[$item->id])}}" onclick="return  confirm('Are you sure yout want to delete this Rule')" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" class="px-2 text-danger">
                                                                <i class="bx bx-trash-alt font-size-18"></i>
                                                            </a>
                                                        </li>
                                                        {{-- @endif --}}
                                                    </ul>
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
@endsection

@section('scripts')

@endsection

