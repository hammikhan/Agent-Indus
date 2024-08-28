<form action="{{ route('admin.users.change.password') }}" method="POST" class="needs-validation" novalidate>
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="mb-4">
                <label for="old_password" class="form-label mb-0">Old Password</label>
                <input type="password" name="old_password" class="form-control" placeholder="Enter old password" required id="old_password">
                <span class="oldPawwordErrorText error-input-text"></span>
            </div>
        </div>
        <div class="col-md-12">
            <div class="mb-3">
                <label for="formrow-newpassword-input" class="form-label mb-0">New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Enter new passord" required id="formrow-newpassword-input">
                {{-- <button type="button" class="btn btn-link position-absolute h-100 end-0 top-0" id="password-addon">
                    <i class="mdi mdi-eye-outline font-size-18 text-muted"></i>
                </button> --}}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="mb-3">
                <label for="formrow-confirmation-input" class="form-label mb-0">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Enter confirm password" required id="formrow-confirmation-input">
            </div>
        </div>
    </div>

    <div class="mt-3 text-end">
        <button type="submit" class="btn btn-primary w-md">Update</button>
    </div>
</form>