@php
$shortId = [];
    if(Cookie::has('sid')){
        $shortId['sorting_hub_id'] = decrypt(Cookie::get('sid'));
    }
    else{
            if(Cookie::has('pincode')){
                $pincode = Cookie::get('pincode');
                $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')
                ->selectRaw('user_id as sorting_hub_id')
                ->first('sorting_hub_id');
            }
            
        }
$area_pincodes = \App\ShortingHub::select('area_pincodes')->where('status',1)->get()->toArray();

        $mapped_pincode = array();

        if(count($area_pincodes) > 0){
            foreach ($area_pincodes as $key => $pincodes) {
                foreach ($pincodes as $key => $ids) {
                    foreach (json_decode($ids) as $key => $id) {
                        $mapped_pincode[] = $id;
                    }
                }
            }
        }
       
$area = \App\Area::select('district_id')->whereIn('pincode',$mapped_pincode)->groupBy('district_id')->get()->toArray();

        $cities = \App\City::whereIn('id', $area)->get();
       
@endphp
 <div class="locationss" id="options">
    <a class="close" href="javascript:;">x</a>
   <div class="detect_loc mb-4">
     <a href="javascript:void(0);" onclick="detectLocation();"><img src="{{static_asset('frontend/images/target.png')}}">
     <!-- <a href="javascript:void(0);" onclick="detectLocation();"><img src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"> -->
     <p>Detect Current Location <span>Using GPS</span></p> </a>
   </div>
    <form class="form-horizontal" action="{{ route('addresses.set_location') }}" method="POST" >
        @csrf
    <div class="pincode">
        <p>Select your location </p>
<div class="mb-3">
        <select class="city demo-select2" name="city">
        <option value="">Select City</option>
        @foreach($cities as $key => $city)
        <option value="{{ $city->id }}">{{ $city->name }}</option>
        @endforeach
        </select>
         </div>
        <div class="mb-3">
          <select class="area_id demo-select2" name="pin">
              
          </select>
        </div>
         <button class="loc-btn" type="submit">Continue</button>
        
    </div>
    </form>
