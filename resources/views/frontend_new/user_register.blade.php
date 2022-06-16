@extends('frontend_new.layouts.app')
@section('content')
<div class="bg-home  py-0 py-lg-5  ">           
           <div class="profile">
                <div class="container">
                    <div class="row login-signup justify-content-center">
                        <div class="col-md-5 p-0">
                           <img class="left-bg" src="{{ static_asset('frontend/new/assets/images/bg.jpg') }}">
                        </div>
                         <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-6 p-0  ">
                            <div class="card">
                                <div class="  px-4 px-md-5 pt-5">
                                    <h1 class="heading heading-4 strong-600">
                                        Create an account.
                                    </h1>
                                </div>
                                <div class=" px-4 px-md-5 py-3 py-lg-4">
                                    <div class="">
                                        <form id="reg-form" class="form-default" role="form" action="{{url('new/userregister')}}" method="POST">
                                              <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                            <input type="hidden" name="next" value="{{ $next }}" />
                                              <div class="form-group">
                                                 @error('name')
                                                    <div class="errorc">{{ $message }}</div>
                                                @enderror
                                                <input type="text" class="h-auto form-control-lg form-control" value="{{old('name')}}" placeholder="Name" name="name" >
                                               
                                                </div>
                                                <div class="form-group">
                                                     @error('phone')
                                                      <div class="errorc">{{ $message }}</div>
                                                     @enderror
                                                    <input type="text" class="h-auto form-control-lg form-control" value="{{old('phone')}}" placeholder="Phone" maxlength="10" minlength="10" name="phone">
                                                    
                                                </div>
                                                <div class="form-group">
                                                     @error('email')
                                                      <div class="errorc">{{ $message }}</div>
                                                     @enderror
                                                    <input type="email" class="h-auto form-control-lg form-control" value="{{old('email')}}" placeholder="Email" name="email">
                                                    
                                                 </div>
                                            
                                                 <div class="form-group pass">
                                                  
                                                    <a href="javascript:;"><i class="fa fa-eye"></i> </a>
                                                    <input type="password" class="h-auto form-control-lg form-control" placeholder="Password" name="password" value="{{old('password')}}">
                                                     @error('password')
                                                      <div class="errorc">{{ $message }}</div>
                                                    @enderror
                                                  </div>

                                                <div class="form-group">
                                                
                                                    <input type="password" class="h-auto form-control-lg form-control" placeholder="Confirm Password" name="password_confirmation" value="{{old('password_confirmation')}}">
                                                     @error('password_confirmation')
                                                        <div class="errorc">{{ $message }}</div>
                                                     @enderror
                                                </div>

                                            
                                                <div class="checkbox text-left">
                                                    <input class="magic-checkbox" type="checkbox" name="checkbox_example_1" id="checkboxExample_1a" required="">
                                                    <label for="checkboxExample_1a" class="text-sm">By signing up you agree to our <a href="terms.html" target="_blank">Terms and conditions.</a></label>

                                                </div>

                                                <div class="text-right mt-3">
                                                    <button type="submit" class="btn btn-styled btn-base-1 w-100 btn-md">Create Account</button>
                                                </div>
                                        </form>
                                   </div>
                                </div>
                                <div class="text-center px-35 pb-3">
                                    <p class="text-lg">
                                        Already have an account?  &nbsp;<a href="{{ route('userapi.userlogin',['next'=>$next]) }}" class="strong-600">Log In</a>
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
          </div>
@endsection