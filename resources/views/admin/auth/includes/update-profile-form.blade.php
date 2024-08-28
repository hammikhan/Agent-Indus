<form action="{{ route('admin.users.bio.update') }}" method="POST" class="needs-validation" novalidate>
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="formrow-firstname-input" class="form-label mb-0">First Name</label>
                <input type="text" class="form-control" required placeholder="First Name" name="first_name" value="{{ adminUser()->first_name }}" id="formrow-firstname-input">
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="formrow-lastname-input" class="form-label mb-0">Last Name</label>
                <input type="text" class="form-control" required name="last_name" value="{{ adminUser()->last_name }}" placeholder="Enter Last Name" id="formrow-lastname-input">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="mb-3">
                <label for="formrow-email-input" class="form-label mb-0">Email</label>
                <input type="email" class="form-control" required placeholder="Email" value="{{ adminUser()->email }}" disabled id="formrow-email-input">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="mb-3">
                <label for="formrow-inputCity" class="form-label mb-0">City</label>
                <input type="text" class="form-control" required name="city" placeholder="Enter City" value="{{ adminUser()->city }}"  id="formrow-inputCity">
            </div>
        </div>
        <div class="col-lg-4">
            <div class="mb-3">
                <label for="formrow-inputState" class="form-label mb-0">Country</label>
                <select id="formrow-inputState" name="country" required class="form-select">
                    <option value="">Choose...</option>
                    @foreach (Countries() as $country)
                        <option value="{{ $country->code }}" {{ ($country->code == adminUser()->country) ? 'selected' : '' }}>{{ substr($country->name, 0, 35) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="mb-3">
                <label for="formrow-inputMobile" class="form-label mb-0">Phone</label>
                <input type="text" class="form-control" required name="phone" value="{{ adminUser()->city }}" placeholder="Enter Phone" id="formrow-inputMobile">
            </div>
        </div>
        <div class="col-lg-12">
            <div class="mb-3">
                <label for="formrow-inputCity" class="form-label mb-0">Address</label>
                <textarea class="form-control" name="address" id="" cols="30" rows="5">{{ adminUser()->address }}</textarea>
            </div>
        </div>
    </div>

    <div class="mt-3 text-end">
        <button type="submit" class="btn btn-primary w-md">Update</button>
    </div>
</form>