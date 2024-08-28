@extends('admin.layouts.app')

@section('styles')

@endsection

@section('content')
<div id="layout-wrapper">
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 mb-3">
                        @if(Auth::guard('admin')->user()->type == 'admin')
                            <div class="dropdown float-end">
                                <div>
                                    <a href="#" data-bs-toggle="modal" data-bs-target=".add-account" class="btn btn-primary">
                                        <i class="bx bx-plus me-1"></i> Add New
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                    @foreach ($bankAccounts as $item)
                        <div class="col-md-6 col-sm-12">
                            <div class="card border shadow-none">
                                <div class="card-body">

                                    <div class="d-flex align-items-start border-bottom pb-3">
                                        <div class="me-4">
                                            <img src="{{ asset($item->image) }}" alt="{{ asset($item->image) }}" class="avatar-lg rounded">
                                        </div>
                                        <div class="flex-grow-1 align-self-center overflow-hidden">
                                            <div>
                                                <h5 class="text-truncate font-size-18 text-dark">{{ $item->account_title }}</h5>
                                                <p class="text-muted mb-0">
                                                    Branch Code: {{ $item->branch_code }}
                                                </p>
                                                <p class="mb-0 mt-1">Account No: <span class="fw-medium">{{ $item->account_no }}</span></p>
                                                <p class="mb-0 mt-1">IBAN: <span class="fw-medium">{{ $item->iban }}</span></p>
                                            </div>
                                        </div>
                                        @if(Auth::guard('admin')->user()->type == 'admin')
                                        <div class="flex-shrink-0 ms-2">
                                            <ul class="list-inline mb-0 font-size-16">
                                                <li class="list-inline-item">
                                                    <a href="javascript:void(0)" onclick="editAccount({{ $item->id }})" class="text-muted px-1">
                                                        <i class="bx bx-pencil font-size-20 bg-light rounded p-2"></i>
                                                    </a>
                                                </li>
                                                <li class="list-inline-item">
                                                    <a href="javascript:void(0)" onclick="delteAccount({{ $item->id }})" class="text-muted px-1">
                                                        <i class="mdi mdi-trash-can-outline"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@if(Auth::guard('admin')->user()->type == 'admin')
    <div class="modal fade add-account" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myExtraLargeModalLabel">Add New Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.bank.accounts.updateCreate') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="col-12 mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" name="image" id="image" class="form-control">
                        </div>
                        <div class="form-group mb-3">
                            <label for="account_title">Account Title</label>
                            <input type="text" class="form-control" id="account_title" name="account_title" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="branch_code">Branch Code</label>
                            <input type="text" class="form-control" id="branch_code" name="branch_code" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="account_no">Account No</label>
                            <input type="text" class="form-control" id="account_no" name="account_no" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="iban">IBAN</label>
                            <input type="text" class="form-control" id="iban" name="iban" required>
                        </div>
                        <div class="form-group">
                            <label for="status_name">Status</label>
                            <select class="form-control" id="status_name" name="status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-danger me-1" data-bs-dismiss="modal">
                                    <i class="bx bx-x me-1"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bx bx-check me-1"></i> Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade edit-account" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>

@if(Auth::guard('admin')->user()->type == 'admin')
<script>
    function editAccount(id){
        $('.edit-account').modal('show');

        $.ajax({
            url: "{{ url('admin/bank-accounts/edit') }}/" + id,
            method: 'GET',
            success: function(data) {
                $('.edit-account .modal-content').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Failed to fetch news details:', error);
            }
        });
    }
    function delteAccount(id){
        showSweetAlertDelete(
            "Are you sure, You want to delete this",
            '',
            'warning',
            'yes Delete',
            'Cancell',
            function() {
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.bank.accounts.delete')}}",
                    data: {
                        id
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            showSweetAlert(data.message,"success","Okay, got it!","btn btn-primary");
                            location.reload();
                        } else {
                            showSweetAlert(data.message,"warning","Okay, got it!","btn btn-danger");
                        }
                    }
                });
            }
        );
    }
    
</script>
@endif

@endsection

