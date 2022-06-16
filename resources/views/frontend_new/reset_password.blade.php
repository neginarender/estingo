@extends('frontend_new.layouts.app')
@section('content')

     

        <!-- main content start -->
         <div class="  py-2 py-lg-5 gry-bg">           
           <div class="profile">
            <div class="container">
                 <div class="row login-signup justify-content-center ">
                        <div class="col-md-3 p-0 img-height"  >
                           <img class="left-bg" src="{{ static_asset('frontend/new/assets/images/bg.jpg') }}">
                        </div>
                         <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-5 p-0  ">
                            <div class="card pb-5 m-height1">
                                <div class="text-left px-4 px-md-5 pb-2 pt-5">

                                     @if(session()->has('message'))
                                     <h1 class="heading heading-4 strong-600" style="color: green!important;">{{ session('message') }}</h1>
                                    @endif
                                    <h1 class="heading heading-4 strong-600">
                                        Reset Password
                                    </h1>
                                     @if(session()->has('messagee'))
                                     <h1 class="heading heading-4 strong-600" style="color: red!important;">{{ session('messagee') }}</h1>
                                    @endif
                                    <p>Enter your email address to recover your password.</p>
                                </div>
                                
                                <div class="px-4 px-md-5 py-3 py-lg-4">
                                    <div class="">
                                        <form class="form-default" role="form" action="{{route('userapi.resetpasswordemail')}}" method="POST">
                                             <input type="hidden" name="_token" value="{{csrf_token()}}" />

                                            <div class="form-group">
                                                <input type="email" class="form-control h-auto form-control-lg " value="" placeholder="Enter your email" name="email" required="">
                                             </div>
                                            <div class="text-center mt-4 col-md-10 mx-auto">
                                                <button type="submit" class="btn btn-styled btn-base-1 btn-md w-100"> Send Password Reset Link</button>
                                            </div>
                                        </form>
                                   </div>
                                </div>
                                <div class="text-center px-35 pb-3">
                                    <p class="text-lg">
                                        <a href="login.html" class="strong-600">Back to Login</a>
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

