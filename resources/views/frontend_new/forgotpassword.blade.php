@extends('frontend_new.layouts.app')
@section('content')
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
                                    <h1 class="heading heading-4 strong-600">
                                            @if(session()->has('message'))
                                     <h1 class="heading heading-4 strong-600" style="color: green!important;">{{ session('message') }} </h1>

                                     @else
                                     <h1 class="heading heading-4 strong-600" style="color: green!important;">{{ session('status') }} </h1>
                                    @endif

                                    </h1>
                                      <div class="registrationFormAlert" style="color:red;" id="CheckPasswordMatch">


                                </div>
                                
                                <div class="px-4 px-md-5 py-3 py-lg-4">
                                    <div class="">
                                        <form class="form-default" role="form" action="{{ route('userapi.forgoypassword') }}" method="POST"> 
                                            <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                            <input type="hidden" name="id" value="{{$user_id}}" />
                                            <div class="form-group">
                                                <input type="password" class="form-control h-auto form-control-lg " value="" placeholder="Enter password" id="txtNewPassword" name="password" required="">
                                             </div>
                                                 <div class="form-group">
                                                <input type="password" class="form-control h-auto form-control-lg " value="" placeholder="Enter Password Confirmation" id="txtConfirmPassword" name="password_confirmation" required="">
                                             </div>
                                            <div class="text-center mt-4 col-md-10 mx-auto">
                                                <button type="submit" class="btn btn-styled btn-base-1 btn-md w-100" >Submit</button>

                                            </div>
                                        </form>
                                    
                                   </div>
                                </div>
                                <div class="text-center px-35 pb-3">
                                    <p class="text-lg">
                                        Need an account? <a href="" class="strong-600">Register Now</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                   </div>
                </div>
        </div>
</div>
   @section('script')


    <script>
    function checkPasswordMatch() {
        var password = $("#txtNewPassword").val();
        var confirmPassword = $("#txtConfirmPassword").val();
        if (password != confirmPassword)
            $("#CheckPasswordMatch").html("Passwords does not match!");
        else
            $("#CheckPasswordMatch").html("Passwords match.");
    }
    $(document).ready(function () {
       $("#txtConfirmPassword").keyup(checkPasswordMatch);
    });
    </script>
@endsection
@endsection
