<div class="modal-header">
    <h5 class="modal-title" id="myExtraLargeModalLabel">Edit FAQ</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form action="{{ route('admin.faqs.update') }}" method="POST">
        @csrf
        <input type="hidden" name="id" value="{{ $faq->id }}">
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="question" class="form-label">Question</label>
                    <input type="text" class="form-control" id="question" name="question" placeholder="Enter Question" value="{{ $faq->question }}" required>
                </div>
            </div>
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="formrow-inputStatus" class="form-label text-muted">Status</label>
                    <select id="formrow-inputStatus" name="status" class="form-select">
                        <option value="1" {{ ($faq->status == 1) ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ ($faq->status == 0) ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="col-md-12 ">
                <div class="mb-3">
                    <label for="answer" class="form-label text-muted">Answer</label>
                    <textarea id="answer" rows="3" name="answer" class="form-control">{{ $faq->answer }}</textarea>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12 text-end">
                <button type="button" class="btn btn-danger me-1" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="bx bx-check me-1"></i> Updated
                </button>
            </div>
        </div>
    </form>
</div>