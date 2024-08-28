@extends('admin.auth.layouts.auth-app')


@section('styles')


@endsection

@section('content')

    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" id="custom_toast" style="width: 100%;">
        <div class="d-flex">
            <div class="toast-body">
                asdfsdafsaf
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>

    <div class="auth-full-page-content rounded d-flex p-3 my-2">
        <div class="w-100">
            <div class="d-flex flex-column h-100">
                <div class="mb-4 mb-md-5">
                    <a href="javascript:void(0)" class="d-block auth-logo">
                        <img src="{{ asset('assets/images/mainLogo.jpg') }}" alt="" height="100" class="auth-logo-dark me-start">
                    </a>
                </div>
                <div class="auth-content my-auto">
                    <div class="text-center">
                        <h4>Verify your email</h4>
                        <p class="mb-5">Please enter the 6 digit code sent to <span class="fw-bold">{{ $email }}</span></p>
                    </div>
                    <form id="otp-form">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <input type="hidden" name="refkey" value="{{ $refkey }}" id="refkey">
                                <input type="hidden" name="email" value="{{ $email }}" id="email">
                                <div class="mb-3 otp-boxes d-flex">
                                    <input class="form-control rounded me-2 otp-input" type="text" id="otp1" maxlength="1" autocomplete="off"/>
                                    <input class="form-control rounded me-2 otp-input" type="text" id="otp2" maxlength="1" autocomplete="off"/>
                                    <input class="form-control rounded me-2 otp-input" type="text" id="otp3" maxlength="1" autocomplete="off"/>
                                    <input class="form-control rounded me-2 otp-input" type="text" id="otp4" maxlength="1" autocomplete="off"/>
                                    <input class="form-control rounded me-2 otp-input" type="text" id="otp5" maxlength="1" autocomplete="off"/>
                                    <input class="form-control rounded me-2 otp-input" type="text" id="otp6" maxlength="1" autocomplete="off"/>
                                </div>
                            </div>
                            <span class="error-otp error"></span>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary w-100 waves-effect waves-light" id="otp-submit" type="submit">Confirm</button>
                        </div>
                    </form>

                    <div class="mt-4 pt-3 text-center">
                        <p class="text-muted mb-0">Didn't receive an email ? 
                            <a href="javascript:void(0)" class="text-primary fw-semibold" id="resend-otp">
                                Resend
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
                   
@endsection

@section('scripts')
<script>
    // |||||||||||||||||||OTP SUBMIT||||||||||||||||
    $(document).ready(function() {
    // Function to handle form submission
    $('#otp-form').submit(function(event) {
        $('#otp-submit').attr('disabled', true);
        event.preventDefault();

        var otpInputs = $('.otp-input');
        var otp = '';
        otpInputs.each(function() {
            otp += $(this).val();
        });
        if (otp.length === 6 && /^\d{6}$/.test(otp)) {
            $('.error-otp').text('');
            var formData = new FormData($(this)[0]);
            formData.append('otp', otp);

            $.ajax({
                url: '{{ url("admin/otp-submit") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.success) {
                        window.location.href = data.redirect;
                    } else {
                        $('.error-otp').text(data.error);
                    }
                    $('#otp-submit').attr('disabled', false);
                },
                error: function(xhr, textStatus, errorThrown) {
                    console.error('There was a problem with the fetch operation:', errorThrown);
                },
                complete: function() {
                    $('#otp-submit').attr('disabled', false);
                }
            });
        } else {
            $('.error-otp').text('Please enter a valid 6-digit OTP.');
            $('#otp-submit').attr('disabled', false);
        }
    });

    $(document).on('keydown', '.otp-input', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            $('#otp-form').submit();
        }
    });
});

    // ||||||||||||||||||||RESEND OTP||||||||||||||||
    $(document).ready(function() {
        $('#resend-otp').click(function() {
            // $(this).off('click');

            var email = $('#email').val();
            var refkey = $('#refkey').val();
            $.ajax({
                url: "{{ route('admin.resendOtp') }}",
                type: "POST",
                data: {
                    email: email,
                    refkey: refkey,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log(response);
                    $('#custom_toast').toast('show');
                    $('#custom_toast .toast-body').text(response.message);
                    $(this).on('click');
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                },
                complete: function() {
                    // $(this).on('click');
                }
            });
        });
    });
    // ||||||||||||||||||||||||||||ON KEY PRESS||||||||||||||||||||||||||||||||\\
    $(document).on('input focus keydown', '.otp-boxes input', function (event) {
        var otpInputs = $('.otp-boxes input');
        var index = otpInputs.index(this);

        if (event.type === 'input') {
            if (this.value.length === this.maxLength && !isNaN(this.value)) {
                if (index < otpInputs.length - 1) {
                    otpInputs.eq(index + 1).focus();
                }
            } else if (this.value.length === 0) {
                if (index > 0) {
                    otpInputs.eq(index - 1).focus();
                }
            } else {
                this.value = ''; // Clear the input if non-numeric characters are entered
            }
        } else if (event.type === 'focus') {
            this.select();
        } else if (event.type === 'keydown') {
            if (!/[0-9]/.test(event.key) && event.key !== 'Backspace') {
                event.preventDefault(); // Prevent entering non-numeric characters
            } else {
                if (event.key === 'ArrowLeft' && index > 0) {
                    otpInputs.eq(index - 1).focus();
                } else if (event.key === 'ArrowRight' && index < otpInputs.length - 1) {
                    otpInputs.eq(index + 1).focus();
                } else if (event.key === 'Backspace' && this.value.length === 0 && index > 0) {
                    otpInputs.eq(index - 1).focus().select(); // Move focus to previous input and select its content
                }
            }
        }
    });
</script>
@endsection