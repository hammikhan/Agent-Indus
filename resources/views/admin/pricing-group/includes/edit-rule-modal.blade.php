{{-- <link href="{{ asset('assets/libs/choices/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" /> --}}
<form action="{{ route('admin.agent.pricing.group.engine.rule.update') }}" method="POST" class="needs-validation" novalidate>
    @csrf
    {{-- @php
        $data = $rule->data['isAllDestinations'];
    @endphp
    @dump($data) --}}
    <div class="modal-header">
        <div class="col-md-6">
            <label for="roleName" class="form-label" style="display: none">Group name</label>
            <input type="text" class="form-control" id="roleName" name="name" value="{{ $rule->pricingGroup->name }}" required>
        </div>
        <div class="col-md-4 mx-2">
            <input type="hidden" name="pricin_group_id" value="{{ @$rule->pricingGroup->id }}">
            <input type="hidden" name="rule_id" value="{{ @$rule->id }}">
            <select id="formrow-inputRule" name="rulePurpose" class="form-select">
                <option value="">Rule Purpose</option>
                @foreach(\App\Models\PricingEngineCustomer::$rulePurpose as $key => $val)
                    <option value="{{ $key }}" {{ ($rule->rule == $val) ? 'selected' : '' }}>{{ $val }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="all_airline" id="all_airline2" {{ (@$rule->data['isAllAirline'] == 1) ? 'checked' : ''}}>
                    <label class="form-check-label" for="all_airline2">All Airline</label>
                </div>
                <div class="mb-3">
                    <label for="airline2" class="form-label font-size-13 text-muted">Airline</label>
                    <select class="form-select" name="airline" id="airline2" required {{ (@$rule->data['isAllAirline'] == 1) ? 'disabled' : ''}}>
                        <option value="">Select Airline</option>
                        @foreach (AllAirlines() as $air)
                            <option value="{{ $air['code'] }}" {{ ($rule->airline == $air['code']) ? 'selected' : ''}}>{{ $air['name'] }} ({{ $air['code'] }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="formrow-inputApi" class="form-label text-muted">API</label>
                    <select id="formrow-inputApi" name="api_id" required class="form-select">
                        <option value="">Select API</option>
                        @foreach ($apis as $val)
                        <option value="{{$val->id}}" {{ ($rule->api_id == $val->id) ? 'selected' : '' }}>{{$val->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="formrow-inputType" class="form-label text-muted">Type</label>
                    <select id="formrow-inputType" name="type" required class="form-select">
                        <option value="Fixed" {{ ($rule->type == 'Fixed') ? 'selected' : '' }}>Fixed</option>
                        <option value="Percentage" {{ ($rule->type == 'Percentage') ? 'selected' : '' }}>Percentage</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="formrow-firstname-input" class="form-label text-muted">Amount</label>
                    <input type="number" class="form-control" value="{{ $rule->amount }}" required name="amount" id="formrow-firstname-input">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="formrow-inputStatus" class="form-label text-muted">Status</label>
                    <select id="formrow-inputStatus" name="status" class="form-select">
                        <option value="1" {{ ($rule->status == 1) ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ ($rule->status == 0) ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="col-md-12">
                <div class="mb-3">
                    <label for="formrow-desription-input" class="form-label text-muted">Description</label>
                    <textarea id=""rows="3" name="description" class="form-control">{{ $rule->description }}</textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch mb-3">
                    <label class="form-check-label" for="all_origin">All Orogin</label>
                    <input class="form-check-input" type="checkbox" name="all_origin" id="all_origin" {{ (@$rule->data['isAllOrigins']) ? 'checked' : ''}}>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="all_destination" id="all_destination" {{ (@$rule->data['isAllDestinations']) ? 'checked' : ''}}>
                    <label class="form-check-label" for="all_destination">All Destinations</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3" id="origins_list" style="{{ (@$rule->data['isAllOrigins'] == 0) ? '' : 'display:none'}}">
                    <label for="choices-multiple-origin-edit" class="form-label font-size-13 text-muted">Origin</label>
                    <select class="form-control" name="origin[]" id="choices-multiple-origin-edit" placeholder="This is a placeholder" multiple>
                        @foreach ($airports as $airport)
                            <option value="{{ $airport->code }}" @if(@$rule->data['origins']) {{ (in_array($airport->code,$rule->data['origins'])) ? 'selected' : ''}} @endif>
                                {{ $airport->city }} ({{ $airport->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3" id="destinations_list" style="{{ (@$rule->data['isAllDestinations'] == 0) ? '' : 'display:none'}}">
                    <label for="choices-multiple-destination-edit" class="form-label font-size-13 text-muted">Destination</label>
                    <select class="form-control" name="destination[]" id="choices-multiple-destination-edit" placeholder="This is a placeholder" multiple>
                        @foreach ($airports as $airport)
                            <option value="{{ $airport->code }}" @if(@$rule->data['destinations']) {{ (in_array($airport->code,$rule->data['destinations'])) ? 'selected' : ''}} @endif>
                                {{ $airport->city }} ({{ $airport->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-start gap-3 mt-4">
            <button type="submit" class="btn btn-primary w-md ms-auto">Submit</button>
        </div>
    </div>
</form>

{{-- <script src="{{ asset('assets/libs/choices/public/assets/scripts/choices.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#all_origin").change(function() {
            if ($(this).is(":checked")) {
                $("#origins_list").hide();

            } else {
                $("#origins_list").show();
            }
        });
        $("#all_destination").change(function() {
            if ($(this).is(":checked")) {
                $("#destinations_list").hide();

            } else {
                $("#destinations_list").show();
            }
        });
    });
    ///////////////////////////Choice////////////////////
    document.addEventListener("DOMContentLoaded", function() {
        var e = document.querySelectorAll("[data-trigger]");
        for (i = 0; i < e.length; ++i) {
            var a = e[i];
            new Choices(a, {
                placeholderValue: "This is a placeholder set in the config",
                searchPlaceholderValue: "This is a search placeholder"
            })
        }
        new Choices("#choices-multiple-origin", {
            removeItemButton: !0
        })
        new Choices("#choices-multiple-destination", {
            removeItemButton: !0
        })
    });
</script> --}}