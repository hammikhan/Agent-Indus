<div class="modal-header">
    <h5 class="modal-title" id="myExtraLargeModalLabel">Edit News</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form action="{{ route('admin.news.updateCreate') }}" method="POST">
        @csrf
        <input type="hidden" name="id" value="{{ $news->id ?? '' }}">
        <div class="col-12 mb-3">
            <label for="for-status" class="form-label">Status</label>
            <select name="status" id="for-status" class="form-control">
                <option value="1" {{ (isset($news) && $news->status == 1) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ (isset($news) && $news->status == 0) ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-12 mb-3">
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