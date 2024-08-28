<div class="modal fade add-new" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.agent.pricing.group.engine.store') }}" method="POST" class="needs-validation" novalidate>
                @csrf
                <div class="modal-header">
                    @if (@!$pricing_group->id)
                        <div class="col-md-6">
                            <label for="roleName" class="form-label" style="display: none">Group name</label>
                            <input type="text" class="form-control" id="roleName" name="name" placeholder="Enter Group Name" value="" required>
                        </div>
                    @endif
                    <div class="col-md-4 mx-2">
                        <input type="hidden" name="group_id" value="{{ @$pricing_group->id }}">
                        <select id="formrow-inputRule" name="rulePurpose" class="form-select" required>
                            <option value="">Rule Purpose</option>
                            @foreach(\App\Models\PricingEngineCustomer::$rulePurpose as $key => $val)
                                <option value="{{ $key }}">{{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="all_airline" id="all_airline">
                                <label class="form-check-label" for="all_airline">All Airline</label>
                            </div>
                            <div class="mb-3">
                                <label for="choices-single-groups" class="form-label font-size-13 text-muted">Airline</label>
                                <select class="form-select" name="airline" id="airline" required>
                                    <option value="">Select Airline</option>
                                    @foreach (AllAirlines() as $air)
                                        <option value="{{ $air['code'] }}">{{ $air['name'] }} ({{ $air['code'] }})</option>
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
                                        <option value="{{$val->id}}">{{$val->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="formrow-inputType" class="form-label text-muted">Type</label>
                                <select id="formrow-inputType" name="type" required class="form-select">
                                    <option value="Fixed">Fixed</option>
                                    <option value="Percentage">Percentage</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="formrow-firstname-input" class="form-label text-muted">Amount</label>
                                <input type="number" class="form-control" required name="amount" id="formrow-firstname-input">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="formrow-inputStatus" class="form-label text-muted">Status</label>
                                <select id="formrow-inputStatus" name="status" class="form-select">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="formrow-desription-input" class="form-label text-muted">Description</label>
                                <textarea id=""rows="3" name="description" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="all_origin" id="all_origin" checked="">
                                <label class="form-check-label" for="all_origin">All Origin</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="all_destination" id="all_destination" checked="">
                                <label class="form-check-label" for="all_destination">All Destinations</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3" id="origins_list" style="display: none;">
                                <select class="form-control" name="origin[]" id="choices-multiple-origin" placeholder="This is a placeholder" multiple>
                                    @foreach ($airports as $airport)
                                        <option value="{{ $airport->code }}">{{ $airport->city }} ({{ $airport->code }})</option>
                                        {{-- <option value="{{ $air['code'] }}" @if(@$exclude_airlines) {{ (in_array($air['code'],$exclude_airlines)) ? 'selected' : ''}} @endif>{{ $air['code'] }}</option> --}}
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3" id="destinations_list" style="display: none;">
                                <select class="form-control" name="destination[]" id="choices-multiple-destination" placeholder="This is a placeholder" multiple>
                                    @foreach ($airports as $airport)
                                        <option value="{{ $airport->code }}">{{ $airport->city }} ({{ $airport->code }})</option>
                                        {{-- <option value="{{ $air['code'] }}" @if(@$exclude_airlines) {{ (in_array($air['code'],$exclude_airlines)) ? 'selected' : ''}} @endif>{{ $air['code'] }}</option> --}}
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
        </div>
    </div>
</div>