<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link name="favicon" type="image/x-icon" href="{{ static_asset(\App\GeneralSetting::first()->favicon) }}" rel="shortcut icon" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!--Bootstrap Stylesheet [ REQUIRED ]-->
    <link href="{{ static_asset('css/bootstrap.min.css')}}" rel="stylesheet">

    <!--active-shop Stylesheet [ REQUIRED ]-->
    <link href="{{ static_asset('css/active-shop.min.css')}}" rel="stylesheet">

    <!--active-shop Premium Icon [ DEMONSTRATION ]-->
    <link href="{{ static_asset('css/demo/active-shop-demo-icons.min.css')}}" rel="stylesheet">

    <!--Font Awesome [ OPTIONAL ]-->
    <link href="{{ static_asset('plugins/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">

    <!--Switchery [ OPTIONAL ]-->
    <link href="{{ static_asset('plugins/switchery/switchery.min.css')}}" rel="stylesheet">

    <!--DataTables [ OPTIONAL ]-->
    <link href="{{ static_asset('plugins/datatables/media/css/dataTables.bootstrap.css') }}" rel="stylesheet">
    <link href="{{ static_asset('plugins/datatables/extensions/Responsive/css/responsive.dataTables.min.css') }}" rel="stylesheet">

    <!--Select2 [ OPTIONAL ]-->
    <link href="{{ static_asset('plugins/select2/css/select2.min.css')}}" rel="stylesheet">

    <link href="{{ static_asset('css/bootstrap-select.min.css')}}" rel="stylesheet">

    <!--Chosen [ OPTIONAL ]-->
    {{-- <link href="{{ static_asset('plugins/chosen/chosen.min.css')}}" rel="stylesheet"> --}}

    <!--Bootstrap Tags Input [ OPTIONAL ]-->
    <link href="{{ static_asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.css') }}" rel="stylesheet">
    <link href="{{ static_asset('plugins/bootstrap-datepicker/bootstrap-datepicker.min.css') }}" rel="stylesheet">
    <!--Summernote [ OPTIONAL ]-->
    <link href="{{ static_asset('css/jodit.min.css') }}" rel="stylesheet">

    <!--Theme [ DEMONSTRATION ]-->
    <!-- <link href="{{ static_asset('css/themes/type-full/theme-dark-full.min.css') }}" rel="stylesheet"> -->
    <link href="{{ static_asset('css/themes/type-c/theme-navy.min.css') }}" rel="stylesheet">

    <!--Spectrum Stylesheet [ REQUIRED ]-->
    <link href="{{ static_asset('css/spectrum.css')}}" rel="stylesheet">

    <!--Custom Stylesheet [ REQUIRED ]-->
    <link href="{{ static_asset('css/custom.css')}}" rel="stylesheet">
    
    
    


    <!--JAVASCRIPT-->
    <!--=================================================-->

    <!--jQuery [ REQUIRED ]-->
    <script src=" {{static_asset('js/jquery.min.js') }}"></script>


    <!--BootstrapJS [ RECOMMENDED ]-->
    <script src="{{ static_asset('js/bootstrap.min.js') }}"></script>


    <!--active-shop [ RECOMMENDED ]-->
    <script src="{{ static_asset('js/active-shop.min.js') }}"></script>

    <!--Alerts [ SAMPLE ]-->
    <script src="{{ static_asset('js/demo/ui-alerts.js') }}"></script>

    <!--Switchery [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/switchery/switchery.min.js')}}"></script>

    <!--DataTables [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/datatables/media/js/jquery.dataTables.js')}}"></script>
    <script src="{{ static_asset('plugins/datatables/media/js/dataTables.bootstrap.js')}}"></script>
    <script src="{{ static_asset('plugins/datatables/extensions/Responsive/js/dataTables.responsive.min.js')}}"></script>

    <!--DataTables Sample [ SAMPLE ]-->
    <script src="{{ static_asset('js/demo/tables-datatables.js')}}"></script>

    <!--Select2 [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/select2/js/select2.min.js')}}"></script>
    <script src="{{ static_asset('js/bootstrap-select.min.js')}}"></script>

    <!--Summernote [ OPTIONAL ]-->
    <script src="{{ static_asset('js/jodit.min.js') }}"></script>

    <!--Bootstrap Tags Input [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js')}}"></script>

    <!--Bootstrap Validator [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/bootstrap-validator/bootstrapValidator.min.js') }}"></script>

    <!--Bootstrap Wizard [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js') }}"></script>

    <!--Bootstrap Datepicker [ OPTIONAL ]-->
    <script src="{{ static_asset('plugins/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>

    <!--Form Component [ SAMPLE ]-->
    <script src="{{static_asset('js/demo/form-wizard.js')}}"></script>

    <!--Spectrum JavaScript [ REQUIRED ]-->
    <script src="{{ static_asset('js/spectrum.js')}}"></script>

    <!--Spartan Image JavaScript [ REQUIRED ]-->
    <script src="{{ static_asset('js/spartan-multi-image-picker-min.js') }}"></script>

    <!--Custom JavaScript [ REQUIRED ]-->
    <script src="{{ static_asset('js/custom.js')}}"></script>

    <script type="text/javascript">

        $( document ).ready(function() {
            //$('div.alert').not('.alert-important').delay(3000).fadeOut(350);
            if($('.active-link').parent().parent().parent().is('ul')){
                $('.active-link').parent().parent().addClass('in');
                $('.active-link').parent().parent().parent().addClass('in');
            }
            if($('.active-link').parent().parent().is('li')){
                $('.active-link').parent().parent().addClass('active-sub');
            }
            if($('.active-link').parent().is('ul')){
                $('.active-link').parent().addClass('in');
            }

            if ($('#lang-change').length > 0) {
                $('#lang-change .dropdown-item a').each(function() {
                    $(this).on('click', function(e){
                        e.preventDefault();
                        var $this = $(this);
                        var locale = $this.data('flag');
                        $.post('{{ route('language.change') }}',{_token:'{{ csrf_token() }}', locale:locale}, function(data){
                            location.reload();
                        });

                    });
                });
            }

        });

    </script>

    <!-- Global site tag (gtag.js) - Google Analytics -->
    @if (\App\BusinessSetting::where('type', 'google_analytics')->first()->value == 1)
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-133955404-1"></script>

        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', @php env('TRACKING_ID') @endphp);
        </script>
    @endif


</head>
<body>

    @foreach (session('flash_notification', collect())->toArray() as $message)
        <script type="text/javascript">
            $(document).on('nifty.ready', function() {
                showAlert('{{ $message['level'] }}', '{{ $message['message'] }}');
            });
        </script>
    @endforeach


    <div id="container" class="effect aside-float aside-bright mainnav-lg">

        @include('inc.admin_nav')

        <div class="boxed">

            <!--CONTENT CONTAINER-->
            <!--===================================================-->
            <div id="content-container">
                <div id="page-content">

                    <div class="row">
    <!--     <div class="col-sm-12">
           <a href="{{ route('peer_partner.createpeer')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add New Peer Discount')}}</a>
           <a href="{{ route('peer_partner.peer_commision')}}" class="btn btn-rounded btn-info pull-right" style="margin-right: 6px">{{translate('View All Peer Commission')}}</a>
        </div> -->
    </div>

    <br>

    <style>
    .error{
        color: red;
    }
</style>


<div class="row">
    <div class="col-lg-8 col-lg-offset-2">
             <div class="panel-heading bord-btm clearfix pad-all h-100">
            <div class="pull-right clearfix">
                <!-- <a href="{{ route('peer_partner.export') }}" class="btn btn-rounded btn-info pull-right" style="float:left;margin-right: 30px;" target="blank">{{translate('Export')}}</a> -->
              <a href="{{route('callceter.callcetertbl')}}" class="btn btn-primary">List Of Users
</a>

            </div>
        </div>
        <form class="form form-horizontal mar-top" action="{{route('callceter.store_partner')}}" method="POST" enctype="multipart/form-data" id="choice_form">
            @csrf
            <input type="hidden" name="added_by" value="admin">
            <input type="hidden" name="parent_id" class="parent_id" />
            <div class="panel">
                <div class="panel-heading bord-btm">
                    <h3 class="panel-title">{{translate('User Information')}}</h3>
                </div>
                <div class="panel-body">

                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{translate('Name')}} <span class="error">*</span></label>
                        <div class="col-lg-7">
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="{{ translate('Name') }}">
                            
                            @if($errors->has('name'))
                            <div class="error  mr-top">{{ $errors->first('name') }}</div>
                            @endif
                        </div>
                        
                    </div>
                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{translate('Phone')}} <span class="error">*</span></label>
                        <div class="col-lg-7">
                            <input type="number" class="form-control"  name="phone" value="{{ old('phone') }}" placeholder="{{ translate('Phone') }}" required>
                            
                            @if($errors->has('phone'))
                            <div class="error  mr-top">{{ $errors->first('phone') }}</div>
                            @endif
                        </div>
                        
                    </div>
                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{translate('Email')}}</label>
                        <div class="col-lg-7">
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="{{ translate('Email') }}">
                            
                            @if($errors->has('email'))
                            <div class="error  mr-top">{{ $errors->first('email') }}</div>
                            @endif
                        </div>
                        
                    </div>
                    <div class="form-group" id="subsubcategory">
                        <label class="col-lg-2 control-label">{{translate('Address')}} <span class="error">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" name="address" value="{{ old('address') }}" placeholder="{{ translate('Address') }}">
                        
                        @if($errors->has('address'))
                            <div class="error  mr-top">{{ $errors->first('address') }}</div>
                        @endif
                        </div>
                        
                    </div>
                    @php 
                    
                    $clusters = \App\Cluster::where('status',1)->select('state_id')->get();
                    $state_ids = [];

                    foreach($clusters as $key => $cluster){
                        foreach(json_decode($cluster->state_id) as $kk => $state){
                            $state_ids[] =$state;
                        }
                        
                    }

                    @endphp
                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{translate('State')}} <span class="error">*</span></label>
                        <div class="col-lg-7">
                            <input type="hidden" name="state" id="state" value="" />
                            <select class="form-control demo-select2 state_id" name="state_id" id="state_id" onchange="loadList(this)">
                                <option value="">Select State</option>
                                @foreach(\App\State::where('status',1)->where('country_id',99)->whereIn('id',array_unique($state_ids))->get() as $key => $state)
                                    <option value="{{ $state->id }}" @if(old('state')==$state->id) selected @endif>{{ $state->name }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('state'))
                            <div class="error  mr-top">{{ $errors->first('state') }}</div>
                            @endif
                        </div>
                        
                    </div>

                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{translate('City/District')}} <span class="error">*</span></label>
                        <div class="col-lg-7">
                        <input type="hidden" name="city" id="city" value="" />
                        <select class="form-control demo-select2 city_id" name="city_id" id="city_id" onchange="loadList(this)">
                                <option value="">Select City/District</option>
                                
                            </select>
                            @if($errors->has('city'))
                            <div class="error  mr-top">{{ $errors->first('city') }}</div>
                            @endif
                        </div>
                       
                    </div>

                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{translate('Block/Taaluka')}} 
                            <span class="error">*</span>
                        </label>
                        <div class="col-lg-7">
                        <input type="hidden" name="block" id="block" value="" />
                        <select class="form-control demo-select2" name="block_id" id="block_id" onchange="loadList(this)">
                                <option value="">Select Block</option>
                            </select>
                            @if($errors->has('block_id'))
                            <div class="error  mr-top">{{ $errors->first('block_id') }}</div>
                            @endif
                        </div>
                       
                    </div>

                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{translate('Gram Panchayat')}} 
                            <!-- <span class="error">*</span> -->
                        </label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" name="village" placeholder="Gram Panchayat" />
                            @if($errors->has('village'))
                            <div class="error  mr-top">{{ $errors->first('village') }}</div>
                            @endif
                        </div>
                       
                    </div>
                    
                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{translate('Pincode')}} <span class="error">*</span></label>
                        <div class="col-lg-7">
                            <select class="form-control pindata demo-select2" name="pincode" id="pincode_id">
                                <option value="">Select Pincode</option>
                            </select>
                            
                            @if($errors->has('pincode'))
                            <div class="error mr-top">{{ $errors->first('pincode') }}</div>
                             @endif
                        </div>
                        
                    </div>
                    
                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{translate('Zone')}}</label>
                        <div class="col-lg-7">
                            <input type="text" class="form-control zonedata" id="zone" name="zone" placeholder="{{ translate('Zone') }}" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{translate('Refferal Code')}}</label>
                        <div class="col-lg-7">
                            <input type="text" class="form-control blank_ref" id="referral_code" name="referral_code" placeholder="{{ translate('Refferal Code') }}">
                            
                        </div>
                        
                    </div>
                    <div class="form-group">
                                           
                    <label class="col-lg-2 control-label">{{  translate('PAN No.') }}</label>
                    
                    <div class="col-lg-7">
                    <input type="text" class="form-control mb-3" placeholder="{{ translate('PAN No.')}}" name="pannumber" id="panNumber">
                    <br />
                    <p style="font-size: 12px;color:green;margin-top: -10px;">First five characters are letters (A-Z), next 4 numerics (0-9), last character letter (A-Z)</p>
                    </div>
                    @if($errors->has('pannumber'))
                        <div class="error">{{ $errors->first('pannumber') }}</div>
                    @endif
                    </div>

                </div>
            </div>
            
            <!-- <div class="panel">
                <div class="panel-heading bord-btm">
                    <h3 class="panel-title">{{translate('Social Media Information')}}</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{translate('Do you have Facebook account ?')}}</label>
                        <div class="col-lg-7">
                            <select name="fb_account" class="form-control">
                                <option value="1">Yes</option>
                                <option value="0" selected>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{translate('Do you have Instagram account ?')}}</label>
                        <div class="col-lg-7">
                        <select name="instagram_account" class="form-control">
                                <option value="1">Yes</option>
                                <option value="0" selected>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-2 control-label">{{ translate('Do you have LinkedIn profile ?') }}</label>
                        <div class="col-lg-7">
                        <select name="linkedin_account" class="form-control">
                                <option value="1">Yes</option>
                                <option value="0" selected>No</option>
                            </select>

                            </div>
                        </div>
                </div>
            </div> -->
            <div class="mar-all text-right">
                <button type="submit" name="button" class="btn btn-info">{{ translate('Add Partner') }}</button>
            </div>
        </form>
    </div>
