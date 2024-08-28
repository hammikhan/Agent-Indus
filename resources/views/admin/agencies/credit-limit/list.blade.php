@extends('admin.layouts.app')
@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        <a href="{{ route('admin.agency.list') }}">Agencies / </a>
        <span>Agents List</span>
    </h4>
@endsection
@section('styles')
    <style>
        .nav-tabs .nav-link {
            margin-bottom: -1px;
            background: 0 0;
            border: 1px solid transparent;
            border-top-left-radius: 0.3rem;
            border-top-right-radius: 0.3rem;
        }
        .nav-tabs-custom {
            border-bottom: none;
        }
    </style>
@endsection

@section('content')
<div class="main-content">

    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-xl-12">
                    @include('admin.agencies.includes.agency-top')
                </div>
                <div class="col-xl-4">
                    <div class="mt-5 mt-lg-0">
                        <div class="card border shadow-none">
                            <div class="card-header bg-transparent border-bottom py-3 px-4">
                                <h5 class="font-size-16 mb-0">Credit Limit</h5>
                            </div>
                            <div class="card-body p-4 pt-2">

                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <tbody>
                                            <tr>
                                                <td>Total Credit:</td>
                                                <td class="text-end text-success">PKR {{ number_format((int)$totalCredit) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Used Credit: </td>
                                                <td class="text-end text-danger">PKR {{ number_format((int)$usedCredit) }}</td>
                                            </tr>
                                            <tr class="bg-light">
                                                <th>Remaining:</th>
                                                <td class="text-end">
                                                    <span class="fw-bold text-warning">
                                                        @php
                                                            $remaining = $totalCredit - $usedCredit;
                                                        @endphp
                                                        PKR {{ number_format((int)$remaining) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- end table-responsive -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header justify-content-between d-flex ">
                            <h4 class="card-title text-muted">{{ $agency->name }} Credit Limit</h4>
                            @if(auth('admin')->user()->can('Add-Credit-Limit'))
                                <div>
                                    <a href="#" data-bs-toggle="modal" data-bs-target=".add-new" class="btn btn-primary">
                                        <i class="bx bx-plus me-1"></i> Add Credit Limit</a>
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            S.No
                                        </th>
                                        <th scope="col">Credit</th>
                                        <th scope="col">Created On</th>
                                        {{-- <th scope="col">Update On</th> --}}
                                      </tr>
                                </thead>

                                <tbody>
                                    @foreach($agency->creditLimits()->orderBy('id', 'desc')->get() as $key => $limit)
                                        <tr>
                                            <td scope="col">
                                                {{ $key+1 }}
                                            </td>
                                            <td>
                                                {{ $limit->currency_type }} {{ number_format((int)$limit->price) }}
                                            </td>
                                            <td>
                                                <span>{{ date('d M Y',strtotime($limit->created_at)) }}</span>
                                            </td>
                                            {{-- <td>
                                                <span>{{ date('d M Y',strtotime($limit->updated_at)) }}</span>
                                            </td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<div class="modal fade add-new" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myExtraLargeModalLabel">Assign Credit Limit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.agency.creditlimit.store') }}" method="post">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="agency_id" value="{{ $agency->id }}">
                        <div class="col-md-12">
                            <div class="mb-3" x-data="{ creditLimit: '' }">
                                <label class="form-label" for="AddNew-credit_limit">Credit Limit</label>
                                <input class="form-control" name="credit_limit" required x-mask="999999" min="5" max="6" placeholder="999999" id="customer_phone" x-model="creditLimit" @keyup="$event.target.value = creditLimit">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-danger me-1" data-bs-dismiss="modal"><i class="bx bx-x me-1"></i> Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bx bx-check me-1"></i> Save Agent
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
@endsection

@section('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
    function formatNumber(value) {
      // Remove non-numeric characters using a regular expression
      const numericValue = value.replace(/[^0-9]/g, '');
    
      // Prevent exceeding maximum digits (adjust `maxDigits` as needed)
      if (numericValue.length > 6) {
        return numericValue.slice(0, 6); // Truncate to maximum length
      }
    
      // Split into groups of three digits (handle negative numbers)
      const parts = numericValue.split(/(?=(?:\d{3})+(?!\d))/).map(Number);
    
      // Reverse the array for correct formatting (thousands first)
      parts.reverse();
    
      // Format with commas and handle leading zeros (adjust as needed)
      const formattedValue = parts.join(',');
      return formattedValue.replace(/^,/, ''); // Remove leading comma if present
    }
</script>
@endsection