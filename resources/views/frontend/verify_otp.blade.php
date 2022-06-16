@extends('frontend.layouts.app')

@section('content')
    <section class="gry-bg py-5">
        <div class="profile">
            <div class="container">
                <div class="row">
                    <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-8 mx-auto">
                        <div class="card">
                            <div class="text-center px-35 pt-5">
                                <h1 class="heading heading-4 strong-500">
                                    {{ translate('Login to your account.')}}
                                </h1>
                            </div>
                            
                            <div class="px-5 py-3 py-lg-4">
                                <div class="">
                                    <form class="form-default" role="form" action="{{ route('user.login_phone') }}" method="POST">
                                        @csrf
                                        
                                        <div class="form-group">
                                            
                                                <input type="text" class="form-control h-auto form-control-lg {{ $errors->has('otp') ? ' is-invalid' : '' }}" value="{{ old('otp') }}" placeholder="{{  translate('OTP') }}" name="otp" autocomplete="off">
                                          @if ($errors->has('otp'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('otp') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        

                                        <input type="hidden" id="phone" name="phone" value="{{Session::get('phone')}}">
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-styled btn-base-1 btn-md w-100">{{  translate('Verify OTP') }}</button>
                                        </div>
                                    </form>
                                   
                                </div>
                            </div>
                            <div class="text-center px-35 pb-3">
                                <p class="text-md">
                                    <a href="javascript:void(0);" id="resend_otp" class="strong-600">{{ translate('Resend OTP')}}</a>
                                </p>
                                <p id="text" style class="badge badge-success"></p>
                                <p class="text-md">
                                    {{ translate('Need an account?')}} <a href="{{ route('user.registration') }}" class="strong-600">{{ translate('Register Now')}}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
<script type="text/javascript">
$("#resend_otp").on('click',function(){
    var phone = $("#phone").val();
$.post("{{ route('otp.resend') }}",{phone:phone,_token:"{{ csrf_token() }}"},function(data){
$("#text").html('<div class="spinner-border text-success" role="status"><span class="sr-only">Loading...</span></div>');
$("#text").html(data);
});
});
</script>
@endsection