</div>

@section('script')
<script type="text/javascript">
    $(document).ready(function(){
        $('#referral_code').on('blur', function() {
        var referral_code = $('#referral_code').val();

        $.post("{{ route('peer_partner.referrals') }}", {_token:'{{ csrf_token() }}', referral_code:referral_code}, function(data){
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
    });

    $('.pindata').on('change', function() {
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

  function loadList(el){
    var id =$(el).attr('id');
    $("#"+id).prev("input").val($("#"+id+" option:selected").text());
    var url = "";

    var keyval = $(el).val();
    if(id=="state_id"){
        url = "{{ route('citylist') }}";
        data = {state_id:keyval};
        var loadid = "city_id";
    }

    if(id=="city_id"){
        url = "{{ route('blocklist') }}";
        data = {city_id:keyval};
        var loadid = "block_id";
    }

    if(id=="block_id"){
        url = "{{ route('pincodelist') }}";
        data = {block_id:keyval};
        var loadid = "pincode_id";
    }

    $.ajax({
    url: url,
    type: "get", //send it through get method
    data: data,
    success: function(response) {
        //Do Something
        $("#"+loadid).empty();
        $("#"+loadid).append("<option value=''>Select</option>");
        $.map(response.state.data,function(item){
            if(id=="city_id"){
                $("#"+loadid).append("<option value="+item.block_id+">"+item.name+"</option>");
            }
            if(id=="state_id"){
                //console.log(item.name);
                $("#"+loadid).append("<option value="+item.city_id+">"+item.name+"</option>");
            }

            if(id=="block_id"){
                //console.log(item.name);
                $("#"+loadid).append("<option value="+item.pincode+">"+item.pincode+"</option>");
            }
            
            });
            $('.demo-select2').select2();
    },
  error: function(xhr) {
    //Do Something to handle error
    console.log(xhr);
  }
});
  }
</script>
        @include('inc.admin_footer')

        @include('partials.modal')

        @yield('modal')

    </div>

    @yield('script')

</body>
</html>
