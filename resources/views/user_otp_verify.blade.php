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
                                    {{ translate('Verify phone number to register')}}
                                </h1>
                            </div>
                            
                            <div class="px-5 py-3 py-lg-4">
                                <div class="">
                                    <form class="form-default" role="form" action="{{ route('user.verify_registration') }}" method="POST">
                                        @csrf
                                        
                                        <div class="form-group">
                                            
                                                <input type="text" class="form-control h-auto form-control-lg {{ $errors->has('otp') ? ' is-invalid' : '' }}" value="{{ old('otp') }}" placeholder="{{  translate('OTP') }}" name="otp" autocomplete="off">
                                          @if ($errors->has('otp'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('otp') }}</strong>
                                                </span>
                                            @endif
                                        </div>


                                        <input type="hidden" name="phone" value="{{Session::get('phone')}}">
                                        <input type="hidden" name="name" value="{{Session::get('name')}}">
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-styled btn-base-1 btn-md w-100">{{  translate('Verify OTP') }}</button>
                                        </div>
                                    </form>
                                   
                                </div>
                            </div>
                            <!-- <div class="text-center px-35 pb-3">
                                <p class="text-md">
                                    {{ translate('Need an account?')}} <a href="{{ route('user.registration') }}" class="strong-600">{{ translate('Register Now')}}</a>
                                </p>
                            </div> -->
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </section>
@endsection
