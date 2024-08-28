@extends('admin.layouts.app')
@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        <a href="{{ route('admin.agency.list') }}">Agencies / </a>
        <span>Agents List</span>
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
                            <h4 class="card-title text-muted">{{ $agency->name }} Agents</h4>
                            @if(auth('admin')->user()->can('Create-Travel-Agents'))
                                <div>
                                    <a href="#" data-bs-toggle="modal" data-bs-target=".add-new" class="btn btn-primary">
                                        <i class="bx bx-plus me-1"></i> Add Agent</a>
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <table class="table project-list-table table-nowrap align-middle table-borderless">
                                <thead>
                                    <tr>
                                        <th scope="col" class="ps-4" style="width: 50px;">
                                            S.No
                                        </th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Is Verified</th>
                                        <th scope="col">Status</th>
                                        {{-- <th scope="col">Projects</th> --}}
                                        <th scope="col" style="width: 200px;">Action</th>
                                      </tr>
                                </thead>

                                <tbody>
                                    @foreach ($admin_users as $key => $user)
                                    <tr>
                                        <td scope="col" class="ps-4" style="width: 50px;">
                                            <a href="#">
                                                A000{{ $key+1 }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="#" class="text-body">{{ $user->first_name }} {{ $user->last_name }}</a>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td><span class="badge badge-soft-success mb-0">{{ $user->type }}</span></td>
                                        <td>
                                            @if($user->email_verified_at)
                                                <span class="badge badge-soft-success mb-0">Verified</span>
                                            @else
                                                <span class="badge badge-soft-danger mb-0">Not Verified</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->status == 1)
                                                <span class="badge badge-soft-success mb-0">Active</span>
                                            @else
                                                <span class="badge badge-soft-danger mb-0">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <ul class="list-inline mb-0">
                                                @if(auth('admin')->user()->can('Edit-Travel-Agents'))
                                                    <li class="list-inline-item">
                                                        <a href="{{ url('admin/agency-agents-edit',['agency'=>$agency->id,'agent' => $user->id])}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" class="px-2 text-primary">
                                                            <i class="bx bx-edit-alt font-size-18"></i>
                                                        </a>
                                                    </li>
                                                @endif
                                                @if(auth('admin')->user()->can('Delete-Travel-Agents'))
                                                    <li class="list-inline-item">
                                                        <a href="{{ url('admin/agency-agents-delete',['agency'=>$agency->id,'agent' => $user->id])}}" onclick="return confirm('Are you sure you want to delete agent')" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" class="px-2 text-danger">
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
    </div>
</div>

<div class="modal fade add-new" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myExtraLargeModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ url('/admin/agency-store-agents') }}" method="post">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="agency_id" value="{{ $agency->id }}">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="AddNew-first_name">First Name</label>
                                <input type="text" class="form-control" name="first_name" placeholder="Enter First Name" required id="AddNew-first_name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="AddNew-last_name">Last Name</label>
                                <input type="text" class="form-control" name="last_name" placeholder="Enter Last Name" required id="AddNew-last_name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Position</label>
                                <select class="form-select" name="role_name" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}">{{ $role }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="AddNew-Email">Email</label>
                                <input type="text" class="form-control" name="email" placeholder="Enter Email" id="AddNew-Email" required autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="AddNew-Phone">Phone</label>
                                <input type="text" class="form-control" name="phone" placeholder="Enter Phone" id="AddNew-Phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="AddNew-Password">Password</label>
                                <input type="text" class="form-control" name="password" placeholder="Enter Password" value="123456" id="AddNew-Password" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="AddNew-password_confirmation">Password Confirmation</label>
                                <input type="text" class="form-control" name="password_confirmation" value="123456" placeholder="Enter password_confirmation" id="AddNew-password_confirmation" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-danger me-1" data-bs-dismiss="modal"><i class="bx bx-x me-1"></i> Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bx bx-check me-1"></i> Save Agent
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
@endsection

@section('scripts')

@endsection







