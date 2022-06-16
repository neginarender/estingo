@extends('frontend_new.layouts.app')
@section('content')
     <!-- main content start -->
         <div class="bg-home py-2 py-lg-5">           
            <div class="profile">
                <div class="container">
                    <div class="row  login-signup justify-content-center">
                          <div class="col-md-5 p-0 ">
                            <img class="left-bg" src="{{ static_asset('frontend/new/assets/images/bg.jpg') }}">
                         </div>
                         <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-6 p-0  ">
                            <div class="card">
                                <div class="px-3 px-md-5 pt-4 pt-lg-5">
                                    @if(session()->has('message'))
                                     <h1 class="heading heading-4 strong-600" style="color: red!important;">{{ session('message') }}</h1>
                                    @endif
                                    <h1 class="heading heading-4 strong-600">
                                        Login to your account.
                                    </h1>
                                </div>
                                <div class="px-3 px-lg-5 py-4 py-lg-4">
                                    <div class="">
                                        <form class="form-default mt-2" role="form" action="{{route('userapi.loginuser')}}" method="POST">
                                            <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                            <input type="hidden" name="next" value="{{ $next }}" />
                                                <div class="form-group">
                                                       @error('email')
                                                        <div class="errorc">{{ $message }}</div>
                                                     @enderror
                                                    <input type="email" class="form-control h-auto form-control-lg " value="" placeholder="Email" name="email" value="{{old('email')}}">
                                                </div>

                                                <div class="form-group">
                                                       @error('password')
                                                        <div class="errorc">{{ $message }}</div>
                                                     @enderror
                                                    <input type="password" class="form-control h-auto form-control-lg " placeholder="Password" name="password" value="{{old('password')}}" id="password">
                                                </div>

                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <div class="checkbox pad-btm text-left">
                                                                <input id="demo-form-checkbox" class="magic-checkbox" type="checkbox" name="remember">
                                                                <label for="demo-form-checkbox" class="text-sm">
                                                                    Remember Me
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-right">
                                                        <a href="{{ route('userapi.resetpassword') }}" class="link link-xs link--style-2">Forgot password?</a>
                                                    </div>
                                                </div>

                                          <!--  <div>
                                                <a data-target="#confirm-email" data-toggle="modal" href="#">Click here to verify Email</a>
                                           </div> -->
                                            <div class="text-center col-md-10 mx-auto mt-4">
                                                <button type="submit" class="btn btn-styled btn-base-1 btn-md w-100">Login</button>
                                                <span class="d-block my-3">-OR-</span>
                                               <div class="social-login"> 
                                                     <a href="{{ route('social.login', ['provider' => 'google']) }}" class="btn" data-toggle="tooltip" data-placement="top" title="" data-original-title="Login with Google"><img   src="{{ static_asset('frontend/new/assets/images/google.png') }}" class="google"></a>  
                                                     
                                                     <a href="{{ route('social.login', ['provider' => 'facebook']) }}" class="btn" data-toggle="tooltip" data-placement="top" title="" data-original-title="Login with Facebook"> <img src="{{ static_asset('frontend/new/assets/images/facebook.png') }}"  class="fb" ></a>

                                                     <a class="btn" data-toggle="tooltip" data-placement="top" title="" href="{{ route('userapi.login',['next'=>$next]) }}" data-original-title="Login with Phone"><img src="{{ static_asset('frontend/new/assets/images/mobile.png') }}" ></a> 
                                               </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                                <div class="text-center px-35 pb-3">
                                   <span class="sep"></span>
                                    <p class="text-md text-center mb-0"> Need an account?</p>
                                    <p class="text-md mt-0">
                                         <a href="{{ route('userapi.register',['next'=>$next]) }}" class="strong-600">Register Now</a>
                                         <span class="px-2">|</span>
                                          <a href="{{ route('userapi.login',['next'=>$next]) }}" class="strong-600">Register with Phone</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                   </div>
                </div>
            </div>
        </div>
@endsection

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <!-- Custom style -->
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<script src="{{ static_asset('frontend/new/assets/js/intlTelInput.min.js')}}"></script>
@endsection

