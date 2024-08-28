@extends('admin.layouts.app')

@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        {{-- <a href="#">Search Flight</a> --}}
        <span>Agencies List</span>
    </h4>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="card-title">Travel Agencies List <span class="text-muted fw-normal ms-2">({{ count($agencies)}})</span></h5>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                            
                            @if(auth('admin')->user()->can('Create-Travel-Agency'))
                                <div>
                                    <a href="#" data-bs-toggle="modal" data-bs-target=".add-new" class="btn btn-primary">
                                        <i class="bx bx-plus me-1"></i> Add New
                                    </a>
                                </div>
                            @endif
                            
                        </div>

                    </div>
                </div>


                <div class="row">
                    <div class="col-lg-12">
                        <div class="">
                            <div class="table-responsive">
                                <table class="table project-list-table table-nowrap align-middle table-borderless">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="ps-4" style="width: 50px;">S.No</th>
                                            <th scope="col">Logo</th>
                                            <th scope="col">Name</th>
                                            {{-- <th scope="col">Address</th> --}}
                                            <th scope="col">Phone</th>
                                            <th scope="col">Credit Limit Used</th>
                                            <th scope="col">Credit Limit Remaining</th>
                                            <th scope="col">Total Credit Limit</th>
                                            <th scope="col">Status</th>
                                            <th scope="col" style="width: 200px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($agencies as $key => $agency)
                                        <tr>
                                            <td scope="col" class="ps-4" style="width: 50px;">
                                                <a href="#">A000{{ $key+1 }}</a>
                                            </td>
                                            <td>
                                                @if($agency->logo =='')
                                                <img src="{{ asset('assets/admin-images/user2.png') }}" alt="" class="avatar-sm rounded-circle me-2">
                                                @else
                                                <img src="{{ asset($agency->logo) }}" alt="" class="avatar-sm rounded-circle me-2">
                                                @endif
                                                
                                            </td>
                                            <td><a href="#" class="text-body">{{ $agency->name }}</a></td>
                                            {{-- <td><span class="badge badge-soft-success mb-0">{{ $agency->address }}</span></td> --}}
                                            <td>{{ $agency->phone }}</td>
                                            <td>
                                                <span class="badge badge-soft-success mb-0 fw-bold">
                                                    PKR 
                                                    {{ agencyCreditLimit($agency->id)['usedCredit'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-soft-info mb-0 fw-bold">
                                                    PKR 
                                                    {{ agencyCreditLimit($agency->id)['remaining'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-soft-primary mb-0 fw-bold">
                                                    PKR 
                                                    {{ agencyCreditLimit($agency->id)['totalCredit'] }}
                                                </span>
                                            </td>
                                            <td>{{ $agency->status }}</td>
                                            <td>
                                                <ul class="list-inline mb-0">
                                                    @if(auth('admin')->user()->can('List-Travel-Agents') || auth('admin')->user()->can('Read-Travel-Agency-User') || auth('admin')->user()->can('Read-Booking'))
                                                        <li class="list-inline-item">
                                                            <a href="{{ url('admin/agency-agents',$agency->id)}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" class="px-2 text-primary">
                                                                <i class="bx bx-show-alt font-size-18"></i>
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if(auth('admin')->user()->can('Delete-Travel-Agency'))
                                                        <li class="list-inline-item">
                                                            <a href="{{ url('admin/delete-agency',$agency->id)}}" onclick="return confirm('Are you sure you want to delete Agency')" class="px-2 text-danger">
                                                                <i class="bx bx-trash-alt font-size-18"></i>
                                                            </a>
                                                        </li>
                                                    @endif
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


            <!--  Extra Large modal example -->
            <div class="modal fade add-new" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="myExtraLargeModalLabel">Create Agency</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ url('/admin/agency-store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="AddNew-logo">Logo</label>
                                            <input type="file" class="form-control" name="logo" id="AddNew-logo" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3 offset-3">
                                        <div class="mb-3" x-data="{ creditLimit: '' }">
                                            <label class="form-label" for="AddNew-cridit-limit">Critid Limit</label>
                                            <input class="form-control" name="credit_limit" required x-mask="999999" min="5" max="6" placeholder="999999" id="customer_phone" x-model="creditLimit" @keyup="$event.target.value = creditLimit">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="AddNew-name">Agency Name</label>
                                            <input type="text" class="form-control" name="name" placeholder="Enter First Name" required id="AddNew-name">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="AddNew-Phone">Phone</label>
                                            <input type="text" class="form-control" name="phone" placeholder="Enter Phone" required id="AddNew-Phone">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="group" required>
                                                <option value="">Please select group</option>
                                                @foreach ($groups as $group)
                                                    <option value="{{ $group->id }}">Group {{ $group->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status" required>
                                                <option value="active" selected>Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label" for="AddNew-address">Address</label>
                                            <textarea name="address" class="form-control" id="AddNew-address" required rows="5"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-danger me-1" data-bs-dismiss="modal">
                                            <i class="bx bx-x me-1"></i> Cancel
                                        </button>
                                        <button type="submit" class="btn btn-success" id="btn-save-event">
                                            <i class="bx bx-check me-1"></i> Save
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
@endsection

@section('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
