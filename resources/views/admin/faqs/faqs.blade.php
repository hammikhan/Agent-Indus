@extends('admin.layouts.app')

@section('styles')

@endsection

@section('content')
<div id="layout-wrapper">
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Frequently Asked Questions?</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($faqs as $key => $faq)
                                <div class="col-lg-4">
                                    <div class="card bg-light overflow-hidden">
                                        <div class="card-body">
                                            <div class="faq-icon">
                                                <i class="bx bx-help-circle text-primary"></i>
                                            </div>
                                            <h5 class="text-primary">{{ $key+1 }}.</h5>
                                            <h5 class="faq-title mt-3">{{ $faq->question }}</h5>
                                            <p class="faq-ans text-muted mt-2 mb-0">{{ $faq->answer }}</p>
                                            @if(Auth::guard('admin')->user()->type == 'admin')
                                                <div class="dropdown float-end">
                                                    <a href="javascript:void(0)" class="text-info" onclick="editFaqs({{ $faq->id }})" class="btn btn-primary">
                                                        <i class="bx bx-pencil font-size-20 bg-white rounded p-2"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" onclick="delteFaqs({{ $faq->id }})" class="px-2 text-danger">
                                                        <i class="bx bx-trash-alt font-size-20 bg-white rounded p-2"></i>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @endforeach
                                

                            @if(Auth::guard('admin')->user()->type == 'admin')
                                <div class="col-xl-4 col-md-6" style="cursor: pointer;">
                                    <div class="card plan-box overflow-hidden" data-bs-toggle="modal" data-bs-target=".add-new">
                                        <div class="card-body p-4" style="padding-top: 5px !important">
                                            <div class="plan-features mt-4 text-center">
                                                <i class="fas fa-plus text-muted" style="font-size:65px;"></i>
                                                <p> Add FAQs</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                </div>
                {{-- *******************Edit Modal***************************** --}}
                {{-- <div class="modal fade edit-faq" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" style="position: absolute">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
</div>
@if(Auth::guard('admin')->user()->type == 'admin')
<!--  Add new Role Model -->
<div class="modal fade add-new" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form class="needs-validation" action="{{ route('admin.faqs.store') }}" method="POST" id="kt_modal_add_role_form" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="myExtraLargeModalLabel">Add FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="question" class="form-label">Question</label>
                                <input type="text" class="form-control" id="question" name="question" placeholder="Enter Question" required>
                            </div>
                        </div>
                        <div class="col-md-12 ">
                            <div class="mb-3">
                                <label for="answer" class="form-label text-muted">Answer</label>
                                <textarea id="answer" rows="3" name="answer" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
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
<div class="modal fade edit-faq" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            
        </div>
    </div>
</div>
@endif
<!-- End edit role model -->
@endsection

@section('scripts')
@if(Auth::guard('admin')->user()->type == 'admin')
<script>
    function editFaqs(id){
        $('.edit-faq').modal('show');
        
        $.ajax({
            url: "{{ url('admin/faqs/edit') }}/" + id,
            method: 'GET',
            success: function(data) {
                $('.edit-faq .modal-content').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Failed to fetch faq details:', error);
            }
        });
    }
    function delteFaqs(id){
        showSweetAlertDelete(
            "Are you sure, You want to delete this",
            '',
            'warning',
            'yes Delete',
            'Cancell',
            function() {
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.faqs.delete')}}",
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

