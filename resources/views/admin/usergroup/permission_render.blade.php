
<form class="needs-validation" id="kt_modal_update_role_form" novalidate>
    @csrf
    <div class="modal-header">
        <input type="hidden" name="" id="idRole" value="{{ $role->id }}">
        <div class="col-md-6">
            <label for="roleName" class="form-label" style="display: none">Role name</label>
            <input type="text" class="form-control" id="editRoleName" name="role_name" placeholder="Enter Role Name" value="{{ $role->name}}" required>
        </div>
        <div class="col-md-4 mx-2">
            <select id="edit_user_type" name="user_type" class="form-select" required>
                <option value="">Select Type</option>
                <option value="Admin User" {{ ($role->user_type == 'Admin User') ? 'selected' : '' }}>Admin User</option>
                <option value="Agency User" {{ ($role->user_type == 'Agency User') ? 'selected' : '' }}>Agency User</option>
            </select>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            @foreach ($permission_all as $module_name => $permission)
                <div class="plan-features mt-4 d-flex flex-wrap gap-2">
                    <span style="width: fit-content; font-weight:bold">{{ $module_name }}:</span>
                    @foreach ($permission as $singlePermission )
                        <div class="form-check mb-3 mx-2" style="width: fit-content">
                            <input class="form-check-input edit-permission" {{ in_array($singlePermission->name, $permissionNames) ? 'checked' : ''; }} type="checkbox" value="{{ $singlePermission->id }}" id="formCheckedit{{ $singlePermission->id }}">
                            <label class="form-check-label" for="formCheckedit{{ $singlePermission->id }}">
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
                <button type="button" class="btn btn-danger me-1" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Cancel
                </button>
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
        </div>
    </div>
</form>
