
<div class="modal-header">
    <h5 class="modal-title" id="myExtraLargeModalLabel">Edit Bank Account</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form action="{{ route('admin.bank.accounts.accountUpdate') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <input type="hidden" name="id" value="{{ $bankAccount->id }}">
            <div class="col-6 mb-3">
                <label for="account_image" class="form-label">Image</label>
                <input type="file" name="image" id="account_image" class="form-control">
            </div>
            <div class="col-6 mb-3">
                <img src="{{ asset($bankAccount->image) }}" alt="{{ $bankAccount->image }}" width="100px;">
            </div>
            <div class="form-group mb-3">
                <label for="account_title">Account Title</label>
                <input type="text" class="form-control" id="account_title" name="account_title" value="{{ $bankAccount->account_title }}" required>
            </div>
            <div class="form-group mb-3">
                <label for="branch_code">Branch Code</label>
                <input type="text" class="form-control" id="branch_code" name="branch_code" value="{{ $bankAccount->branch_code }}" required>
            </div>
            <div class="form-group mb-3">
                <label for="account_no">Account No</label>
                <input type="text" class="form-control" id="account_no" name="account_no" value="{{ $bankAccount->account_no }}" required>
            </div>
            <div class="form-group mb-3">
                <label for="iban">IBAN</label>
                <input type="text" class="form-control" id="iban" name="iban" value="{{ $bankAccount->iban }}" required>
            </div>
            <div class="form-group mb-3">
                <label for="bank_status">Status</label>
                <select class="form-control" id="bank_status" name="status" required>
                    <option value="1" {{ $bankAccount->status == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $bankAccount->status == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12 text-end">
                <button type="button" class="btn btn-danger me-1" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Cancel
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="bx bx-check me-1"></i> Update Changes
                </button>
            </div>
        </div>
    </form>
</div>