</div>
<style type="text/css">
  .top-navbar {background: #f3f3f3;padding: 10px 0; height: 40px;}
  .top-navbar .loc {position: relative; right: auto;}
  .top-navbar .loc i {font-size:18px; float: left; color: #333}
  .nav-cart-box img {width: 18px;}
  .nav-cart-box {background: none}
  .nav-box-text {font-size: 13px; font-weight: 600; color: #333!important}
  .nav-box-number {color: #009245!important; margin-right: 10px}
  .category-menu-icon img{max-width: 13px; float: left; margin-top: 9px; margin-right: 5px}
  .logo-bar-area .navbar-brand img {margin-left: -20px;}
  .logo-bar-area .navbar-brand {margin-top: 10px}
  .nav-search-box {margin-top: 0; padding: 0 10px; width: 96%}
  .logo-bar-area {padding: 0px 0 10px}
  .hover-category-menu {top: 75%; left: 20%; width: 80%}
  .referral {width: 100%; border: solid 1px #999;padding: 0; position: relative; height: 35px; border-radius: 2px; padding: 0!important}
  .referral span{width: 45%; background: #F3C42F; padding: 2px 5px; font-size: 10px; color: #000; position: absolute; line-height: 14px; top: 0; height: 33px}
  .referral input{border:0; width: 100%; margin-left: 10px; background: transparent; height: 35px;}
  .navbar-light .navbar-nav input,  .logo-bar-area .referral input{border:0; width: 55%; margin-left: 46%; background: transparent; height: 35px;}
  .referral input::placeholder {font-size: 12px;}
  .navbar-light .navbar-nav .btn, .referral .btn {right: -4px; color: #000; bottom: 7px}
   .sm-fixed-top {top: -113px!important;}
        .sm-fixed-top .top-navbar{margin-bottom: 0px}
        .logo-bar-area.sm-fixed-top .navbar-brand {
            margin-top: 5px;
        }

        /*footer*/

        .fixed-bottom {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 1030;
        }
        .footer, .footer .footer-bottom {padding-bottom: 55px}
         .location-landing,  .location-landing.show {position: fixed; width: 100%; height: 75%; background: #fff;left: 0; top: -75%; animation: drop ease .5s forwards;z-index: 999999; opacity: 1; visibility: visible;}
    @keyframes drop{
        0%{}
        100%{top:0}
    }
     .ovrlay-landing {background: rgba(0,0,0,.5); position: fixed; top: 0; left: 0; z-index: 9999; width:  100%; height: 100%; animation: ovrlay ease .5s forwards; opacity: 0}
    @keyframes ovrlay{
    0%{}
    100%{opacity: 1}
    }
    .location-landing.hide {animation: up ease .5s forwards; opacity: 0; visibility: hidden;}
     @keyframes up{
        0%{}
        100%{top:-75%}
    }
    .location-landing .left{width: 60% ;float: left; padding: 20px 100px;}
    .location-landing .right{width: 40%; float: left; overflow: hidden; position: relative; height: 100%}
    .location-landing .right img{object-fit: cover; width: 100%; height: 100%; position: absolute;}
 
    .location-landing .left h1{font-weight: 800; font-family: 'Open Sans';  margin-bottom: 3px; margin-top: 40px}
    .location-landing .left p {font-size: 14px;}
    .location-landing .left .box {position: relative;margin-top: 25px}
    .location-landing .left .box .txtbox {width: 100%; height: 50px; border:solid 1px #999; padding-left: 40px; font-size: 16px}
  .location-landing .left .box  img{position: absolute; top: 13px; color: #999; left: 10px; max-width: 15px; opacity: .7;z-index: 999 }

  .location-landing .left .box .btnn {width: 100%; height: 50px; border:solid 0px #999; padding-left: 40px; font-size: 14px; width: 35%; position: absolute; right: 0; background: #497B02; color: #fff;z-index: 999 }
  .location-landing .left .box .btnn img {max-width: 18px; position: absolute;top: 15px; opacity: 1; left: 15px}

  .location-landing .left .bottom {position: absolute; bottom: 15px;}
  .location-landing .left .bottom h5{font-weight: 700; font-size: 16px; margin-bottom: 5px}
  .location-landing .left .bottom p{  font-size: 14px;}
  .location-landing .left .bottom p  a {padding: 0 10px; border-right: solid 1px #ccc; color: #999}
   .location-landing .left .bottom p  a.active {color: #555}
  .location-landing .left .bottom p  a:first-child {padding-left: 0}
  .location-landing .left .bottom p  a:last-child {border:0;}
  .trend-category-single .img img {height: 70px}
  .trend-category-single .img {min-height: 70px;}
  .location-landing .select2-container {z-index: 99}
  .location-landing .select2-container--default .select2-selection--single {width: 100%; height: 50px; border:solid 1px #999; padding-left: 40px; font-size: 16px}
  .location-landing .select2-container--default .select2-selection--single .select2-selection__rendered {padding: 0.9rem 0.1rem}
  .location-landing .close_btn {position: absolute; right: 10px; top: 10px; background: #aaa; color: #fff; border-radius: 20px; width: 25px; height: 25px; display: block; line-height: 22px; text-align: center; z-index: 999}
       .search form {margin-block-end: 0!important;}
     .news {height: 30px; color: #009245; font-size: 16px; font-weight: 700; background: #eee; padding: 3px 0;  overflow: hidden;box-sizing: border-box; white-space: nowrap;}
    
    .news p {
        display:  block;
        padding-left: 100%;
        animation: marquee 18s linear infinite; font-size: 16px; 
    }    
  @media (max-width: 800px){
    .news  {padding: 4px 0; margin-top: -10px}
        .news p {animation: marquee 10s linear infinite;}
    .logo-bar-area .navbar-brand img {
          margin-left: 0;
      }
     .top-navbar {padding: 5px 0; height: 35px}
     .myCart {top: 22px!important}
     .nav-cart-box img {max-width: 16px; margin-top: -1px}
     .nav-search-box {width: 100%; padding: 0}
     .top-navbar .loc {left: 0!important}
     .sm-fixed-top .top-navbar {margin-bottom: 0; padding-bottom: 0}
     .sm-fixed-top {top: -158px!important;}
     .logo-bar-area .navbar-brand{margin-top: 2px} 
     .mobile-menu-icon-box {padding: 0}
     .logo-bar-area .navbar-brand, .logo-bar-area.sm-fixed-top .navbar-brand {
          margin-top: 0;
      }
      .sm-fixed-top .search-box-mob {
          margin-top: 10px;
      }
      .referral {margin-top: 0; height: 32px}
      .referral span {line-height: 12px; height: 30px; width: 42%;}
       .referral input  {height: 31px!important}
      .hamburger-icon span:nth-child(2), .hamburger-icon span:nth-child(3) {
          top: 7px;
      }
      .hamburger-icon span:nth-child(4) {
            top: 14px;
        }
        .location-landing .select2-container--default .select2-selection--single {height: 35px; padding-left: 35px; font-size: 14px}
        .location-landing .select2-container--default .select2-selection--single .select2-selection__rendered {
              padding: 0.5rem 0.1rem;
          }
          .location-landing, .location-landing.show {height: 100%;}
        .location-landing .left .box img {max-width: 10px; top: 10px}
       .location-landing .left{width: 100% ;float: none; padding: 10px 20px  ;}
      .location-landing .left .logo {max-width: 80px; margin-top: -10px }
      .location-landing .right{width: 100%; float: none; overflow: hidden; position: relative; height: 350px; margin-top: 25px}
      .location-landing .left h1{ font-size: 16px; margin-top: 5px; margin-bottom: 0 }
      .location-landing .left p {
            font-size: 11px;
        }
        .location-landing .left .box {margin-top: 0}
      .location-landing .left .box  div {position: relative;}
      .location-landing .left   .btnn {position: relative; height: auto; margin-top: 20px; color: #497B02 ;width: 100% ; text-align: left; background: none; font-weight: 600;   padding: 0; margin-bottom: 25px;  border: 0; background: none}
        
       .location-landing .left  .btnn img {max-width: 13px; top: 0; position: relative;   left: 0; margin-right: 3px; margin-top: -2px}
       .location-landing .left .bottom {position: relative; bottom: 0; margin-top: 40px}
       .location-landing .left .bottom p a {padding: 0 5px; font-size: 12px;display: inline-block;}
       .location-landing .close_btn {opacity: .8}

  }
</style>
<!-- locaiton popup -->
<div class="location-landing pincode">
  <a href="javascript:;" class="close_btn">x</a>
     <div class="left">
        <img src="{{static_asset('frontend/images/rozana.png')}}" class="logo"  alt="">
        <img src="{{ static_asset('frontend/img/spinnig_arrow.gif') }}"  alt="" id = "spinnig_arrow"  style = "display:none">
        <h1>Pick a City</h1>
        <p>To find awesome offers around you. </p>

        <div class="box mb-2 mb-md-3">
            <button class="btnn d-none d-md-block" onclick = "detectLocation()"><img class="d-none d-md-block lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}"  data-src="{{ static_asset('frontend/images/icon-loc.png') }}"  alt="">
             <!-- <button class="btnn d-none d-md-block" onclick = "detectLocation()"><img class="d-none d-md-block lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"  data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"  alt=""> -->
             <img class="d-inline d-md-none lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{ static_asset('frontend/images/icon-loc-mob.png') }}"  alt="">
             <!-- <img class="d-inline d-md-none lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"  alt=""> -->
                Use my current location
            </button>
            <div>
            <!-- <img class="lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"  data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"  alt=""> -->
           <img class="lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}"  data-src="{{ static_asset('frontend/images/icon-map.png') }}"  alt="">
            <!-- <input type="text" class="txtbox" name="" placeholder="Enter your city name"> -->
           
            <select class="city demo-select2"  id = "city_id">
                <option value="">Select City</option>
                 @foreach($cities as $key => $city)
                <option value="{{ $city->id }}">{{ $city->name }}</option>
                @endforeach
                 
           </select> </div>
        </div>
         <select class="area_id demo-select2">
                 
           </select>

            <button class="btnn d-block d-md-none" onclick = "detectLocation()"><img class="d-none d-md-block lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}"  data-src="{{ static_asset('frontend/images/icon-loc.webp') }}"  alt=""><img class="d-inline d-md-none"  src="{{ static_asset('frontend/images/icon-loc-mob.png') }}"  alt="">
             <!-- <button class="btnn d-block d-md-none" onclick = "detectLocation()"><img class="d-none d-md-block lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"  data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"  alt=""><img class="d-inline d-md-none"  src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"  alt=""> -->
                Use my current location
            </button>

        {{-- <div class="bottom">
            <h5>Popular Cities</h5>
            <p><a href="#" class="active">Lucknow</a> <a href="#" class="active">New Delhi</a> <a href="#">Noida</a> <a href="#">Gurgaon</a><a href="#">Pune</a><a href="#">Banglore</a><a href="#">Hyderabad</a></p>
        </div> --}}
     </div>  
     <div class="right">
      
             <img class="lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}"  data-src="{{ static_asset('frontend/images/location-img.webp') }}"  alt="">
             <!-- <img class="lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"  data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"  alt=""> -->
     </div> 

        
</div>
<div class="ovrlay-landing" ></div>

<!-- end locaiton popup -->



 
<div class="overly"></div>
<div class="overrlay"></div>

<div class="header bg-white">
 

    
       
      <!--  <div class="d-inline-block float-right login  "> 
            <img src="{{ static_asset('frontend/images/homepage/login.png') }}" alt="{{ env('APP_NAME') }}">
            @auth 
              <a href="{{ route('logout')}}" class="top-bar-item">{{ translate('Logout')}}</a>
            @else
            <a href="{{ route('user.login') }}" class="top-bar-item">{{ translate('Login')}}</a><span>|</span>
            <a href="{{ route('user.registration') }}" class="top-bar-item">{{ translate('Registration')}}</a>
            @endauth
        </div> -->
    <!-- END Top Bar -->

    <!-- mobile menu -->
    <div class="mobile-side-menu d-lg-none">
        <div class="side-menu-overlay opacity-0" onclick="sideMenuClose()"></div>
        <div class="side-menu-wrap opacity-0">
            <div class="side-menu closed">
                <div class="side-menu-header ">
                    <div class="side-menu-close" onclick="sideMenuClose()">
                        <i class="la la-close"></i>
                    </div>

                    @auth
                        <div class="widget-profile-box px-3 py-4 d-flex align-items-center">
                            @if (Auth::user()->avatar_original != null)
                                <div class="image " style="background-image:url('{{ my_asset(Auth::user()->avatar_original) }}')"></div>
                            @else
                                <div class="image " style="background-image:url('{{ static_asset('frontend/images/user.png') }}')"></div>
                            @endif

                            <div class="name">{{ Auth::user()->name }}
                            @if(Auth::user()->user_type == 'partner' && Auth::user()->peer_partner == '1' && auth::user()->partner->verification_status == 1)
                              <p class="mb-0">Peer Partner</p>
                              <p class="mb-0">Peer Partner Code:{{Auth::user()->partner->code}}</p>
                              <p class="mb-0"> <a class="text-white  text-small" href="https://api.whatsapp.com/send?&text=Greetings! Treat yourself to amazing year round discounts for all your grocery needs! Just enter my peer partner discount code {{Auth::user()->partner->code}} on the homepage only on rozana.in">
                                <img class="lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{ static_asset('frontend/images/whatsapp.png') }}" class="img-width"></a> | 
                                    <!-- <img class="lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" class="img-width"></a> |-->
                                       <a href="mailto:?subject=I wanted you to see this site&amp;body=Greetings! Treat yourself to amazing year round discounts for all your grocery needs! Just enter my peer partner discount code {{Auth::user()->partner->code}} on the homepage only on rozana.in"
                                     title="Share by Email">
                                   <img class="lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="http://png-2.findicons.com/files/icons/573/must_have/48/mail.png" class="img-width-20">
                                    <!--  <img class="lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" data-src="http://png-2.findicons.com/files/icons/573/must_have/48/mail.png" class="img-width-20"> -->
                                  </a> </p>
                            @endif

                            </div>
                        </div>
                        <div class="side-login px-3 pb-3">
                            <a href="{{ route('logout') }}">Sign Out</a>
                        </div>
                    @else
                        <div class="widget-profile-box px-3 py-4 d-flex align-items-center">
                                <div class="image " style="background-image:url('{{ static_asset('frontend/images/icons/user-placeholder.jpg') }}')"></div>
                        </div>
                        <div class="side-login px-3 pb-3">
                            <a href="{{ route('user.login') }}">Sign In</a>
                            <a href="{{ route('user.registration') }}">Registration</a>
                             
                        </div>
                    @endauth
                </div>
                <div class="side-menu-list px-3">
                    <ul class="side-user-menu">
                        <li>
                            <a href="{{ route('home') }}">
                                <i class="la la-home"></i>
                                <span>Home</span>
                            </a>
                        </li>
                       <li>
                            <a href="{{ url('/') }}/categories">
                                <i class="la la-th-large"></i>
                                <span>Shop by category</span>
                            </a>
                        </li>
                        <!--
                        <li>
                            <a href="{{ route('home') }}">
                                <i class="la la-home"></i>
                                <span>{{translate('Brands')}}</span>
                            </a>
                        </li>
                          <li>
                            <a href="{{ route('home') }}">
                                <i class="la la-home"></i>
                                <span>{{translate('Who we are')}}</span>
                            </a>
                        </li> -->


                        <li>
                            <a href="{{ route('dashboard') }}">
                                <i class="la la-dashboard"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('purchase_history.index') }}">
                                <i class="la la-file-text"></i>
                                <span>Purchase History</span>
                            </a>
                        </li>
                        @if(@Auth::user()->user_type == 'partner' && Auth::user()->peer_partner == 1 && auth::user()->partner->verification_status == 1)
                        <li>
                            <a href="{{ route('partner.referral.history') }}" class="{{ areActiveRoutesHome(['partner.referral.history'])}}">
                                <i class="la la-file-text"></i>
                                <span class="category-name">
                                    Peer Partner Referrals
                                </span>
                            </a>
                        </li>
                @endif
                        @auth
                            @php
                                $conversation = \App\Conversation::where('sender_id', Auth::user()->id)->where('sender_viewed', '1')->get();
                            @endphp
                            @if (\App\BusinessSetting::where('type', 'conversation_system')->first()->value == 1)
                                <li>
                                    <a href="{{ route('conversations.index') }}" class="{{ areActiveRoutesHome(['conversations.index', 'conversations.show'])}}">
                                        <i class="la la-comment"></i>
                                        <span class="category-name">
                                            Conversations
                                            @if (count($conversation) > 0)
                                                <span class="ml-2" class="color-green"><strong>({{ count($conversation) }})</strong></span>
                                            @endif
                                        </span>
                                    </a>
                                </li>
                            @endif
                        @endauth
                        <!-- <li>
                            <a href="{{ route('compare') }}">
                                <i class="la la-refresh"></i>
                                <span>{{translate('Compare')}}</span>
                                @if(Session::has('compare'))
                                    <span class="badge" id="compare_items_sidenav">{{ count(Session::get('compare'))}}</span>
                                @else
                                    <span class="badge" id="compare_items_sidenav">0</span>
                                @endif
                            </a>
                        </li> -->
                        <li>
                            <a href="{{ route('cart') }}">
                                <i class="la la-shopping-cart"></i>
                                <span>Cart</span>
                                @if(Session::has('cart'))
                                    <span class="badge" id="cart_items_sidenav">{{ count(Session::get('cart'))}}</span>
                                @else
                                    <span class="nav-box-text  d-xl-inline-block" id="cart_items_sidenav">0</span>
                                @endif
                            </a>
                        </li>
                        <!-- <li>
                            <a href="{{ route('wishlists.index') }}">
                                <i class="la la-heart-o"></i>
                                <span>Wishlist</span>
                            </a>
                        </li> -->

                     

                        @if (\App\BusinessSetting::where('type', 'wallet_system')->first()->value == 1)
                            <li>
                                <a href="{{ route('wallet.index') }}">
                                    <i class="la la-dollar"></i>
                                    <span>My Wallet</span>
                                </a>
                            </li>
                        @endif

                        <li>
                            <a href="{{ route('profile') }}">
                                <i class="la la-user"></i>
                                <span>Manage Profile</span>
                            </a>
                        </li>


                        

                        <li>
                            <a href="{{ route('support_ticket.index') }}" class="{{ areActiveRoutesHome(['support_ticket.index', 'support_ticket.show'])}}">
                                <i class="la la-support"></i>
                                <span class="category-name">
                                    Support Ticket
                                </span>
                            </a>
                        </li>

                          <li>
                            <a href="{{ url('/') }}/contact-us">
                                <i class="la la-phone"></i>
                                <span>Contact Us</span>
                            </a>
                        </li>

                    </ul>
                    
                </div>
            </div>
        </div>
    </div>
    <!-- end mobile menu -->

    <div class="position-relative logo-bar-area">
       <div class="top-navbar">
        <div class="container">
          <div class="row">
                <div class="col-lg-7 col text-left  ">
                     <!-- <a href="javascript:;" onclick="open_location_modal()" style="margin-right: 285px;"><i class="fa fa-map-marker"></i></a>  -->
                     <!-- <a href="https://api.whatsapp.com/send?phone=+919667018020" class="whatsapp d-none d-md-inline"><i class="fa fa-whatsapp"></i> Order By Whatsapp</a> -->
                     <a href="javascript:;" class="loc"><i class="fa fa-map-marker"></i>
                        @if(!empty(Cookie::get('pincode'))) 
                        {!! Cookie::get('pincode') !!},{!! Cookie::get('state') !!} 
                        @else
                        Select Location
                        @endif
                      <span class="fa fa-angle-down"></span></a>
                  </div>
                
                  <div class="col-lg-3 col text-right  ">
                      <div class="login-opt ">
                            <a  href="#" class="login-btn" ><i class="fa fa-user-o">
                          </i> 
                          Hello! 
                          @if(Auth::check())
                          @php
                         /* $splitname = explode(" ", auth()->user()->name);
                          if(count($splitname) > 1) {
                          $lastname = array_pop($splitname);
                          $firstname = implode(" ", $splitname);
                          }
                              else
                              {
                                  $firstname = auth()->user()->name;
                                  $lastname = " ";
                          }*/
                          echo ucfirst(strtok(Auth::user()->name, " "));
                          @endphp
                          @else
                            Sign in
                           @endif
                          
                          <i class="fa fa-angle-down"></i> </a>
                           <div class="login-dropdown">
                            <div class="text-center">  <span class="arrow"></span>
                           
                              @auth
                              <p class="user-name">{{ auth()->user()->name }}</p>

                              @else
                              <a href="{{ route('user.login') }}"  class="btn btn-md mb-0" > Sign in   </a> 
                              <p>New User? <a href="{{ route('user.registration') }}" class="link"  >Sign up here</a> </p>

                              @endauth
                              </div>
                              <div class="linkss mt-4">
                               
                                <a href="@if(Auth::check()){{route('profile')}}@else {{route('user.login')}} @endif">Your Account</a>
                                <!-- <a href="{{route('wishlists.index')}}">Your Wishlist</a> -->
                                <a href="{{route('purchase_history.index')}}">Your Orders</a>
                                 <a href="{{route('orders.track')}}">Track Order</a>
                                  
                                 @auth
                                <a href="{{ route('logout')}}" class="top-bar-item">Logout</a>
                                @endauth
                              </div>
                          </div>
                      </div>
                </div>
                 <div class="col-lg-2 col-7  position-static myCart">
                      <div class="d-inline-block float-right  " data-hover="dropdown"> 
                          <div class="nav-cart-box dropdown" id="cart_items">
                              <a href="" class="nav-box-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                     <img class="lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{ static_asset('frontend/images/homepage/header/cart.png') }}" alt="{{ env('APP_NAME') }}"> 
                                    <!--  <img class="lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" alt="{{ env('APP_NAME') }}">-->
                                  <span class="nav-box-text  d-xl-inline-block"> 
                                  @if(Session::has('cart'))
                                      <span class="nav-box-number">{{ count(Session::get('cart'))}}</span>
                                  @else
                                      <span class="nav-box-number">0 items</span>
                                  @endif
                                  </span>
                                   <span class="nav-box-text  d-xl-inline-block">My Cart</span>
                              </a>
                              <ul class="dropdown-menu dropdown-menu-right px-0 ">
                                  <li>
                                      <div class="dropdown-cart px-0">
                                          @if(Session::has('cart'))
                                              @if(count($cart = Session::get('cart')) > 0)
                                                  <div class="dc-header">
                                                      <h3 class="heading heading-6 strong-700">Cart Items</h3>
                                                  </div>
                                                  <div class="dropdown-cart-items c-scrollbar">
                                                      @php
                                                          $total = 0;
                                                          $sub_price = 0;
                                                      @endphp
                                                      @foreach($cart as $key => $cartItem)
                                                          @php
                                                              $product = \App\Product::find($cartItem['id']);
                                                              if(Session::has('referal_discount')){
                                                                  
                                                                  if(!empty($shortId)){
                                                                      $total += (peer_discounted_newbase_price($cartItem['id'],$shortId)*$cartItem['quantity']); 
                                                                  }
                                                                  else{
                                                                      $total += (peer_discounted_newbase_price($cartItem['id'])*$cartItem['quantity']);
                                                                  }
                                                              }
                                                              else{
                                                                  $total = $total + $cartItem['price']*$cartItem['quantity'];
                                                              }
                                                          @endphp
                                                          <div class="dc-item">
                                                              <div class="d-flex align-items-center">
                                                                  <div class="dc-image">
                                                                      <a href="{{ route('product', $product->slug) }}">
                                                                          <img class="lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{ my_asset($product->thumbnail_img) }}" class="img-fluid lazyload" alt="{{ __($product->name) }}"> 
                                                                         <!--  <img class="lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" class="img-fluid lazyload" alt="{{ __($product->name) }}">-->
                                                                      </a>
                                                                  </div>
                                                                  <div class="dc-content">
                                                                      <span class="d-block dc-product-name text-capitalize strong-600 mb-1">
                                                                          <a href="{{ route('product', $product->slug) }}">
                                                                              {{ __($product->name) }}
                                                                          </a>
                                                                      </span>

                                                                      <span class="dc-quantity">x{{ $cartItem['quantity'] }}</span>
                                                                      
                                                                      @if(Session::has('referal_discount'))
                                                                      @if(!empty($shortId))
                                                                      <span class="dc-price">{{ single_price(peer_discounted_newbase_price($cartItem['id'],$shortId)*$cartItem['quantity']) }}</span>
                                                                      @else
                                                                      <span class="dc-price">{{ single_price(peer_discounted_newbase_price($cartItem['id'])*$cartItem['quantity']) }}</span>
                                                                      @endif
                                                                      @else    
                                                                           <span class="dc-price">{{ single_price($cartItem['price']*$cartItem['quantity']) }}</span>
                                                                      @endif                       

                                                                  </div>
                                                                  <div class="dc-actions">
                                                                    <button onclick="removeFromCart({{ $key }})">
                                                                          <i class="la la-close"></i>
                                                                      </button>
                                                                  </div>
                                                              </div>
                                                          </div>
                                                      @endforeach
                                                  </div>
                                                  <div class="dc-item py-3">
                                                      <span class="subtotal-text">Subtotal
                                                      </span>
                                                           <span class="subtotal-amount">{{ single_price($total) }}</span>  
                                                  </div>
                                                  <div class="py-2 text-center dc-btn">
                                                      <ul class="inline-links inline-links--style-3">
                                                          <li class="px-1">
                                                              <a href="{{ route('cart') }}" class="link link--style-1 text-capitalize btn btn-base-1 px-3 py-1">
                                                                  <i class="la la-shopping-cart"></i> View cart
                                                              </a>
                                                          </li>
                                                          @if (Auth::check())
                                                          <li class="px-1">
                                                              <a href="{{ route('checkout.shipping_info') }}" class="link link--style-1 text-capitalize btn btn-base-1 px-3 py-1 light-text">
                                                                  <i class="la la-mail-forward"></i> Checkout
                                                              </a>
                                                          </li>
                                                          @endif
                                                      </ul>
                                                  </div>
                                              @else
                                                  <div class="dc-header">
                                                      <h3 class="heading heading-6 strong-700">Your Cart is empty</h3>
                                                  </div>
                                              @endif
                                          @else
                                              <div class="dc-header">
                                                  <h3 class="heading heading-6 strong-700">Your Cart is empty</h3>
                                              </div>
                                          @endif
                                      </div>
                                  </li>
                              </ul>
                          </div>
                     </div>
                     
                 </div>


                    <!-- <span>|</span> -->
                 <!--  <a href="{{ route('user.registration') }}"  >{{ translate('Registration')}}</a> -->
                  
              </div>
            </div>
        </div>
        <div class="">
            <div class="container">
                <div class="row no-gutters align-items-center">
                    <div class="col-lg-2 col-5">
                        <div class="d-flex">
                            <div class="d-block d-lg-none mobile-menu-icon-box">
                                <!-- Navbar toggler  -->
                                <a href="" onclick="sideMenuOpen(this)">
                                    <div class="hamburger-icon">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </a>
                            </div>

                            <!-- Brand/Logo -->
                            <a class="navbar-brand w-100" href="{{ route('home') }}">
                                @php
                                    $generalsetting = \App\GeneralSetting::first();
                                @endphp
                                @if($generalsetting->logo != null)
                                    <img src="{{ my_asset($generalsetting->logo) }}" alt="{{ env('APP_NAME') }}"> 
                                    <!-- <img src="{{ asset('public/uploads/logo/logo.jpg') }}" alt="{{ env('APP_NAME') }}">-->
                                
                                @else
                                    <img src="{{ static_asset('frontend/images/logo/logo.png') }}" alt="{{ env('APP_NAME') }}"> 
                                   <!--  <img src="{{ asset('public/uploads/logo/logo.jpg') }}" alt="{{ env('APP_NAME') }}">-->
                                @endif
                            </a>

                            
                           <!--  @if(Route::currentRouteName() != 'home' && Route::currentRouteName() != 'categories.all') -->
                               
                            <!-- @endif -->
                           
                        </div>
                    </div>

                   <!--  <div class="col-lg-1 col-4 position-static d-none d-md-block">   
                        <nav class="navbar navbar-expand-lg navbar-light  float-right d-none d-md-block">
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                            </button>
                        </nav>   
                    </div> -->
                     <!-- <a href="https://api.whatsapp.com/send?phone=+919667018020" class="whatsapp d-block d-md-none"><i class="fa fa-whatsapp"></i> Order By Whatsapp</a> -->
                   
                      <div class="col-lg-2 pl-0  ">
                          <div class="d-none d-xl-block category-menu-icon-box pl-0">
                                    <div class="dropdown-toggle navbar-light category-menu-icon" id="category-menu-icon">
                                        <!-- <span class="navbar-toggler-icon"></span> -->
                                        Shop by Category  <i class="fa fa-chevron-down"></i>
                                    </div>
                                </div>
                     </div>
                    <div class="col-lg-5 co-12 position-static  search-box-mob">
                    
                      <div class="d-inline-block search">
                        <form action="{{ route('search') }}" method="GET">
                          <div class="nav-search-box  ">
                               <button class="btn" type="submit" ><i class="fa fa-search"></i></button>
                               <input type="text" id="search" name="q" placeholder="Search products.." autocomplete="off">
                          </div>
                          <div class="typed-search-box d-none">
                                            <div class="search-preloader">
                                                <div class="loader"><div></div><div></div><div></div></div>
                                            </div>
                                            <div class="search-nothing d-none">

                                            </div>
                                            <div id="search-content">

                                            </div>
                                        </div>
                        </form>
                      </div>
                    </div>

                   

                   

                     <div class="col-lg-3 referral pr-0  ">
                      <span> To get exclusive discounts Enter Peer Code </span>
                        @if(Session::has('referal_discount'))
                        <form action="{{route('discount.remove_partner_coupon_code')}}" method="post">
                            @csrf
                            
                            <input type="text" name="code" value="{{Session::get('referal_code')}}" placeholder="" autocomplete="off">
                            <button type="submit" class="btn"><i class="fa fa-paper-plane"></i></button>
                        </form>
                        @else
                        <form action="{{route('discount.apply_partner_coupon_code')}}" method="post">
                            @csrf
                            <input type="text" name="code" value="{{Session::get('referal_code')}}" placeholder="Enter Peer Partner Code" required="" autocomplete="off">
                            <button type="submit" class="btn"><i class="fa fa-paper-plane"></i></button>

                             
                            
                        </form>
                        @endif          
                     </div>
                </div>
            </div>
        </div>
        <div class="hover-category-menu" id="hover-category-menu">
            <div class="container">
                <div class="row no-gutters position-relative">
                    <div class="col-lg-3 position-static">
                        <div class="category-sidebar" id="category-sidebar">
                            <div class="all-category">
                                <span>CATEGORIES</span>
                                <a href="{{ route('categories.all') }}" class="d-inline-block">See All <i class="fa fa-angle-right"></i></a>
                            </div>
                            

                            @php 
                            
                            $featured_categories = featured_categories($shortId);
                            @endphp
                            <ul class="categories">
                                @foreach ($featured_categories as $key => $category)
                                    @php
                                        $brands = [];
                                    @endphp
                                    <li class="category-nav-element" data-id="{{ $category->id }}">
                                        <a href="{{ route('products.category', $category->slug) }}">
                                            <img class="cat-image lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{ my_asset($category->icon) }}" width="30" alt="{{ __($category->name) }}">
                                            <!-- <img class="cat-image lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" width="30" alt="{{ __($category->name) }}"> -->
                                            <span class="cat-name">{{ __($category->name) }}</span>
                                        </a>
                                        @if(count($category->subcategories)>0)
                                            <div class="sub-cat-menu c-scrollbar">
                                                <div class="c-preloader">
                                                    <i class="fa fa-spin fa-spinner"></i>
                                                </div>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Navbar -->

    <!-- <div class="main-nav-area d-none d-lg-block">
        <nav class="navbar navbar-expand-lg navbar--bold navbar--style-2 navbar-light bg-default">
            <div class="container">
                <div class="collapse navbar-collapse align-items-center justify-content-center" id="navbar_main">
                    <ul class="navbar-nav">
                        @foreach (\App\Search::orderBy('count', 'desc')->get()->take(5) as $key => $search)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('suggestion.search', $search->query) }}">{{ $search->query }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </nav>
    </div> -->
    
</div>


<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Josefin+Sans&display=swap" rel="stylesheet"> 
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.0/semantic.min.css" /> -->
<div class="modal_popup_location" id="modal_popup_location">
  <form action="{{route('location.set')}}" class="zooming" method="POST">
    @csrf
    <span id="close" onclick="close_location_modal()">&times;</span>
        <h2>Location</h2>
        <p id="sub_title"></p>
        <center>
          <ul>
            <li>
                <label for="location">Enter Your Address</label>
                <div class="ui icon big input" id="locator-input-section">
                  <input type="text" name="address" placeholder="Enter Your Address" id="autocomplete"  @if(session()->has('location_address')) value="{{session::get('location_address')}}"  @endif/>
                  <i aria-hidden="true" class="dot circle outline link icon" id="locator-button"></i>
                </div>
            </li>
            <input type="hidden" name="city" id="location_city"  @if(session()->has('location_city')) value="{{session::get('location_city')}}"  @else value="" @endif />
            <input type="hidden" name="state" id="location_state" @if(session()->has('location_state')) value="{{session::get('location_state')}}"  @else value="" @endif>
            <input type="hidden" name="country" id="location_country" @if(session()->has('location_country')) value="{{session::get('location_country')}}"  @else value="" @endif>
            <input type="hidden" name="postal" id="location_postal" @if(session()->has('location_postal')) value="{{session::get('location_postal')}}"  @else value="" @endif>
            <li><button type="submit">Submit</button></li>
          </ul>
        </center>
  </form>
</div>



<style type="text/css">

   #locator-input-section {
    width: 102%;
  margin: 2px;
  max-width: 537px;
  margin-left: -5px;
}
.modal_popup_location{
font-family: 'Josefin Sans', sans-serif;
width:100%;
height:100%;
display:flex;
align-items:center;
justify-content:center;
position:fixed;
top:15%;
z-index:1;
display:none;
box-shadow:0px 6px 16px -6px rgba(1,1,1,0.5);
background:rgba(1,1,1,0.5);
text-align:center;
}
.modal_popup_location form{
padding:20px;
background-image:url("https://i.ibb.co/dcBg06k/vision.jpg");
background-size:cover;
width:35%;
position:absolute;
left:30%;
top:20%;
text-align:center;
}
.modal_popup_location #close{
position:absolute;
right:0;
top:0;
cursor:pointer;
background:#1e1e1e;
padding:0px 10px;
color:#3399ff;
font-size:2em;
transition:0.2s;
}
.modal_popup_location #close:hover{
color:#fff;
}
.modal_popup_location ul{
width:80%;
}
.modal_popup_location #sub_title{
font-size:0.7em;
color:gray;
position:static;
background:transparent;
padding:0px;
cursor:default;
}
.modal_popup_location ul li{
list-style:none;
text-align:left;
}
.modal_popup_location ul li label,.modal_popup_location ul li input,.modal_popup_location ul li button{
margin:10px 0px;
display:block;
}
.modal_popup_location ul li input{
padding:15px 10px;
width:300px;
outline:none;
}
.modal_popup_location ul li button{
background:#3399ff;
outline:none;
border:0;
border-radius:3px;
box-shadow:0px 6px 16px -6px rgba(1,1,1,0.5);
padding:10px 20px;
color:#fff;
transition:0.5s;
}
.modal_popup_location ul li button:hover{
box-shadow:0px 1px 1px 0px rgba(1,1,1,0.5);
}
/* Add zooming Animation */
.zooming {
  -webkit-animation: animatezoom 0.5s;
  animation: animatezoom 0.5s
}

@-webkit-keyframes animatezoom {
  from {-webkit-transform: scale(0)} 
  to {-webkit-transform: scale(1)}
}
  
@keyframes animatezoom {
  from {transform: scale(0)} 
  to {transform: scale(1)}
}
/*@media (max-width:400px){
.btn{
 position:absolute;
 left:30vw;
}
.modal_popup_location form{
position:fixed;
padding:10px;
left:1%;
width:90%;
margin:1%;
}
.modal_popup_location ul li label{
font-size:13px; 
}
.modal_popup_location h2{
font-size:1em;    
}
.modal_popup_location ul li input{
width:100%;
}
.modal_popup_location ul{
width:70%;
position:relative;
left:-30px;
}
.modal_popup_location #sub_title{
font-size:10px;    
}
}*/
@media (max-width:600px){
.modal_popup_location form{
position:fixed;
padding:10px;
left:1%;
width:90%;
margin:1%;
}
.modal_popup_location ul li input{
width:100%;
}
.modal_popup_location ul{
width:70%;
}
}

@media (max-width:760px){
.modal_popup_location form{
position:fixed;
padding:10px;
left:1%;
width:90%;
margin:1%;
}
.modal_popup_location ul li input{
width:100%;
}
}

@media (max-width:900px){
.modal_popup_location form{
position:fixed;
padding:10px;
left:1%;
width:90%;
margin:1%;
}
.modal_popup_location ul li input{
width:100%;
}
}
@media (max-width:980px){
.modal_popup_location form{
position:fixed;
padding:10px;
left:1%;
width:90%;
margin:1%;
}
.modal_popup_location ul li input{
width:100%;
}
.modal_popup_location ul{
width:70%;
position:relative;
left:-30px;
}
}
  .logo-bar-area .navbar-brand {
    text-align: center !important;
  }
	ul.bs-autocomplete-menu {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  max-height: 200px;
  overflow: auto;
  z-index: 9999;
  border: 1px solid #eeeeee;
  border-radius: 4px;
  background-color: #fff;
  box-shadow: 0px 1px 6px 1px rgba(0, 0, 0, 0.4);
}

ul.bs-autocomplete-menu a {
  font-weight: normal;
  color: #333333;
}

.ui-helper-hidden-accessible {
  border: 0;
  clip: rect(0 0 0 0);
  height: 1px;
  margin: -1px;
  overflow: hidden;
  padding: 0;
  position: absolute;
  width: 1px;
}

.ui-state-active,
.ui-state-focus {
  color: #23527c;
  background-color: #eeeeee;
}

.bs-autocomplete-feedback {
  width: 1.5em;
  height: 1.5em;
  overflow: hidden;
  margin-top: .5em;
  margin-right: .5em;
}

.loader {
  font-size: 10px;
  text-indent: -9999em;
  width: 1.5em;
  height: 1.5em;
  border-radius: 50%;
  background: #333;
  background: -moz-linear-gradient(left, #333333 10%, rgba(255, 255, 255, 0) 42%);
  background: -webkit-linear-gradient(left, #333333 10%, rgba(255, 255, 255, 0) 42%);
  background: -o-linear-gradient(left, #333333 10%, rgba(255, 255, 255, 0) 42%);
  background: -ms-linear-gradient(left, #333333 10%, rgba(255, 255, 255, 0) 42%);
  background: linear-gradient(to right, #333333 10%, rgba(255, 255, 255, 0) 42%);
  position: relative;
  -webkit-animation: load3 1.4s infinite linear;
  animation: load3 1.4s infinite linear;
  -webkit-transform: translateZ(0);
  -ms-transform: translateZ(0);
  transform: translateZ(0);
}

.loader:before {
  width: 50%;
  height: 50%;
  background: #333;
  border-radius: 100% 0 0 0;
  position: absolute;
  top: 0;
  left: 0;
  content: '';
}

.loader:after {
  background: #fff;
  width: 75%;
  height: 75%;
  border-radius: 50%;
  content: '';
  margin: auto;
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
}

@-webkit-keyframes load3 {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}

@keyframes load3 {
  0% {
    -webkit-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
</style>

@section('script')

@endsection
<script type="text/javascript">
  jQuery(function(){
     var vals = $('.yes_exist').val();
     if(vals == 1){
        jQuery('#modal').click();
     }
     
  });
</script>
<script type="text/javascript">




  function get_states_by_region(el){
    var region_id = $(el).val();
        $(el).closest('.product-choose').find('.state_id').html(null);
    $.post('{{ route('states.get_states_by_region') }}',{_token:'{{ csrf_token() }}', region_id:region_id}, function(data){
        for (var i = 0; i < data.length; i++) {
            $(el).closest('.product-choose').find('.state_id').append($('<option>', {
                value: data[i].id,
                text: data[i].name
            }));
        }
        $(".state_id").prepend("<option value='' selected='selected'>Select state</option>");
            $(el).closest('.product-choose').find('.state_id');
    });
  }

  function get_mapped_city_by_state_id(el){
    var state_id = $(el).val();
        $(el).closest('.pincode').find('.city').html(null);
    $.post('{{ route('cities.get_mapped_city_by_state_id') }}',{_token:'{{ csrf_token() }}', state_id:state_id}, function(data){
        for (var i = 0; i < data.length; i++) {
            $(el).closest('.pincode').find('.city').append($('<option>', {
                value: data[i].id,
                text: data[i].name
            }));
        }
        $(".city").prepend("<option value='' selected='selected'>Select city</option>");
            $(el).closest('.pincode').find('.city');
    });
  }

  function get_cluster_by_city(el){
    var city_id = $(el).val();

    $(el).closest('.product-choose').find('.cluster_id').html(null);
      $.post('{{ route('area.get_cluster_by_city_id') }}',{_token:'{{ csrf_token() }}', city_id:city_id}, function(data){
          for (var i = 0; i < data.length; i++) {
              $(el).closest('.product-choose').find('.cluster_id').append($('<option>', {
                  value: data[i].user_id,
                  text: data[i].email
              }));
          }
          $(".cluster_id").prepend("<option value='' selected='selected'>Select cluster</option>");
              $(el).closest('.product-choose').find('.cluster_id').select2();
      });
  }

  function get_area_by_city(el){
    var city_id = $(el).val();
        $(el).closest('.pincode').find('.area_id').html(null);
        $.post('{{ route('area.get_area_for_delivery') }}',{_token:'{{ csrf_token() }}', city_id:city_id}, function(data){
         $(el).closest('.pincode').find('.area_id').append($("<option></option>")
                    .attr("value", "")
                    .text("SELECT PINCODE")); 
            for (var i = 0; i < data.length; i++) {
                $(el).closest('.pincode').find('.area_id').append($('<option>', {
                    value: data[i].pincode,
                    // text: data[i].area_name+' | '+data[i].pincode
                    text: data[i].pincode
                }));
            }
                $(el).closest('.pincode').find('.area_id').select2();
        });
  }

    $(document).ready(function(){
        $('.demo-select2').select2();
    });

    $('.state').on('change', function() {
        get_mapped_city_by_state_id(this);
    });

    $('.city').on('change', function() {
        get_area_by_city(this);
    });


(function () {
    var locatorSection = document.getElementById("locator-input-section")
    var input = document.getElementById("autocomplete");
    var city  = document.getElementById("location_city");
    var state = document.getElementById("location_state");
    var country = document.getElementById("location_country");
    var postal = document.getElementById("location_postal");


    function init() {
        var locatorButton = document.getElementById("locator-button");
        locatorButton.addEventListener("click", locatorButtonPressed)
    }

    function locatorButtonPressed() {
        locatorSection.classList.add("loading")
        navigator.geolocation.getCurrentPosition(function (position) {
                getUserAddressBy(position.coords.latitude, position.coords.longitude)
            },
            function (error) {
                locatorSection.classList.remove("loading")
                alert("The Locator was denied :( Please add your address manually")
            })
    }

    function getUserAddressBy(lat, long) {
        var GOOGLE_MAP_KEY = "AIzaSyAI3w2M4TTzj7JRpmO3gfGm5q6aaC9lnTM";
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                var address = JSON.parse(this.responseText)
                setAddressToInputField(address.results[0].formatted_address)
                setHiddenToInputField(address.results[0].address_components[4].long_name, address.results[0].address_components[5].long_name, address.results[0].address_components[6].long_name, address.results[0].address_components[7].long_name)
            }
        };
        xhttp.open("GET", "https://maps.googleapis.com/maps/api/geocode/json?latlng=" + lat + "," + long + "&key="+ GOOGLE_MAP_KEY, true);
        xhttp.send();
    }

    function setAddressToInputField(address) {
        input.value = address
        locatorSection.classList.remove("loading")
    }

    function setHiddenToInputField(cityL, stateL, countryL, postal_code){
        city.value = cityL
        state.value = stateL
        country.value = countryL
        postal.value = postal_code
    }

    var defaultBounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(45.4215296, -75.6971931),
    );

    var options = {
        bounds: defaultBounds
    };

    var autocomplete = new google.maps.places.Autocomplete(input, options);
    init()
})();


function open_location_modal(){
  document.getElementById("modal_popup_location").style.display="block";
}

function close_location_modal(){
  document.getElementById("modal_popup_location").style.display="none";
}


  function detectLocation() {
  if(navigator.geolocation) {
		navigator.geolocation.getCurrentPosition(function(position) {
            var latitude = position.coords.latitude;
            var longitude = position.coords.longitude;
            
            var address = getAddress(latitude,longitude);
            
	 }, function() {
		  	handleNoGeolocation(true);
		});
	 } else {
			handleNoGeolocation(false);
	 }
}


function getAddress (latitude, longitude) {
    return new Promise(function (resolve, reject) {
        var request = new XMLHttpRequest();

        var method = 'GET';
        var url = 'https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyAI3w2M4TTzj7JRpmO3gfGm5q6aaC9lnTM&latlng=' + latitude + ',' + longitude + '&sensor=true';
        var async = true;

        request.open(method, url, async);
        request.onreadystatechange = function () {
            if (request.readyState == 4) {
                if (request.status == 200) {
                    var data = JSON.parse(request.responseText);
                    var address = data.results[0];
                    var currentPosition = address.address_components;
                    var arr = [];
                    
		
                    for( var i = 0; i < currentPosition.length; i++ ) {
                        if ( currentPosition[ i ].types[0] && 'route' === currentPosition[ i ].types[0] ) {
                            var route = currentPosition[ i ].long_name;
                        }
                        if ( currentPosition[ i ].types[0] && 'locality' === currentPosition[ i ].types[0] ) {
                           var locality = currentPosition[ i ].long_name;
                        }
                        if ( currentPosition[ i ].types[0] && 'administrative_area_level_1' === currentPosition[ i ].types[0] ) {
                            var states = currentPosition[ i ].long_name;
                        }
                        if ( currentPosition[ i ].types[0] && 'country' == currentPosition[ i ].types[0] ) {
                            var countries = currentPosition[ i ].long_name;
                        }
                        if ( currentPosition[ i ].types[0] && 'postal_code' == currentPosition[ i ].types[0] ) {
                            var postalCode = currentPosition[ i ].long_name;
                        }
                        if ( currentPosition[ i ].types[0] && 'administrative_area_level_2' === currentPosition[ i ].types[0] ) {
                            var citi = currentPosition[ i ].long_name;
                        }
                    }
                    var autoloc = true;
                    document.getElementById("spinnig_arrow").style.display = "";
                        $.ajax({
                        type:"POST",
                        url: '{{ route('addresses.set_location') }}',
                        data: {_token:'{{ csrf_token() }}',citi:citi,postalCode:postalCode,countries:countries,states:states,locality:locality,route:route,autoloc:true},
                        success: function(data){
                            window.location.reload();
                            document.getElementById("spinnig_arrow").style.display = "hidden";
                        }
                    });

                    resolve(address);
                }
                else {
                    reject(request.status);
                }
            }
        };
        request.send();
    });
}

</script>