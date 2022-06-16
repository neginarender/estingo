@extends('frontend.layouts.app')

@section('content')

    <section class="gry-bg py-4 profile">
        <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-lg-9 mx-auto">
                    <div class="main-content">
                        <!-- Page title -->
                        <div class="page-title">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                        {{ translate('Peer Partner information')}}
                                    </h2>
                                </div>
                                <div class="col-md-6">
                                    <div class="float-md-right">
                                        <ul class="breadcrumb">
                                            <li><a href="{{ route('home') }}">{{ translate('Home')}}</a></li>
                                            <li><a href="{{ route('dashboard') }}">{{ translate('Dashboard')}}</a></li>
                                            <li class="active"><a href="{{ route('peer-partner.create') }}">{{ translate('Create Peer Partner')}}</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form id="shop" class="" action="{{ route('peer-partner.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                          
                                <div class="form-box bg-white mt-4">
                                    <div class="form-box-title px-3 py-2">
                                        {{ translate('Basic Information')}}
                                    </div>
                                    <div class="form-box-content p-3">

                                        <!-- <div class="row">
                                        <div class="col-12">
                                         <div class="form-group">
                                            <label>{{  translate('Peer Type') }}<span style="color: red">*</span></label>
                                                    <select class="form-control" name="peer_type" id="peer_type" required>
                                                        <option value="">Select Peer Type</option>
                                                        <option value="master">Master Peer</option>
                                                        <option value="sub">Sub Peer</option>
                                                    </select>
                                            </div>
                                        </div>
                                      </div>    -->
                                      
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label>{{  translate('Name') }}<span style="color: red">*</span></label>
                                                    <div class="input-group input-group--style-1">
                                                        <input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="@if(Auth::check()) {{ auth()->user()->name}} @endif" placeholder="{{  translate('Name') }}" name="name" required="required">
                                                        <span class="input-group-addon">
                                                            <i class="text-md la la-user"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label>{{  translate('Phone') }}<span style="color: red">*</span></label>
                                                    <div class="input-group input-group--style-1">
                                                        <input type="phone" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" value="@if(Auth::check()) {{ auth()->user()->phone}} @endif" placeholder="{{  translate('Phone') }}" name="phone" required="required" readonly="readonly">
                                                        <span class="input-group-addon">
                                                            <i class="text-md la la-phone"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                   <label>{{  translate('Email') }}<span style="color: red">*</span></label>
                                                    <div class="input-group input-group--style-1">
                                                        <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" value="@if(Auth::check()) {{ auth()->user()->email}} @endif" placeholder="{{  translate('Email') }}" name="email" required="required">
                                                        <span class="input-group-addon">
                                                            <i class="text-md la la-envelope"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                 <label>{{  translate('Address') }}<span style="color: red">*</span></label>
                                                <input type="text" class="form-control mb-3" placeholder="{{ translate('Address')}}" name="address" value="@if(Auth::check() && !is_null(auth()->user()->address)) {{ auth()->user()->address}} @endif" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                 <label>{{  translate('City') }}<span style="color: red">*</span></label>
                                                <input type="text" class="form-control mb-3" placeholder="{{ translate('City')}}" name="addressone" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                 <label>{{  translate('Pin Code') }}<span style="color: red">*</span></label>
                                                <input type="number" class="form-control mb-3 pindata" placeholder="{{ translate('Pin Code')}}" name="addresstwo" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                 <label>{{  translate('Zone') }}<span style="color: red">*</span></label>
                                                <input type="text" class="form-control mb-3 zonedata" placeholder="{{ translate('Zone')}}" name="zone" readonly required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>{{  translate('Referral Code') }}</label>
                                                <input type="hidden" name="parent_id" class="parent_id">
                                                <input type="text" class="form-control mb-3 blank_ref" placeholder="{{ translate('Referral Code')}}" name="referral_code" id="referral_code">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>{{  translate('PAN No.') }}</label>
                                                <input type="text" class="form-control mb-3" placeholder="{{ translate('PAN No.')}}" name="pannumber" id="panNumber">
                                                <p style="font-size: 12px;color:green;margin-top: -10px;">First five characters are letters (A-Z), next 4 numerics (0-9), last character letter (A-Z)</p>
                                            </div>
                                        </div>


                                    </div>

                                     <div class="form-box-title px-3 py-2">
                                        {{ translate('Social Media Information') }}
                                    </div>
                                    <br>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label style="margin-right: 20px;">{{  translate('Do you have Facebook account ? ') }}</label>
                                          <div class="form-check" style="display: inline-block;">
                                              <input class="form-check-input" type="radio" name="fb_account" value="1" id="fbradio1">
                                              <label class="form-check-label" for="flexRadioDefault1" style="margin-right: 30px;">
                                                Yes
                                              </label>
                                            
                                              <input class="form-check-input" type="radio" name="fb_account" id="fbradio2" value="0" checked>
                                              <label class="form-check-label" for="">
                                                No
                                              </label>
                                            </div>
                                        </div>
                                    </div>

                                   <div id="fb_tab" style="display: none;">
                                        <div class="col-12">
                                            <div class="form-group">
                                                 <label style="margin-right: 15px;">{{  translate('Please fill you Facebook Page name ') }}</label>
                                                <div class="input-group input-group--style-1">
                                                    <input type="text" class="form-control" value="" placeholder="{{  translate('Facebook Page name') }}" name="fb_page_name">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                 <label>{{  translate('How many Followers/Likers do you have on you Facebook page') }}</label>
                                                <div class="input-group input-group--style-1">
                                                    <input type="text" class="form-control" value="" placeholder="{{  translate('Followers/Likers') }}" name="fb_follower">
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group">
                                            <label style="margin-right: 15px;">{{  translate('Do you have Instagram account ? ') }}</label>
                                          <div class="form-check" style="display: inline-block;">
                                              <input class="form-check-input" type="radio" name="instagram_account" id="instaradio1" value="1">
                                              <label class="form-check-label" for="1" style="margin-right: 30px;">
                                                Yes
                                              </label>
                                            
                                              <input class="form-check-input" type="radio" name="instagram_account" id="instaradio2" value="0" checked="">
                                              <label class="form-check-label" for="">
                                                No
                                              </label>
                                            </div>
                                        </div>
                                    </div>


                                    <div id="insta_tab" style="display: none;">
                                        <div class="col-12">
                                            <div class="form-group">
                                                 <label>{{  translate('Please fill you Instagram Page name ') }}</label>
                                                <div class="input-group input-group--style-1">
                                                    <input type="text" class="form-control" value="" placeholder="{{ translate('Instagram Page name') }}" name="instagram_page_name">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                 <label>{{  translate('How many Followers/Likers do you have on you Instagram page') }}</label>
                                                <div class="input-group input-group--style-1">
                                                    <input type="text" class="form-control" value="" placeholder="{{  translate('Followers/Likers') }}" name="instagram_follower">
                                                </div>
                                            </div>

                                        </div>
                                    </div>



                                    <div class="col-12">
                                        <div class="form-group">
                                            <label style="margin-right: 32px;">{{  translate('Do you have LinkedIn profile ? ') }}</label>
                                          <div class="form-check" style="display: inline-block;">
                                              <input class="form-check-input" type="radio" name="linkedin_account" id="linkedinradio1" value="1">
                                              <label class="form-check-label" for="1" style="margin-right: 30px;">
                                                Yes
                                              </label>
                                            
                                              <input class="form-check-input" type="radio" name="linkedin_account" id="linkedinradio2" value="0" checked="">
                                              <label class="form-check-label" for="">
                                                No
                                              </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="linkedin_tab" style="display: none;">
                                        <div class="col-12">
                                            <div class="form-group">
                                                 <label>{{  translate('Please fill you LinkedIn Profile name ') }}</label>
                                                <div class="input-group input-group--style-1">
                                                    <input type="text" class="form-control" value="" placeholder="{{  translate('LinkedIn Profile name') }}" name="linkedin_profile_name">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                 <label>{{  translate('How many Followers/Likers do you have on you LinkedIn page') }}</label>
                                                <div class="input-group input-group--style-1">
                                                    <input type="text" class="form-control" value="" placeholder="{{  translate('Followers/Likers') }}" name="linkedin_follower">
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            
    
                            <div class="text-right mt-4">
                                <button type="submit" class="btn btn-styled btn-base-1">{{ translate('Save')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('script')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script type="text/javascript">
     $('#referral_code').on('blur', function() {
        var referral_code = $('#referral_code').val();

        $.post('{{ route('peer_partner.referrals') }}', {_token:'{{ csrf_token() }}', referral_code:referral_code}, function(data){
            // console.log(data);
                if(data != 0){
                    $('.parent_id').val(data);
                    alert('Referral Code Successfully Applied');
                }
                else{
                    var myLengths = $("#referral_code").val().length;
                    if(myLengths!=0){
                        alert('The referral code entered by you does not exist');
                        $('.blank_ref').val('');
                    }    
                }
            });
  }); 

    $('#panNumber').on('blur', function() {
        var panVal = $('#panNumber').val();
        var regpan = /^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/;

        if(regpan.test(panVal)){
           // valid pan card number
        } else {
          var myLength = $("#panNumber").val().length;
          if(myLength!=0){
               alert('Please enter a valid pan number')
               $("#panNumber").val('');
          }     
        }
  }); 
  $('.pindata').on('blur', function() {
        var pin_code = $('.pindata').val();       
        var pinlength = pin_code.toString().length;
        if(pinlength==6){

            $.post('{{ route('home.checkpin') }}', {_token:'{{ csrf_token() }}', pin_code:pin_code}, function(data){
                // console.log(data);
                 if(data != 0){
                    $('.zonedata').val(data);
                 }else{
                     alert('The pin code entered by you does not exist');
                     $('.pindata').val(''); 
                     $('.zonedata').val(''); 
                 }   
            });

        }else{
            alert('Please write correct pincode');
            $('.pindata').val(''); 
            $('.zonedata').val(''); 
        }
  });      
</script>
<script type="text/javascript">
    // making the CAPTCHA  a required field for form submission
    $(document).ready(function(){

        $('#fbradio1').click(function() {
           if($('#fbradio1').is(':checked')) 
            { 
                $("#fb_tab").show();
                $("#fb_tab").find("input").prop('required',true);
            }
        });
        $('#fbradio2').click(function() {
           if($('#fbradio2').is(':checked')) 
            { 
                $("#fb_tab").hide();
                $("#fb_tab").find("input").val("");
                $("#fb_tab").find("input").prop('required',false);
            }
        });

        // Instagram

        $('#instaradio1').click(function() {
           if($('#instaradio1').is(':checked')) 
            { 
                $("#insta_tab").show();
                $("#insta_tab").find("input").prop('required',true);
            }
        });
        $('#instaradio2').click(function() {
           if($('#instaradio2').is(':checked')) 
            { 
                $("#insta_tab").hide();
                $("#fb_tab").find("input").val("");
                $("#insta_tab").find("input").prop('required',false);
            }
        });

        // Linkidin
        $('#linkedinradio1').click(function() {
           if($('#linkedinradio1').is(':checked')) 
            { 
                $("#linkedin_tab").show();
                $("#linkedin_tab").find("input").prop('required',true);
            }
        });
         $('#linkedinradio2').click(function() {
           if($('#linkedinradio2').is(':checked')) 
            { 
                $("#linkedin_tab").hide();
                $("#linkedin_tab").find("input").val("");
                $("#linkedin_tab").find("input").prop('required',false);
            }
        });

        // alert('helloman');
        $("#shop").on("submit", function(evt)
        {
            var response = grecaptcha.getResponse();
            if(response.length == 0)
            {
            //reCaptcha not verified
                alert("please verify you are humann!");
                evt.preventDefault();
                return false;
            }
            //captcha verified
            //do the rest of your validations here
            $("#reg-form").submit();
        });
    });
</script>
@endsection
