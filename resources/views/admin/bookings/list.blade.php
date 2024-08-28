@extends('admin.layouts.app')

@section('breadecrum')
    <h4 class="page-title">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-home"></i> / 
        </a> 
        <span>Booking List</span>
    </h4>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
    <style>
        .ribbon-box.right.ribbon-box .ribbon-two {
            left: auto;
            right: -5px;
        }
        .ribbon-box .ribbon-two {
            position: absolute;
            left: -5px;
            top: -5px;
            z-index: 1;
            overflow: hidden;
            width: 75px;
            height: 75px;
            text-align: right;
        }
        .ribbon-box.right.ribbon-box .ribbon-two span {
            left: auto;
            right: -21px;
            -webkit-transform: rotate(45deg);
            transform: rotate(45deg);
        }
        .ribbon-box .ribbon-two-booked span {
            background: #3b76e1;
        }
        .ribbon-box .ribbon-two-ticketed span {
            background: #a8cf42;
        }
        .ribbon-box .ribbon-two-booked span:before {
            border-left: 3px solid #3b76e1;
            border-top: 3px solid #3b76e1;
        }
        .ribbon-box .ribbon-two-ticketed span:before {
            border-left: 3px solid #84a62e;
            border-top: 3px solid #84a62e;
        }
        .ribbon-box .ribbon-two-cancel span:before {
            border-left: 3px solid #a6542e;
            border-top: 3px solid #a6542e;
        }
        .ribbon-box .ribbon-two span:before {
            content: "";
            position: absolute;
            left: 0;
            top: 100%;
            z-index: -1;
            border-right: 3px solid transparent;
            border-bottom: 3px solid transparent;
        }
        .ribbon-box .ribbon-two span {
            font-size: 13px;
            color: #fff;
            text-align: center;
            line-height: 20px;
            -webkit-transform: rotate(-45deg);
            transform: rotate(-45deg);
            width: 100px;
            display: block;
            -webkit-box-shadow: 0 0 8px 0 rgba(0,0,0,.06), 0 1px 0 0 rgba(0,0,0,.02);
            box-shadow: 0 0 8px 0 rgba(0,0,0,.06), 0 1px 0 0 rgba(0,0,0,.02);
            position: absolute;
            top: 19px;
            left: -21px;
            font-weight: 600;
        }
        .ribbon-box .ribbon-two {
            position: absolute;
            left: -5px;
            top: -5px;
            z-index: 1;
            overflow: hidden;
            width: 75px;
            height: 75px;
            text-align: right;
        }
        .ribbon-box .ribbon-two-danger span:after {
            border-right: 3px solid #84a62e;
            border-top: 3px solid #84a62e;
        }
        .ribbon-box .ribbon-two span:after {
            content: "";
            position: absolute;
            right: 0;
            top: 100%;
            z-index: -1;
            border-left: 3px solid transparent;
            border-bottom: 3px solid transparent;
        }
        .ribbon-box .ribbon-two span {
            font-size: 13px;
            color: #fff;
            text-align: center;
            line-height: 20px;
            -webkit-transform: rotate(-45deg);
            transform: rotate(-45deg);
            width: 100px;
            display: block;
            -webkit-box-shadow: 0 0 8px 0 rgba(0,0,0,.06), 0 1px 0 0 rgba(0,0,0,.02);
            box-shadow: 0 0 8px 0 rgba(0,0,0,.06), 0 1px 0 0 rgba(0,0,0,.02);
            position: absolute;
            top: 19px;
            left: -21px;
            font-weight: 600;
        }
        /* ------------------------------ */
        .filter-form{
            border: 1px dashed rgb(206 207 211);
            border-radius: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h5 class="card-title">Total Bookings <span class="text-muted fw-normal ms-2">({{ count($bookings) }})</span></h5>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-outline-success" id="btn-filter">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </div>
                <div class="card p-3 mt-2 align-items-center rounded-1 m-auto" id="filter-form-card" style="box-shadow: 5px 7px 17px #c2c5cc; display:none;">
                    <form class="gx-3 gy-2 w-100 filter-form p-3 bg-light" method="GET" action="{{ route('admin.bookings') }}" id="filterForm">
                        <div class="row">
                            <div class="col-md-3 col-lg-4 mb-2">
                                <select class="form-select rounded-1 bg-light" id="search_criteria" name="search_criteria">
                                    <option value="" {{ (request('search_criteria') == '') ? 'selected' : '' }}>Select Criteria</option>
                                    <option value="PNR" {{ (request('search_criteria') == 'PNR') ? 'selected' : '' }}>PNR</option>
                                    <option value="ticket" {{ (request('search_criteria') == 'ticket') ? 'selected' : '' }}>Ticket Number</option>
                                    <option value="pax_name" {{ (request('search_criteria') == 'pax_name') ? 'selected' : '' }}>PAX Name</option>
                                    <option value="email" {{ (request('search_criteria') == 'email') ? 'selected' : '' }}>Email</option>
                                    <option value="phone" {{ (request('search_criteria') == 'phone') ? 'selected' : '' }}>Phone</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-4 rounded-1 mb-3">
                                <input type="text" class="form-control rounded-1 bg-light" name="search_text" id="search_text" value="{{ request('search_text') }}" placeholder="Search Text" autocomplete="off">
                                <input type="text" class="form-control rounded-1 bg-light" name="first_name" id="first_name" value="{{ request('first_name') }}" placeholder="First Name" autocomplete="off" style="display: none">
                                <input type="text" class="form-control rounded-1 bg-light mt-2" name="last_name" id="last_name" value="{{ request('last_name') }}" placeholder="Last Name" autocomplete="off" style="display: none">
                            </div>
                            <div class="col-md-4 col-lg-4">
                                <label class="form-check-label" for="booking_date">
                                    <input class="form-check-input" type="radio" id="booking_date" name="date_by" {{ (request('date_by') == 'booking_date') ? 'checked' : '' }} value="booking_date">
                                    <span class="text-blackish-gray">
                                        Booking Date
                                    </span>
                                </label>
                                <label class="form-check-label mx-3" for="issued_date">
                                    <input class="form-check-input" type="radio" id="issued_date" name="date_by" {{ (request('date_by') == 'issued_date') ? 'checked' : '' }} value="issued_date">
                                    <span class="text-blackish-gray">
                                        Issue Date
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-lg-3 mt-2">
                                <select class="form-select rounded-1 bg-light" id="booking_status" name="status">
                                    <option value="" {{ (request('status') == '') ? 'selected' : '' }}>All</option>
                                    <option value="Ticketed" {{ (request('status') == 'Ticketed') ? 'selected' : '' }}>Ticketed</option>
                                    <option value="Not Ticketed" {{ (request('status') == 'Not Ticketed') ? 'selected' : '' }}>Not Ticketed</option>
                                    <option value="Voided" {{ (request('status') == 'Voided') ? 'selected' : '' }}>Voided</option>
                                    <option value="Cancelled" {{ (request('status') == 'Cancelled') ? 'selected' : '' }}>Cancel</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-3 mt-2">
                                <input type="text" class="form-control rounded-1 bg-light" name="from" id="from" placeholder="From" value="{{ request('from') }}" autocomplete="off">
                            </div>
                            <div class="col-md-3 col-lg-3 mt-2">
                                <input type="text" class="form-control rounded-1 bg-light" name="to" id="to" placeholder="To" value="{{ request('to') }}" autocomplete="off">
                            </div>
                            <div class="col-lg-3 mt-2">
                                <div class="hstack gap-3">
                                    <button type="submit" class="btn btn-secondary">Submit</button>
                                    <div class="vr"></div>
                                    <button type="reset" class="btn btn-outline-danger" id="recet-filter">Reset</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- -------------------------------Booking list Table--------------- --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="">
                            <div class="table-responsive">
                                <table class="table project-list-table table-nowrap align-middle table-borderless">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="ps-4" style="width: 50px;">
                                                Ref-ID
                                            </th>
                                            <th scope="col">PNR</th>
                                            {{-- <th scope="col">Email</th> --}}
                                            <th scope="col" width="10%">Origin - Dest</th>
                                            <th scope="col">Trip</th>
                                            <th scope="col">Provider</th>
                                            <th scope="col">
                                                Created By
                                            </th>
                                            <th scope="col">Booked Date</th>
                                            <th scope="col">Issued Date</th>
                                            <th scope="col">Ticket Status</th>
                                            <th scope="col">Segment Status</th>
                                            <th scope="col">Total Price</th>
                                            <th scope="col" style="width: 200px;">Action</th>
                                          </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($bookings as $key => $item)
                                            @php
                                                $customer = json_decode($item->customer_data, true);
                                                $final_data = json_decode($item->final_data, true);
                                                
                                                $flight = $final_data['Flights'];
                                                $Segments = $flight[0]['Segments'];
                                                $flightCode = $Segments[0]['OperatingAirline']['Code'];
                                                $origin = $Segments[0]['Departure']['LocationCode'];
                                                $DepartureDateTime = $Segments[0]['Departure']['DepartureDateTime'];
                                                $destination = '';
                                                foreach ($Segments as $key => $seg) {
                                                    $destination = $seg['Arrival']['LocationCode'];
                                                }
                                                $fare_brand = 0;
                                            @endphp
                                            <tr>
                                                <td scope="col" class="ps-4" style="width: 50px;">
                                                    <a href="#" class="text-body">
                                                        B000{{ $item->id }}
                                                    </a>
                                                </td>
                                                <td>
                                                    {{-- <a href="{{ route('admin.create.booking', ['booking_ref' => $item->ref_key]) }}"> --}}
                                                    <a href="{{ route('admin.create.booking', ['booking_ref' => $item->ref_key]) }}">
                                                        <b>{{ $item->pnrCode }}</b>
                                                    </a>
                                                </td>
                                                
                                                {{-- <td>
                                                    <span>
                                                        <i class="mdi mdi-email me-1"></i>
                                                        {{ $item->customerEmail }}
                                                    </span>
                                                    <br>
                                                    <span>
                                                        <i class="mdi mdi-phone-outline me-1"></i>
                                                        {{ $customer['customer_phone'] }}
                                                    </span>
                                                    <br>
                                                </td> --}}
                                                <td class="text-center">
                                                    {{ CityNameByAirportCode($origin) }}
                                                    <i class="mdi mdi-airplane-takeoff me-1"></i>
                                                     - 
                                                    <i class="mdi mdi-airplane-landing me-1"></i>
                                                    {{ CityNameByAirportCode($destination) }}
                                                    <br>
                                                    <img src="{{ asset('assets/airlines/'.$flightCode.'.png')}}" class="avatar-md" alt="" style="height: 15px; width: 15px;">
                                                    {{ date('D, d M, Y', strtotime($DepartureDateTime)) }}
                                                </td>
                                                <td>
                                                    {{ (count($flight) == 1) ? 'O/W' : 'R/T' }}
                                                </td>
                                                <td>
                                                    {{ $item->api }}
                                                </td>
                                                <td>
                                                    @if(@$item->agency)
                                                        {{ @$item->agency->name }}
                                                    @elseif (auth('admin')->user()->type == 'Travel Agent')
                                                        {{ $item->admin->first_name }} {{ @$item->admin->last_name }}
                                                    @else
                                                        {{-- <img src="{{ asset(@$item->agency->logo) }}" alt="{{ @$item->agency->name }}" class="avatar-sm rounded-circle"> --}}
                                                        {{ @$item->admin->first_name }} {{ @$item->admin->last_name }}
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ date('d M, Y',strtotime($item->created_at)) }}
                                                    <br>
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ date('h:i a',strtotime($item->created_at)) }}
                                                </td>
                                                <td>
                                                    @if (@$item->issued_at)
                                                        {{ date('d M, Y',strtotime($item->issued_at)) }}
                                                        <br>
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ date('h:i a',strtotime($item->issued_at)) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        if($item->status == 'Not Ticketed')
                                                            $status_badge = 'primary';
                                                        else if($item->status == 'Ticketed')
                                                            $status_badge = 'success';
                                                        else if($item->status == 'Voided')
                                                            $status_badge = 'warning';
                                                        else
                                                            $status_badge = 'danger';
                                                    @endphp
                                                    <span class="badge badge-soft-{{$status_badge}} mb-0">
                                                        <b>{{ $item->status }}</b>
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        if($item->pnr_status == 'Confirmed')
                                                            $seg_status_badge = 'primary';
                                                        else
                                                            $seg_status_badge = 'danger';
                                                    @endphp
                                                    <span class="badge badge-soft-{{$seg_status_badge}} mb-0">
                                                        <b>{{ $item->pnr_status }}</b>
                                                    </span>
                                                </td>
                                                <td>
                                                    PKR {{ number_format($item->total) }}
                                                </td>
                                                {{-- <td>{{ $user->email }}</td> --}}
                                                <td>
                                                    <div class="dropdown">
                                                        <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="mdi mdi-dots-horizontal font-size-18"></i>
                                                        </a>
                                                        <span class="spinner spinner-border spinner-border-sm d-none" id="email_spiner_{{ @$item->ref_key }}" role="status" aria-hidden="true"></span>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item text-info" href="{{ route('admin.generate.pdf', ['booking_ref' => $item->ref_key, 0]) }}" target="_blank">
                                                                <i class="fa fa-print"></i>
                                                                View PDF
                                                            </a>
                                                            <a class="dropdown-item text-success" href="{{ route('admin.generate.pdf', ['booking_ref' => $item->ref_key, 1]) }}" target="_blank">
                                                                <i class="fa fa-print"></i>
                                                                View PDF With Fare
                                                            </a>
                                                            <a class="dropdown-item text-info" href="javascript:void(0)" onclick="emailBooking('{{ @$item->pnrCode }}','{{ @$item->ref_key }}', this,0)">
                                                                <i class="fas fa-envelope"></i>
                                                                Email Booking
                                                            </a>
                                                            <a class="dropdown-item text-success" href="javascript:void(0)" onclick="emailBooking('{{ @$item->pnrCode }}','{{ @$item->ref_key }}', this,1)">
                                                                <i class="fas fa-envelope"></i>
                                                                Email Booking With Fare
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- ------------------------------------Pagination------------------ --}}
                <div class="row g-0 align-items-center pb-4">
                    <div class="col-sm-6">
                        <div>
                            <p class="mb-sm-0">
                                Showing {{ $bookings->firstItem() }} to {{ $bookings->lastItem() }} of {{ $bookings->total() }} entries
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        {{ $bookings->links('admin.paginations.bootstrap') }}
                    </div>
                </div>
                <!-- end row -->
                
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<!-- datepicker js -->
<script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#recet-filter').on('click', function() {
            console.log(12321);
            $('#filterForm')[0].reset();
            
            // Clear URL parameters
            const url = window.location.href.split('?')[0];
            window.history.replaceState(null, null, url);

            // Manually clear select elements and radio buttons
            $('#search_criteria').val('');
            $('#search_text').val('');
            $('input[name="date_by"]').prop('checked', false);
            $('#booking_status').val('');
            $('#from').val('');
            $('#to').val('');
        });
        $('#btn-filter').click(function() {
            $('#filter-form-card').slideToggle();
        });
    });
    flatpickr("#from", {
        dateFormat: "d-m-Y",
        onClose: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                const selectedDepartureDate = selectedDates[0];
                const nextDay = new Date(selectedDepartureDate);
                nextDay.setDate(nextDay.getDate() + 1);
                const formattedNextDay = instance.formatDate(nextDay, "d-m-Y");
                to_date.set("minDate", formattedNextDay);
            }
        }
    });
    const to_date = flatpickr("#to", {
        dateFormat: "d-m-Y",
        maxDate: "today"
    });

    function emailBooking(pnr, book_ref_key, button,f) {
        $(button).attr('disabled', true);
        $('#email_spiner_'+book_ref_key).removeClass('d-none');
        $.ajax({
            type: 'POST',
            url: "{{ route('admin.email.booking')}}",
            data: {
                pnr,book_ref_key,f
            },
            success: function(data) {
                if (data.status == 'success') {
                    showSweetAlert(data.message,"success","Okay, got it!","btn btn-primary");
                    $('#email_spiner_'+book_ref_key).addClass('d-none');
                    
                } else {
                    showSweetAlert(data.message,"warning","Okay, got it!","btn btn-danger");
                    $('#email_spiner_'+book_ref_key).addClass('d-none');
                }
            },
            complete: function () {
                $('#email_spiner_'+book_ref_key).addClass('d-none');
                $(button).attr('disabled', false);
            }
        });
    }
    $(document).ready(function() {
        function toggleLastNameInput() {
            if ($('#search_criteria').val() === 'pax_name') {
                $('#first_name').show();
                $('#last_name').show();
                $('#search_text').hide();
            } else {
                $('#first_name').hide();
                $('#last_name').hide();
                $('#search_text').show();
            }
        }

        toggleLastNameInput();
        $('#search_criteria').change(function() {
            toggleLastNameInput();
        });
    });
</script>
@endsection
