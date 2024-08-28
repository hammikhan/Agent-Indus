@extends('admin.layouts.app')

@section('styles')

@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="card-title">Travel Agents List <span class="text-muted fw-normal ms-2">({{ count($admin_users)}})</span></h5>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                            
                            @if(auth('admin')->user()->can('Create-Users'))
                                <div>
                                    <a href="#" data-bs-toggle="modal" data-bs-target=".add-new" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Add New</a>
                                </div>
                            @endif
                            
                        </div>

                    </div>
                </div>

                <!-- end row -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="">
                            <div class="table-responsive">
                                <table class="table project-list-table table-nowrap align-middle table-borderless">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="ps-4" style="width: 50px;">
                                                S.No
                                            </th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Position</th>
                                            <th scope="col">Email</th>
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
                                                @if($user->profile_image =='')
                                                    <img src="{{ asset('assets/admin-images/user2.png') }}" alt="" class="avatar-sm rounded-circle me-2">
                                                @else
                                                    <img src="{{ asset($user->profile_image) }}" alt="" class="avatar-sm rounded-circle me-2">
                                                @endif
                                                <a href="#" class="text-body">{{ $user->first_name }} {{ $user->last_name }}</a>
                                            </td>
                                            <td><span class="badge badge-soft-success mb-0">{{ $user->type }}</span></td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                <ul class="list-inline mb-0">
                                                    @if(auth('admin')->user()->can('Read-Users'))
                                                        <li class="list-inline-item">
                                                            <a href="{{ url('admin/view-agent',$user->id)}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" class="px-2 text-primary">
                                                                <i class="bx bx-show-alt font-size-18"></i>
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if(auth('admin')->user()->can('Delete-Users'))
                                                        <li class="list-inline-item">
                                                            <a href="{{ url('admin/delete-agent',$user->id)}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" class="px-2 text-danger">
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
                <!-- end row -->

                <div class="row g-0 align-items-center pb-4">
                    <div class="col-sm-6">
                        <div>
                            <p class="mb-sm-0">Showing 1 to 10 of 57 entries</p>
                        </div>
                    </div>
                    {{-- <div class="col-sm-6">
                        <div class="float-sm-end">
                            <ul class="pagination mb-sm-0">
                                <li class="page-item disabled">
                                    <a href="#" class="page-link"><i class="mdi mdi-chevron-left"></i></a>
                                </li>
                                <li class="page-item active">
                                    <a href="#" class="page-link">1</a>
                                </li>
                                <li class="page-item">
                                    <a href="#" class="page-link">2</a>
                                </li>
                                <li class="page-item">
                                    <a href="#" class="page-link">3</a>
                                </li>
                                <li class="page-item">
                                    <a href="#" class="page-link">4</a>
                                </li>
                                <li class="page-item">
                                    <a href="#" class="page-link">5</a>
                                </li>
                                <li class="page-item">
                                    <a href="#" class="page-link"><i class="mdi mdi-chevron-right"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div> --}}
                </div>
                <!-- end row -->
                
            </div>


            <!--  Extra Large modal example -->
            <div class="modal fade add-new" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="myExtraLargeModalLabel">Add New</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ url('/admin/create-agent') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="AddNew-first_name">First Name</label>
                                            <input type="text" class="form-control" name="first_name" placeholder="Enter First Name" id="AddNew-first_name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="AddNew-last_name">Last Name</label>
                                            <input type="text" class="form-control" name="last_name" placeholder="Enter Last Name" id="AddNew-last_name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Position</label>
                                            <select class="form-select" name="role_name">
                                                <option>Select Role</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role }}">{{ $role }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="AddNew-Email">Email</label>
                                            <input type="text" class="form-control" name="email" placeholder="Enter Email" id="AddNew-Email" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="AddNew-Phone">Phone</label>
                                            <input type="text" class="form-control" name="phone" placeholder="Enter Phone" id="AddNew-Phone">
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
                                        <button type="submit" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#success-btn" id="btn-save-event"><i class="bx bx-check me-1"></i> Confirm</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
        </div>
    </div>
@endsection

@section('scripts')

@endsection
