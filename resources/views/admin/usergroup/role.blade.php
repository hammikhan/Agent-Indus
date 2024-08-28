@extends('admin.layouts.app')
@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        <span>Roles & Permissions</span>
    </h4>
@endsection
@section('styles')
@endsection

@section('content')
<div class="main-content">

    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row justify-content-center">
                                <div class="col-lg-6">
                                    <div class="text-center mb-5">
                                        <h4>All Roles</h4>
                                        <p class="text-muted">This roles will be assign to admin Users</p>
                                    </div>
                                </div>
                            </div>
    
                            <div class="row">
                                @if(auth('admin')->user()->can('Create-Roles'))
                                    <div class="col-xl-4 col-md-6" style="cursor: pointer;">
                                        <div class="card plan-box overflow-hidden" data-bs-toggle="modal" data-bs-target=".add-new">
                                            <div class="card-body p-4" style="padding-top: 5px !important">
                                                <div class="plan-features mt-4 text-center">
                                                    <i class="fas fa-plus text-muted" style="font-size:65px;"></i>
                                                    <p> Create Role</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @foreach ($all_roles as $role)
                                    <div class="col-xl-4 col-md-6">
                                        <div class="card plan-box overflow-hidden">
                                            <div class="bg-light p-2" style="padding-left:1.5rem !important">
                                                <div class="d-flex pt-2">
                                                    <div class="flex-grow-1">
                                                        <h5>{{ $role->name }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body p-4" style="padding-top: 5px !important">
                                                <form action="#">
                                                    <div class="plan-features mt-4">
                                                        {{-- {{dd($role->permissions->pluck('name')->all())}} --}}
                                                        @foreach ($permission_all_array as $permission)
                                                            <div class="form-check">
                                                                @if(in_array($permission, $role->permissions->pluck('name')->all()))
                                                                    <i class="fas fa-check" style="color: green"></i>
                                                                @else
                                                                    <i class="fas fa-times" style="color: red"></i>
                                                                @endif
                                                                <label class="form-check-label" for="formCheck2">
                                                                    Can {{ $permission }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="text-center plan-btn mt-4 pt-2">
                                                        @if(auth('admin')->user()->can('Delete-Roles'))
                                                        <button class="btn btn-secondary waves-effect waves-light" data-id="{{$role->id }}" id="delete_role">Delete</button>
                                                        @endif
                                                        @if(auth('admin')->user()->can('Edit-Roles'))
                                                            <button class="btn btn-secondary waves-effect waves-light" data-id="{{ $role->id }}" id="edit_role">Edit</button>
                                                        @endif                                           
                                                    </div>
                                                </form>
                                            </div>
                                            
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

        <!--  Add new Role Model -->
        <div class="modal fade add-new" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form class="needs-validation" id="kt_modal_add_role_form" novalidate>
                        @csrf
                        <div class="modal-header">
                            <div class="col-md-6">
                                <label for="roleName" class="form-label" style="display: none">First name</label>
                                <input type="text" class="form-control" id="roleName" name="role_name" placeholder="Enter Role Name" value="" required>
                            </div>
                            <div class="col-md-4 mx-2">
                                <select id="user_type" name="user_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="Admin User">Admin User</option>
                                    <option value="Agency User">Agency User</option>
                                </select>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                @foreach ($permission_all as $module_name => $permission)
                                    <div class="plan-features mt-4 d-flex flex-wrap gap-2">
                                        <span style="width: fit-content; font-weight:bold">{{ $module_name }}:</span>
                                        @foreach ($permission as $singlePermission)
                                            <div class="form-check mb-3 mx-2" style="width: fit-content">
                                                <input class="form-check-input checkbox-permission" type="checkbox" value="{{ $singlePermission->id }}" id="formCheck{{ $singlePermission->id }}">
                                                <label class="form-check-label" for="formCheck{{ $singlePermission->id }}">
                                                    <?php
                                                        $trimWord = substr($singlePermission->name, 0, strpos($singlePermission->name, "-"));
                                                    ?>
                                                    {{ $trimWord }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                            <div class="row mt-2">
                                <div class="col-12 text-end">
                                    <button type="button" class="btn btn-danger me-1" data-bs-dismiss="modal"><i class="bx bx-x me-1"></i> Cancel</button>
                                    <button class="btn btn-primary" type="submit">Save</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- End add new role model -->
        <!--  Edit Role Model -->
        <div class="modal fade edit-role" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" id="edit_role_content">
                    
                </div>
            </div>
        </div>
        <!-- End edit role model -->
        
    </div>
</div>
@endsection

@section('scripts')
    
    <script>

        /*********************************************\
        *               Add New Role                  *
        \*********************************************/
        $('#kt_modal_add_role_form').submit(function(e){
            e.preventDefault();
            
            var roleName = $('#roleName').val();
            var user_type = $('#user_type').val();
            var permissionArray=[];
            $('.checkbox-permission:checked').each(function () {
                permissionArray.push($(this).val());
            });
            $.ajax({
                type:'GET',
                url:"{{route('admin.roles.create')}}",
                data:{
                    roleName:roleName,
                    user_type:user_type,
                    permissionArray:permissionArray
                },
                success:function(data) {
                    if(data.status == 'success'){
                        $('.add-new').closest('.modal').modal('hide');
                        Swal.fire({
                            text: data.message,
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Okay, got it!",
                            customClass: {
                            confirmButton: "btn btn-primary"
                            }
                        })
                        .then((isConfirm) => {
                            if (isConfirm.value) {
                                window.location.reload(true);
                            }
                        });
                    }else{
                        Swal.fire({
                            text: data.message,
                            icon: "warning",
                            buttonsStyling: false,
                            confirmButtonText: "Okay, got it!",
                            customClass: {
                            confirmButton: "btn btn-danger"
                            }
                        });
                    }
                    
                },

                error:function(data){
                    var data = JSON.parse(data.responseText);
                    var message = '';
                    
                    // data.errors.permissionArray ? message = data.errors.permissionArray[0]  :  message = data.errors.roleName[0];
                    Swal.fire({
                        text: data.message,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Okay, got it!",
                        customClass: {
                        confirmButton: "btn btn-primary"
                        }
                    })
                    return false;
                }
            });
        });
        $(document).on('click', '#delete_role', function(e) {
            e.preventDefault();
            var roleId = $(this).data('id');
            showSweetAlertDelete(
                "Are you sure, You want to delete this Role?",
                "You won't be able to revert this!",
                "warning",
                "Yes, delete it!",
                "No, cancel!",
                function() {
                    // Confirm callback function
                    $.ajax({
                        type: 'GET',
                        url: "{{url('admin/delete-role')}}/" + roleId,
                        success: function(data) {
                            if (data.status == 'success') {
                                showSweetAlert(data.message,"success","Okay, got it!","btn btn-primary");
                                window.location.reload(true);
                            } else {
                                showSweetAlert(data.message,"warning","Okay, got it!","btn btn-danger");
                            }
                        }
                    });
                }
            );
        });
        /*********************************************\
        *               Edit Role                  *
        \*********************************************/
        $(document).on('click', '#edit_role', function(e) {
            e.preventDefault();
            var roleId = $(this).data('id');
            var permissionArray=[];
            $('.checkbox-permission:checked').each(function () {
                permissionArray.push($(this).val());
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type:'POST',
                url:"{{url('admin/edit-role')}}",
                data:{roleId:roleId},
                success:function(data) {
                    $('#edit_role_content').html(data);
                },
                error:function(data){
                    var data = JSON.parse(data.responseText);
                    var message = '';
                    
                    // data.errors.permissionArray ? message = data.errors.permissionArray[0]  :  message = data.errors.roleName[0];
                    Swal.fire({
                        text: data.message,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Okay, got it!",
                        customClass: {
                        confirmButton: "btn btn-primary"
                        }
                    })
                    return false;
                }
            });
            $('.edit-role').closest('.modal').modal('show');
        })
        /*
        *   Update Role
        */
        $(document).on('submit','#kt_modal_update_role_form',function(e){
            e.preventDefault();
            var roleName = $('#editRoleName').val();
            var user_type = $('#edit_user_type').find(":selected").val();
            var idRole = $('#idRole').val();
            var permissionArray=[];
            $('.edit-permission:checked').each(function () {
                permissionArray.push($(this).val());
            });

            $.ajax({
                type:'POST',
                url:"{{route('admin.roles.update')}}",
                data:{
                    roleName:roleName,
                    user_type:user_type,
                    permissionArray:permissionArray,
                    idRole:idRole
                },
                success:function(data) {
                    var responseData = JSON.parse(JSON.stringify(data));
                    if(responseData.status == 'success'){
                        Swal.fire({
                            text: data.message,
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Okay, got it!",
                            customClass: {
                            confirmButton: "btn btn-primary"
                            }
                        })
                        .then((isConfirm) => {
                            if (isConfirm.value) {
                                window.location.reload(true);
                            }
                        });
                    }else{
                        Swal.fire({
                            text: responseData.message,
                            icon: "warning",
                            buttonsStyling: false,
                            confirmButtonText: "Okay, got it!",
                            customClass: {
                            confirmButton: "btn btn-primary"
                            }
                        }) 
                    }
                },
                error:function(data){
                    var data = JSON.parse(data.responseText);
                    console.log(data);
                    var message = '';
                    data.errors.idRole ? message = data.errors.idRole[0]  : data.errors.permissionArray ? message = data.errors.permissionArray[0] : data.errors.roleName ? message = data.errors.roleName[0] : message="Some Thing Went Wrong";
                    Swal.fire({
                        text: message,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Okay, got it!",
                        customClass: {
                        confirmButton: "btn btn-primary"
                        }
                    });
                }
            });
        });
    </script>
@endsection







