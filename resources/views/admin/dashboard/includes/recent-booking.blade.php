@foreach ($recent_bookings as $booking)
                                                    
                                                        @php
                                                            $bgStatus = match($booking->status) {
                                                                'Not Ticketed' => 'primary',
                                                                'Ticketed' => 'success',
                                                                'Voided' => 'warning',
                                                                default => 'danger',
                                                            };
                                                            $final_data = json_decode($booking->final_data,true);
                                                            if(@$final_data['LowFareSearch']){
                                                                $flight = $final_data['LowFareSearch'];
                                                            }else{
                                                                $flight = $final_data['Flights'];
                                                            }
                                                            $flightCode = $flight[0]['Segments'][0]['OperatingAirline']['Code'];
                                                            $departDate = $flight[0]['Segments'][0]['Departure']['DepartureDateTime'];
                                                            $origin = $flight[0]['Segments'][0]['Departure']['LocationCode'];
                                                            $destination = end($flight[0]['Segments'])['Arrival']['LocationCode'];
                                                        @endphp
                                                        <tr>
                                                            <td class="py-1">
                                                                <a href="{{ route('admin.create.booking', ['booking_ref' => $booking->ref_key]) }}" class="text-body fw-semibold">
                                                                    #{{ $booking->pnrCode }}
                                                                </a>
                                                            </td>
                                                            <td class="py-1">
                                                                <img src="{{ asset('assets/airlines/'.$flightCode.'.png')}}" class="avatar-md" alt="" style="height: 15px; width: 15px;">
                                                                {{ $origin }}-{{ $destination }}
                                                            </td>
                                                            <td class="py-1">
                                                                {{ date('d M, Y', strtotime($departDate)) }}
                                                                <i class="fa fa-clock" style="color: #9f9494;"></i>
                                                                {{ date('H:i', strtotime($departDate)) }}
                                                            </td>
                                                            <td class="py-1">
                                                                @if(@$booking->agency)
                                                                    {{-- <img src="{{ asset(@$booking->agency->logo) }}" alt="{{ @$booking->agency->name }}" class="avatar-sm rounded-circle"> --}}
                                                                    {{ @$booking->agency->name }}
                                                                @elseif (auth('admin')->user()->type == 'Travel Agent')
                                                                    {{ $booking->admin->first_name }} {{ @$booking->admin->last_name }}
                                                                @else
                                                                    {{ @$booking->admin->first_name }} {{ @$booking->admin->last_name }}
                                                                @endif
                                                            </td>
                                                            <td class="py-1">
                                                                {{ date('d M, Y',strtotime($booking->created_at)) }}
                                                                <br>
                                                                <i class="fas fa-clock me-1" style="color: #9f9494;"></i>
                                                                {{ date('h:i a',strtotime($booking->created_at)) }}
                                                            </td>
                                                            <td class="py-1">
                                                                @if (@$booking->issued_at)
                                                                    {{ date('d M, Y',strtotime($booking->issued_at)) }}
                                                                    <br>
                                                                    <i class="fas fa-clock me-1" style="color: #9f9494;"></i>
                                                                    {{ date('h:i a',strtotime($booking->issued_at)) }}
                                                                @endif
                                                            </td>
                                                            <td class="py-1">
                                                                Sabre
                                                            </td>
                                                            <td class="text-center py-1">
                                                                <span class="badge badge-pill badge-soft-{{ $bgStatus }} font-size-11">
                                                                    {{ $booking->status }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center py-1">
                                                                @php
                                                                    if($booking->pnr_status == 'Confirmed')
                                                                        $seg_status_badge = 'primary';
                                                                    else
                                                                        $seg_status_badge = 'danger';
                                                                @endphp
                                                                <span class="badge badge-soft-{{$seg_status_badge}} mb-0">
                                                                    <b>{{ $booking->pnr_status }}</b>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach