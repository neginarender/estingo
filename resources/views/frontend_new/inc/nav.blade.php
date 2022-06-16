<!-- <div class="location-landing pincode" style="display: none;">
           .
          
            <div class="left">
                <div class="loc_loader" id="spinnig_arrow" style="display:none"><img src="{{ static_asset('frontend/img/spinning_arrows.gif')}}" alt=""></div>
                <h1>Pick a City</h1>
                <p>To find awesome offers around you</p>
                 <button class="btnn" onclick = "detectLocation()"> 
                {{ translate("Use my current location") }}
                </button> 
                <div class="box mb-3">
                    <div class="row">
                        <img src="{{ static_asset('frontend/new/assets/images/icon-map.png') }}" alt="">

                        <div class="col-md-6">
                            <select class="city demo-select2" name="city_id" id="city_id" data-placeholder="Select City" onchange="getPincodes(this.value)">
                                
                                
                                
                            </select>
                        </div>
                        <div class="col-md-6 pt-3 pt-md-0">
                            <select class="area_id demo-select2" id="area_id" data-placeholder="Select Pincode" onchange="setSortingHub(this.value)">
                                
                                </select>
                        </div>
                    </div>
                </div>
        </div> -->
        <!-- <div class="ovrlay-landing"></div> -->
        <!-- end locaiton popup -->
        <div class="overly"></div>
        <div class="overrlay"></div>
        <div class="header bg-white">
            <!-- mobile menu -->
            <div class="mobile-side-menu d-lg-none">
                <div class="side-menu-overlay opacity-0" onclick="sideMenuClose()"></div>
                <div class="side-menu-wrap opacity-0">
                    <div class="side-menu closed">
                        <div class="side-menu-header ">
                            <div class="side-menu-close" onclick="sideMenuClose()">
                                x
                            </div>
                            <div class="widget-profile-box px-3 py-4 d-flex align-items-center">
                                <div class="image " style="background-image:url('assets/images/icons/user-placeholder.jpg')"></div>
                            </div>
                            <div class="side-login px-3 pb-3">
                                <a href="login.html">Sign In</a>
                                <a href="registration.html">Registration</a>

                            </div>
                        </div>
                        <div class="side-menu-list px-3">
                            <ul class="side-user-menu">
                                <li>
                                    <a href="index.html">
                                             <i class="fa fa-home"></i>
                                            <span>Home</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="categories.html">
                                        <i class="fa fa-th-large"></i>
                                        <span>Shop by category</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="user-dashboard.html">
                                    <i class="fa fa-dashboard"></i>
                                    <span>Dashboard</span>
                                </a>
                                </li>

                                <li>
                                    <a href="order-history.html">
                                    <i class="fa fa-file-text"></i>
                                     <span>Purchase History</span>
                                   </a>
                                </li>

                                <li>
                                    <a href="cart.html">
                                      <i class="fa fa-shopping-cart"></i>
                                       <span>Cart</span>
                                      <span class="badge item_in_cart" id="cart_items_sidenav">0</span>
                                 </a>
                                </li>
                                <li>
                                    <a href="wishlist.html">
                                        <i class="fa fa-heart-o"></i>
                                        <span>Wishlist</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="wallet.html">
                                         <i class="fa fa-dollar"></i>
                                          <span>My Wallet</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="profile.html">
                                        <i class="fa fa-user"></i>
                                        <span>Manage Profile</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="support-ticket.html" class="">
                                        <i class="fa fa-support"></i>
                                        <span class="category-name">
                                            Support Ticket
                                        </span>
                                    </a>
                                </li>

                                <li>
                                    <a href="contact-us.html">
                                        <i class="fa fa-phone"></i>
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
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-2 col-7 text-left brdr pr-4 ">
                                <a href="javascript:;" class="loc"><img src="{{ static_asset('frontend/new/assets/images/home/Mask%20Group%2036.png')}}">
                                @if(Cookie::has('sid'))
                                    {{ Cookie::get('pincode') }}, {{ Cookie::get('city_name') }}
                                @else
                                Select Location
                                @endif
                                 <span class="fa fa-angle-down"></span>
                             </a>
                            </div>

                            <div class="col-lg-4 col-5 text-left pl-4 contact_info brdr">
                                <span class="info"> <img class="float-left mr-1" src="{{ static_asset('frontend/new/assets/images/home/Mask%20Group%2037.png') }}"> 9667018020 </span>
                                <span class="ml-2 mr-2 sep">|</span>
                                <span class="">Download App <a href="#" target="_blank"> <img class="mr-1 ml-2" src="{{ static_asset('frontend/new/assets/images/home/Mask%20Group%2038.png') }}"> 
                                <!-- <a href="#" target="_blank">  <img src="{{ static_asset('frontend/new/assets/images/home/Mask%20Group%2039.png')}}"></a> -->
                            </span>

                            </div>

                            <div class="col-lg-6 col">
                                <div class="position-static float-left">
                                    <ul class="inline-links middle lang float-right float-md-left  pr-5 ">

                                        <li class="dropdown" id="lang-change">
                                            <a href="#" class="dropdown-toggle top-bar-item" data-toggle="dropdown">
                                              <img src="{{ static_asset('frontend/new/assets/images/icons/flags/en.png')}}" height="11" data-src="{{ static_asset('frontend/new/assets/images/icons/flags/en.png')}}" class="flag lazyload" alt="English" height="11"><span class="language">English</span>
                                            </a>

                                            <ul class="dropdown-menu">
                                                <li class="dropdown-item ">
                                                    <a href="#" data-flag="en"><img src="{{ static_asset('frontend/new/assets/images/icons/flags/en.png')}}" data-src="{{ static_asset('frontend/new/assets/images/icons/flags/en.png')}}" class="flag lazyload" alt="English" height="11"><span class="language">English</span></a>
                                                </li>

                                            </ul>

                                        </li>
                                    </ul>
                                </div>
                                <div class="position-static myCart float-right">
                                    <div class="d-inline-block float-right  " data-hover="dropdown">
                                        <div class="nav-cart-box dropdown" id="cart_items">
                                            <a href="#" class="nav-box-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                              <img src="{{ static_asset('frontend/new/assets/images/home/Mask%20Group%2040.png')}}" alt="Rozana">
                                              <span class="nav-box-text  d-xl-inline-block"> 
                                                    <span class="nav-box-number item_in_cart">0</span>
                                                </span>
                                                 <span class="nav-box-text  d-xl-inline-block">Cart</span>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-right px-0 nav_cart">
                                           

                                        
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                                <div class="login-opt float-right mr-4">
                                    <a href="#" class="login-btn"><i class="fa fa-user-o">
                                       </i> 
                                    Hello!   
                                    @if(Session::has('user'))
                                        {{ json_decode(session()->get('user'))->name }}
                                    @else
                                    Sign in
                                    @endif

                                   
                                       <i class="fa fa-angle-down"></i> </a>
                                        <div class="login-dropdown" style="min-height:100px!important;">
                                       
                                            <div class="text-center"> <span class="arrow"></span>
                                            @if(!Session::has('user'))
                                                <a href="{{ route('userapi.userlogin',['next'=>'normal']) }}" class="btn btn-md mb-0"> Sign in   </a>
                                                <p>New User? <a href="{{ route('userapi.register',['next'=>'normal']) }}" class="link">Sign up here</a> </p>
                                            @endif
                                            </div>
                                            <div class="linkss mt-4">
                                                <!-- <a href="{{route('phoneapi.dashboard')}}">Your Account</a>
                                                <a href="{{route('phoneapi.wishlists')}}">Your Wishlist</a>
                                                <a href="{{route('phoneapi.purchase_history')}}">Your Orders</a>
                                                <a href="{{route('phoneapi.track_order')}}">Track Order</a> -->
                                                @if(Session::has('user'))
                                                <a href="{{ route('phoneapi.logout') }}">Logout</a>
                                                @endif
                                            </div>
                                        </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="logo-area">
                    <div class="container-fluid">
                        <div class="row no-gutters align-items-center">
                            <div class="col-lg-2 col-5">
                                <div class="d-flex">
                                    <div class="d-block d-lg-none mobile-menu-icon-box">
                                        <!-- Navbar toggler  -->
                                        <a href="#" onclick="sideMenuOpen(this)">
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
                                      <img src="{{ static_asset('frontend/new/assets/images/logo.jpg')}}">
                                    </a>
                        
                                </div>
                            </div>
                             <div class="col-lg-2 pl-0  ">
                                <div class="d-none d-xl-block category-menu-icon-box pl-0">
                                    <div class="dropdown-toggle navbar-light category-menu-icon" id="category-menu-icon">
                                        Shop by Category <i class="fa fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5 co-12 position-static  search-box-mob">

                                <div class="d-inline-block search" style="position: relative;">
                                    <form action="{{ route('phoneapi.elasticsearch') }}" method="GET" class="mb-0">
                                        <div class="nav-search-box  ">
                                            <button class="btn" type="submit"><i class="fa fa-search"></i></button>
                                            <input type="text" id="search" name="q" placeholder="Search products.." autocomplete="off">
                                        </div>
                                        <div class="typed-search-box d-none">
                                            <div class="search-preloader">
                                                <div class="loader">
                                                    <div></div>
                                                    <div></div>
                                                    <div></div>
                                                </div>
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
                                <form id="apply_referal_code_form"> 
                                    @php $peercode = ""; if(Cookie::has('peer')){$peercode = Cookie::get('peer');} @endphp
                                    <input type="text" name="code" id="referal_code" value="{{ $peercode }}" placeholder="Enter Peer Partner Code" required="" autocomplete="off" readonly>
                                    <button type="button" id="referal_btn" class="btn" onclick="@if(!empty($peercode)) removePeerCode() @else applyPeerCode() @endif">
                                    <!-- <i class="fa fa-paper-plane"></i> -->
                                </button>
                                </form>

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
                                        <!-- <a href="categories.html" class="d-inline-block">See All <i class="fa fa-angle-right"></i></a> -->
                                    </div>

                                    <ul class="categories" id="categories">
                                        
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
      </div>

      <script>

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