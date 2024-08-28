@extends('admin.layouts.app')

@section('styles')

@endsection

@section('content')
<div id="layout-wrapper">
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">News</h4>
                                @if(Auth::guard('admin')->user()->type == 'admin')
                                <div class="dropdown float-end">
                                    <div>
                                        <a href="#" data-bs-toggle="modal" data-bs-target=".add-new" class="btn btn-primary">
                                            <i class="bx bx-plus me-1"></i> Add New
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="my-2">
                                    <ul class="verti-timeline list-unstyled">
                                        @foreach ($news as $item)
                                            <li class="event-list">
                                                <div class="event-timeline-dot">
                                                    <i class="bx bx-right-arrow-circle"></i>
                                                </div>
                                                <div class="d-flex">
                                                    <div class="flex-grow-1">
                                                        <small>{{ date('d M Y',strtotime($item->created_at)) }}</small>
                                                        <div>
                                                            {!! @$item->news !!}
                                                        </div>
                                                    </div>
                                                    @if(Auth::guard('admin')->user()->type == 'admin')
                                                    <div class="dropdown float-end">
                                                        <a href="javascript:void(0)" class="text-info" onclick="editNews({{ $item->id }})" class="btn btn-primary">
                                                            <i class="bx bx-pencil font-size-20 bg-light rounded p-2"></i>
                                                        </a>
                                                        <a href="javascript:void(0)" onclick="delteNews({{ $item->id }})" class="px-2 text-danger">
                                                            <i class="bx bx-trash-alt font-size-20 bg-white rounded p-2"></i>
                                                        </a>
                                                    </div>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if(Auth::guard('admin')->user()->type == 'admin')
<div class="modal fade add-new" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myExtraLargeModalLabel">Add News</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.news.updateCreate') }}" method="POST">
                    @csrf
                    <div class="col-12 mb-3">
                        <label for="for-status" class="form-label">Status</label>
                        <select name="status" id="for-status" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <textarea name="news" id="ckeditor-classic" class="form-control" rows="5"></textarea>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-danger me-1" data-bs-dismiss="modal">
                                <i class="bx bx-x me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="bx bx-check me-1"></i> Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade edit-news" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            {{-- <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">News</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.news.updateCreate') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $news->id ?? '' }}">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="1" {{ (isset($news) && $news->status == 1) ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ (isset($news) && $news->status == 0) ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <textarea name="news" id="ckeditor-classic" class="form-control" rows="5">{{ $news->news ?? '' }}</textarea>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-danger me-1" data-bs-dismiss="modal">
                                            <i class="bx bx-x me-1"></i> Cancel
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bx bx-check me-1"></i> Update
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>

@if(Auth::guard('admin')->user()->type == 'admin')
<script>
    function editNews(id){
        $('.edit-news').modal('show');

        $.ajax({
            url: "{{ url('admin/news/edit') }}/" + id,
            method: 'GET',
            success: function(data) {
                $('.edit-news .modal-content').html(data);
                CKEditor();
            },
            error: function(xhr, status, error) {
                console.error('Failed to fetch news details:', error);
            }
        });
    }
    function delteNews(id){
        showSweetAlertDelete(
            "Are you sure, You want to delete this",
            '',
            'warning',
            'yes Delete',
            'Cancell',
            function() {
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.news.delete')}}",
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
<script>
    CKEditor();
    function CKEditor(){
        ClassicEditor.create(document.querySelector("#ckeditor-classic"), {
            toolbar: {
                items: [
                    'heading',
                    '|',
                    'bold',
                    'italic',
                    'link',
                    'bulletedList',
                    'numberedList',
                    'blockQuote',
                    '|',
                    'undo',
                    'redo'
                ]
            }
        })
        .then(function(editor) {
            editor.ui.view.editable.element.style.height = "200px";
        })
        .catch(function(error) {
            console.error(error);
        });
    }
</script>
@endsection

