@php 
header("Cache-Control: max-age=3600"); @endphp
<!DOCTYPE html>
@if(\App\Language::where('code', Session::get('locale', Config::get('app.locale')))->first()->rtl == 1)
<html dir="rtl" lang="en">
@else
<html lang="en">
@endif
<head>
<meta name="ahrefs-site-verification" content="b01ca395542d4fa17860424811f72e4c41795a178cc695adf3a98f71e1899c11">
@php

if(!Auth::check())
{
    if(Cookie::has('auth_id'))
    {
        $userInfo = \App\User::where('id', Cookie::get('auth_id'))->first();
        Auth::login($userInfo);
    }
}

if(!Session::has('cart')){
    $cart = collect([]);
        if(Cookie::has('cart')){
            $cart = json_decode(Cookie::get('cart'));
            $collection = collect([]);
            foreach($cart as $key => $item){
                $collection->push(['id' => $item->id,
                                           'variant'=>$item->variant,
                                           'quantity'=>$item->quantity,
                                           'price'=>$item->price,
                                           'shipping'=>$item->shipping,
                                           'tax'=>$item->tax,
                                           'product_referral_code'=>$item->product_referral_code,
                                           'digital'=>$item->digital

                ]);
        }
            Session::put('cart',$collection);
        }
    }

    //13-10-2021
    if(!Session::has('referal_code')){
        if (isset($_COOKIE['last_used_code'])) {

            $data = explode(",", $_COOKIE["last_used_code"]);
            $partner_id = $data[0];
            $referal_discount = $data[1];
            $referal_code = $data[2];

            Session::put('partner_id', $partner_id);
            Session::put('referal_discount', $referal_discount);
            Session::put('referal_code', $referal_code);
        } 
                
    }

    $seosetting = \App\SeoSetting::first();
    
@endphp


<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1.0, 
     user-scalable=0">
<meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">
<meta name="robots" content="index, follow">
<title>@yield('meta_title', config('app.name', 'Laravel'))</title>
<meta name="description" content="@yield('meta_description', $seosetting->description)" />
<meta name="keywords" content="@yield('meta_keywords', $seosetting->keyword)">
<meta name="author" content="{{ $seosetting->author }}">
<meta name="sitemap_link" content="{{ $seosetting->sitemap_link }}">
<meta name="csrf-token" content="{{ csrf_token() }}" />
{{-- <meta name="google-site-verification" content="pqZvvOX5_n6SiTPvvLmW7jOtPgu8SLzvDvi4ShwbmQ0" /> --}}
<meta name="google-site-verification" content="CwkFIAWvl8TXgnIOcp6IZ4bm02r__P9GXxeEyAWlA6w" />
<meta name="p:domain_verify" content="05871752df1d3c0feba13e50bb0543b8"/>

@yield('meta')


{{-- @if(!isset($detailedProduct) && !isset($shop) && !isset($page)) --}}
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ config('app.name', 'Laravel') }}">
    <meta itemprop="description" content="{{ $seosetting->description }}">
    <meta itemprop="image" content="{{ Storage::disk('s3')->url(\App\GeneralSetting::first()->logo) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ config('app.name', 'Laravel') }}">
    <meta name="twitter:description" content="{{ $seosetting->description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ Storage::disk('s3')->url(\App\GeneralSetting::first()->logo) }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ config('app.name', 'Laravel') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ route('home') }}" />
    <meta property="og:image" content="{{ Storage::disk('s3')->url(\App\GeneralSetting::first()->logo) }}" />
    <meta property="og:description" content="{{ $seosetting->description }}" />
    <meta property="og:site_name" content="{{ env('APP_NAME') }}" />
    <meta property="fb:app_id" content="{{ env('FACEBOOK_PIXEL_ID') }}">
{{-- @endif --}}
<meta property="og:image" itemprop="image" content="{{ Storage::disk('s3')->url(\App\GeneralSetting::first()->logo) }}" />

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAI3w2M4TTzj7JRpmO3gfGm5q6aaC9lnTM&libraries=places">
</script>
<!-- Favicon -->
<link type="image/x-icon" href="{{ Storage::disk('s3')->url(\App\GeneralSetting::first()->favicon) }}" rel="shortcut icon" />

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i" rel="stylesheet" media="none" onload="if(media!='all')media='all'">

<link type="text/css" href="{{ static_asset('frontend/css/combine_all.css') }}" rel="stylesheet" media="all">
 

  <!--  <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.css" rel="stylesheet"> -->
   <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.css" as="style" 
onload="this.rel='stylesheet'"><noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.css"></noscript>
<!-- <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">  -->

<link rel="preload" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css" as="style" 
onload="this.rel='stylesheet'"><noscript><link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css"></noscript>


@if(\App\Language::where('code', Session::get('locale', Config::get('app.locale')))->first()->rtl == 1)
     <!-- RTL -->
    <link type="text/css" href="{{ static_asset('frontend/css/active.rtl.css') }}" rel="stylesheet" media="all">
@endif

<!-- Custom style -->

<!-- <link rel="preload" href="{{ static_asset('frontend/css/custom-style.css') }}" as="style" 
onload="this.rel='stylesheet'"><noscript><link rel="stylesheet" href="{{ static_asset('frontend/css/custom-style.css') }}"></noscript> -->

<!-- jQuery -->
<script   src="{{ static_asset('frontend/js/vendor/jquery.min.js') }}"></script>


<script>
		window.onload=function(){
			document.getElementById('loader').style.display="none";
			document.getElementById('wholecontent').style.display="block";
		};
		</script>
		<style>
		#wholecontent{display:none;}
		#loader{
			position: absolute;
			margin: auto;
			top: 50%;
			right: 0;
			bottom: 0;
			left: 0;
			width: 400px;
			height: 400px;
		}

		#offermodal .close{right: -14px;top: -3px; color: #fff; opacity: 1; font-size: 16px;}
		@media (max-width: 640px){
			#offermodal .close{right: 0; top: -18px}
		}
		</style>


        <!-- Global site tag (gtag.js) - Google Ads: 583827208 -->
        {{-- <script async src="https://www.googletagmanager.com/gtag/js?id=AW-583827208"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'AW-583827208');
        </script> --}}


<!--schema start -->
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "WebSite",
  "name": "Rozana.in",
  "url": "https://www.rozana.in/",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "https://www.rozana.in/{search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>
<!--schema end-->
<!--facebook code start-->
<meta name="facebook-domain-verification" content="3vye0rq3kqbtrq4egmyv15tt6pnchm" />
<!--facebook code end-->

</head>
<body>



<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TSHJSZ9"
height="0" width="0" class="iframe-none"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<!-- {{ static_asset('frontend/images/logo/logo.png') }} -->
<div id="loader"><img class="center" src="{{ static_asset('frontend/images/loader1.jpg') }}"/></div>
<!-- MAIN WRAPPER -->
<div class="body-wrap shop-default shop-cards shop-tech gry-bg" id="wholecontent">

    <!-- Header -->
    
    @include('frontend.inc.nav')
    
    @yield('content')
    
    @include('frontend.inc.footer')
    
    @include('frontend.partials.modal')
    
    {{-- @if (\App\BusinessSetting::where('type', 'facebook_chat')->first()->value == 1)
        <div id="fb-root"></div>
        <!-- Your customer chat code -->
        <div class="fb-customerchat"
          attribution=setup_tool
          page_id="{{ env('FACEBOOK_PAGE_ID') }}">
        </div>
    @endif --}}

    <div class="modal fade" id="addToCart">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="c-preloader">
                    <i class="fa fa-spin fa-spinner"></i>
                </div>
                <button type="button" class="close absolute-close-btn" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div id="addToCart-modal-body">

                </div>
            </div>
        </div>
    </div>


    <!-- offer modal on load -->
     <!-- <div class="modal fade" id="offermodal">
        <div class="modal-dialog modal-md modal-dialog-centered  "   role="modal">
            <div class="modal-content position-relative p-0">
            	<a href="#"   data-dismiss="modal" class="close position-absolute  ">X</a>
                	<img class="w-100" src="{{ static_asset('frontend/images/diwali-sale.jpg') }}">
            </div>
        </div>
    </div> -->


</div><!-- END: body-wrap -->

<!-- SCRIPTS -->
<!-- <a href="#" class="back-to-top btn-back-to-top"></a> -->

<!-- Core -->
<script   src="{{ static_asset('frontend/js/vendor/popper.min.js') }}"></script>
<script  src="{{ static_asset('frontend/js/vendor/bootstrap.min.js') }}"></script>

<!-- Plugins: Sorted A-Z -->
<script    src="{{ static_asset('frontend/js/jquery.countdown.min.js') }}"></script>
<script src="{{ static_asset('frontend/js/select2.min.js') }}"></script>
<script   src="{{ static_asset('frontend/js/nouislider.min.js') }}"></script>
<script   src="{{ static_asset('frontend/js/sweetalert2.min.js') }}"></script>
<script src="{{ static_asset('frontend/js/slick.min.js') }}"></script>
<script src="{{ static_asset('frontend/js/jssocials.min.js') }}"></script>
<script src="{{ static_asset('frontend/js/bootstrap-tagsinput.min.js') }}"></script>
{{-- <script src="{{ static_asset('frontend/js/jodit.min.js') }}"></script> --}}
<script src="{{ static_asset('frontend/js/xzoom.min.js') }}"></script>
<script async src="{{ static_asset('frontend/js/fb-script.js') }}"></script>
<script   src="{{ static_asset('frontend/js/lazysizes.min.js') }}"></script>
<script src="{{ static_asset('frontend/js/intlTelInput.min.js') }}"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<!-- Global site tag (gtag.js) - Google Analytics -->


<!-- App JS -->
<script   src="{{ static_asset('frontend/js/active-shop.js') }}"></script>
<script    src="{{ static_asset('frontend/js/main.js') }}"></script>

<!-- diwali css -->
<style type="text/css">
  .categoriesss.diwali {background: url('{{ static_asset('frontend/images/diwali/crackers1.gif') }}') 0 100% no-repeat; background-size: 150px;}
  .categoriesss.diwali img.cracker {position: absolute; right: 0; max-width: 150px; top:10px;z-index: 0}
  .categoriesss.diwali .trending-category  {position: relative; z-index: 10}
  .top-navbar.diwali img.diya {position: absolute; left: 10px; top: 5px; max-width: 35px}
  .top-navbar.diwali img.diya1{position: absolute; right: 20px; top: 5px; max-width: 35px}
  .logo-bar-area.diwali img.swastik{position: absolute; left: 10px; top: 60px; }
  /*-webkit-animation:spin 4s linear infinite; -moz-animation:spin 4s linear infinite; animation:spin 4s linear infinite;*/
  .logo-bar-area.diwali img.swastik1{position: absolute; right: 20px; top: 60px;}
    #section_best_selling, #section_home_categories  {position: relative;}
  /*#section_home_categories   img.decor{position: absolute; left: 1px; top: 0px;}*/
  #section_best_selling.diwali:before {content: url('{{ static_asset('frontend/images/diwali/04.png') }}') ; position: absolute; left: 10px; top: 0;z-index: 1 }
  #section_best_selling.diwali:after {content: url('{{ static_asset('frontend/images/diwali/04-1.png') }}') ; position: absolute; right: 5px; top: 0;z-index: 1 }
  /*#section_best_selling.diwali:after {background: url('{{ static_asset('frontend/images/diwali/04.png') }}') left 0 no-repeat transparent!important; }*/
   #section_home_categories.diwali section:first-child:before {content: url('{{ static_asset('frontend/images/diwali/10.png') }}') ; position: absolute; left: 10px; top: 30px;z-index: 1;   -moz-transform: scaleX(-1);
    -webkit-transform: scaleX(-1);
    -o-transform: scaleX(-1);
    transform: scaleX(-1);
    -ms-filter: fliph; /*IE*/
    filter: fliph; /*IE*/ }
    #section_home_categories.diwali section {position: relative;}
  #section_home_categories.diwali section:first-child:after {content: url('{{ static_asset('frontend/images/diwali/10.png') }}') ; position: absolute; right: 15px; top: 30px;z-index: 1 }


  #section_home_categories.diwali section:nth-child(2):before {content: url('{{ static_asset('frontend/images/diwali/07.png') }}') ; position: absolute; left: 0px; top: 30px;z-index: 1; }

  #section_home_categories.diwali section:nth-child(2):after {content: url('{{ static_asset('frontend/images/diwali/07.png') }}') ; position: absolute; right: 15px; top: 30px;z-index: 1;  -moz-transform: scaleX(-1);
  -webkit-transform: scaleX(-1);
  -o-transform: scaleX(-1);
  transform: scaleX(-1);
  -ms-filter: fliph; /*IE*/
  filter: fliph; /*IE*/ }

  #section_home_categories.diwali section:nth-child(3):before {content: url('{{ static_asset('frontend/images/diwali/05.png') }}') ; position: absolute; left: -60px; top: 30px;z-index: 1; }
  #section_home_categories.diwali section:nth-child(3):after {content: url('{{ static_asset('frontend/images/diwali/05.png') }}') ; position: absolute; right: -60px; top: 30px;z-index: 1; }

#section_home_categories.diwali section:nth-child(4):before {content: url('{{ static_asset('frontend/images/diwali/08.png') }}') ; position: absolute; left: -10px; top: 30px;z-index: 1; }
#section_home_categories.diwali section:nth-child(4):after {content: url('{{ static_asset('frontend/images/diwali/08.png') }}') ; position: absolute; right: 0px; top: 30px;z-index: 1; }

#section_home_categories.diwali section:nth-child(7):before {content: url('{{ static_asset('frontend/images/diwali/12.png') }}') ; position: absolute; left: -10px; top: 30px;z-index: 1; }
#section_home_categories.diwali section:nth-child(7):after {content: url('{{ static_asset('frontend/images/diwali/12.png') }}') ; position: absolute; right: 0px; top: 30px;z-index: 1; }

#section_home_categories.diwali section:nth-child(8):before {content: url('{{ static_asset('frontend/images/diwali/06.png')}}') ; position: absolute; left: -10px; top: 30px;z-index: 1; }
#section_home_categories.diwali section:nth-child(8):after {content: url('{{ static_asset('frontend/images/diwali/06.png')}}') ; position: absolute; right: 10px; top: 30px;z-index: 1; -moz-transform: scaleX(-1);
  -webkit-transform: scaleX(-1);
  -o-transform: scaleX(-1);
  transform: scaleX(-1);
  -ms-filter: fliph; /*IE*/
  filter: fliph; /*IE*/ }
   #section_home_categories.diwali section:nth-child(9):before {content: url('{{ static_asset('frontend/images/diwali/10.png') }}') ; position: absolute; left: 10px; top: 30px;z-index: 1;   -moz-transform: scaleX(-1);
    -webkit-transform: scaleX(-1);
    -o-transform: scaleX(-1);
    transform: scaleX(-1);
    -ms-filter: fliph; /*IE*/
    filter: fliph; /*IE*/ }


 
  #section_home_categories.diwali section:nth-child(9):after {content: url('{{ static_asset('frontend/images/diwali/10.png') }}') ; position: absolute; right: 15px; top: 30px;z-index: 1 }


  #section_home_categories.diwali section:nth-child(10):before {content: url('{{ static_asset('frontend/images/diwali/12.png') }}') ; position: absolute; left: -10px; top: 30px;z-index: 1; }
#section_home_categories.diwali section:nth-child(10):after {content: url('{{ static_asset('frontend/images/diwali/12.png') }}') ; position: absolute; right: 0px; top: 30px;z-index: 1; }

#section_home_categories.diwali section:nth-child(11):before {content: url('{{ static_asset('frontend/images/diwali/08.png') }}') ; position: absolute; left: -10px; top: 30px;z-index: 1; }
#section_home_categories.diwali section:nth-child(11):after {content: url('{{ static_asset('frontend/images/diwali/08.png') }}') ; position: absolute; right: 0px; top: 30px;z-index: 1; }

  #section_banner_slider.diwali section  {background: url('{{ static_asset('frontend/images/diwali/crackers2.gif')}}') -150px  center repeat!important ;}
   #section_master_banner.diwali section  {background: url('{{ static_asset('frontend/images/diwali/crackers2.gif')}}') -150px  center repeat!important ;}
  .about_txt.diwali  {position: relative;}
  .about_txt.diwali    p {position: relative; z-index: 1; background: none!important}
  .about_txt.diwali  img.dd {position: absolute; max-width: 40%; left: 30%; z-index: 0; top: -5px; opacity: .4}
  .footer-top.diwali {position: relative;}
  .footer-top.diwali  img.diya {position: absolute; max-width: 50px; right: 0; z-index: 0; top: -45px;  }  
   .footer-top.diwali  img.diya1 {position: absolute; max-width: 50px; right: 0; z-index: 0; bottom: 0;  }  
   .footer.diwali { background: url('{{ static_asset('frontend/images/diwali/footer-bg.png')}}') -50px  110% repeat-x!important   ; }


  @-moz-keyframes spin { 100% { -moz-transform: rotate(360deg); } }
  @-webkit-keyframes spin { 100% { -webkit-transform: rotate(360deg); } }
  @keyframes spin { 100% { -webkit-transform: rotate(360deg); transform:rotate(360deg); } }

  @media (max-width: 640px){
    .top-navbar.diwali img.diya {left: 50%; top:38px}
     .top-navbar.diwali img.diya1 {top: 38px;}
     .logo-bar-area.diwali img.swastik {display: none;}
     .logo-bar-area.diwali img.swastik1 {right: 85px; top: 41px; width: 28px;}
     .categoriesss.diwali img.cracker {display: none;}
     .categoriesss.diwali {background: none}
     /*.categoriesss.diwali {background: url('{{ static_asset('frontend/images/diwali/crackers2.gif')}}') center center  repeat;}*/
     #section_best_selling.diwali:before, #section_best_selling.diwali:after {display: none;}
     #section_best_selling.diwali section {background: url('{{ static_asset('frontend/images/diwali/04-1.png')}}') 100% -25px  no-repeat; background-size: 40px;}

     #section_home_categories.diwali section:first-child:before {display: none;}
     #section_home_categories.diwali section:first-child:after {display: none;}
     #section_home_categories.diwali section:nth-child(2):before {left: -25px; top: -75px; z-index: 0; opacity: .5;}
      #section_home_categories.diwali section:nth-child(2):after {right: -25px; top: -75px; z-index: 0; opacity: .5;}
      #section_home_categories.diwali section:nth-child(3):before {content: url('{{ static_asset('frontend/images/diwali/03.png')}}'); top: -40px; left: 0}
        #section_home_categories.diwali section:nth-child(3):after {content: url('{{ static_asset('frontend/images/diwali/03.png')}}'); top: -50px; right: 0}
           #section_home_categories.diwali section:nth-child(4):before {content: url('{{ static_asset('frontend/images/diwali/08-1.png')}}'); top: -50px;  left: -7px; opacity: .5 }
        #section_home_categories.diwali section:nth-child(4):after {content: url('{{ static_asset('frontend/images/diwali/08-1.png')}}'); top: -50px; right: -6px ;  opacity: .5 }
        #section_home_categories.diwali section:nth-child(7):before {left: 12px; top: -30px;z-index: 0;opacity: .5; content: url({{ static_asset('frontend/images/diwali/02.png')}});}
         #section_home_categories.diwali section:nth-child(7):after {right: 12px; top: -30px;z-index: 0; 
         opacity: .5; content: url({{ static_asset('frontend/images/diwali/02.png')}});}
         #section_home_categories.diwali .slick-slider {z-index: 10}
         #section_home_categories.diwali .prod_info {background: #fff}
         #section_home_categories.diwali section:nth-child(8):before{display: none;}
          #section_home_categories.diwali section:nth-child(8):after {z-index: 0; top: -40px}
         #section_home_categories.diwali .sec_title .btn {z-index: 10}
         #section_home_categories.diwali section:nth-child(9):before {top: -250px; left: 12px;}
         #section_home_categories.diwali section:nth-child(9):after {top: -250px; right: 12px}
          #section_home_categories.diwali section:nth-child(10):before {left: -30px; top: -30px;z-index: 0;
         opacity: .5; display: none;}
         #section_home_categories.diwali section:nth-child(10):after {right: -30px; top: -30px;z-index: 0; display: none;
         opacity: .5;}
         /* #section_home_categories.diwali section:nth-child(11):before {content: url('{{ static_asset('frontend/images/diwali/08-1.png')}}'); display: none; top: -40px;  left: -11px; opacity: .8 }
        #section_home_categories.diwali section:nth-child(11):after {content: url('{{ static_asset('frontend/images/diwali/08-1.png')}}'); top: -40px; right: -9px ; display: none;  opacity: .8 }*/
         #section_home_categories.diwali section:nth-child(13)  { background: url(https://www.rozana.in/rozana_uat/public/frontend/images/diwali/crackers2.gif) -50px -175px no-repeat!important; background-size: 150px }
          #section_home_categories.diwali section:nth-child(13):after,  #section_home_categories.diwali section:nth-child(13):before {content: url('{{ static_asset('frontend/images/diwali/08-1.png')}}'); top: -40px; display: none; right: -9px ;  opacity: .8; position: absolute; }
          #section_home_categories.diwali section:nth-child(11):before {
          content: url(https://www.rozana.in/rozana_uat/public/frontend/images/diwali/10.png); top: -250px; left: 12px;}
          #section_home_categories.diwali section:nth-child(11):after {
          content: url(https://www.rozana.in/rozana_uat/public/frontend/images/diwali/10.png); top: -250px; right: 12px;}
           .about_txt.diwali img.dd {max-width: 100%; left: 0}
           .about_txt.diwali {position: relative; margin-top: 25px}
           .about_txt.diwali:before {content: url('{{ static_asset('frontend/images/diwali/02.png')}}'); top: 0px; right: 0px ;  position: absolute; }
          .footer-top.diwali {background: url('{{ static_asset('frontend/images/diwali/crackers3.gif')}}') center center no-repeat }
          .footer.diwali .footer-bottom {background: transparent!important;}
          .footer.diwali {  background-position: -120px 95%!important;}
  }
</style>
<script type="text/javascript">
  //diwali
  // $(document).ready(function(){
  
  //   $('.categoriesss, .top-navbar, .footer-top, .footer, .logo-bar-area, #section_best_selling, #section_home_categories, .about_txt, #section_banner_slider,#section_master_banner').addClass('diwali');
  //   $('.categoriesss.diwali').append("<img class='cracker' src='{{ static_asset('frontend/images/diwali/crackers1.gif') }}'>");
  //   $('.top-navbar.diwali').append("<img class='diya' src='{{ static_asset('frontend/images/diwali/Diya.gif') }}'>");
  //   $('.top-navbar.diwali').append("<img class='diya1' src='{{ static_asset('frontend/images/diwali/Diya.gif') }}'>");
  //   $('.logo-bar-area.diwali').append("<img class='swastik' src='{{ static_asset('frontend/images/diwali/02.png') }}'>");
  //   $('.logo-bar-area.diwali').append("<img class='swastik1' src='{{ static_asset('frontend/images/diwali/02.png') }}'>");
  //   $('.about_txt.diwali ').append("<img class='dd' src='{{ static_asset('frontend/images/diwali/d-bg.jpg') }}'>");
  //   $('.footer-top.diwali').append("<img class='diya' src='{{ static_asset('frontend/images/diwali/Diya.gif') }}'>");
  //   $('.footer-top.diwali').append("<img class='diya1' src='{{ static_asset('frontend/images/diwali/Diya.gif') }}'>");

     
  // });
</script>
<script type="text/javascript">
      //career page file upload css
      $(document).ready(function() {
     
            $('.career-page   .file').change(function(e) {
                var fname = e.target.files[0].name;
                $("#file").show();
                $("#file").text(fname);
 
            });
        });
    //  $( window ).load(function() {
	// 	  $('#offermodal').show();
	// 	  $('#offermodal').modal('show');
	// 	});

       

       
    // $('.loc').click(function(){
       //  $('.locationss ').fadeIn();
        // $('.overly').fadeIn();
    // });
    // $('.locationss .close').click(function(){
        //$('.locationss').hide();
       //  $('.overly').hide();
    //});


    //start for location window
            let y = getCookie("pincode");
            console.log(y);
            if(y != ""){
                 $(".pincode").hide();
                 $(".ovrlay-landing").hide();
            }
            function getCookie(cname) {
                let name = cname + "=";
                let ca = document.cookie.split(';');
                for(let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                    }
                }
                return "";
            }

            $(".loc").click(function(){
                $(".pincode").show();
                $(".ovrlay-landing").show();
            });
    //end for location window

    //show offer popup start

     $(document).ready(function() {
            //var isshow = localStorage.getItem('isshow');
            var x = getCookie("isshow");
            var date = new Date();
            var minutes = 60;
            console.log(x);
            date.setTime(date.getTime() + (minutes * 60 * 1000));
            if(y != ""){
            if (x == "") {
                //localStorage.setItem('isshow', 1);
                 
                // Show popup here
                 //$('#offermodal').modal('show');
                 //$.cookie("isshow", "111" , { expires: date });

            }
             }else{

             }
        }); 

    //show offer popup ends

    //on select pincode
     $('.area_id').on('change',function(){
       var city =  $('#city_id').find('option:selected').val();
       var pin = $(this).val();
       document.getElementById("spinnig_arrow").style.display = "";
       $.post('{{ route('addresses.set_location') }}',{_token:'{{ csrf_token() }}', city:city,pin:pin}, function(data){
           window.location.reload();
           document.getElementById("spinnig_arrow").style.display = "hidden";
                
        });
    });

    // locaiton popup
        $('.close_btn').click(function(){ 
            $('.location-landing').addClass('hide');
             $('.ovrlay-landing').hide();
        })
        $('.loc').click(function(){
             $('.location-landing').removeClass('hide');
             $('.location-landing').addClass('show');
            $('.ovrlay-landing').fadeIn();
         });

        

      $('.pass a').click(function(){
        $(this).find('i').toggleClass("fa-eye-slash");
        var input = $(this).parent().find('input');
         if (input.attr("type") === "password") { 
            input.attr("type", "text");
          } 
          else   {
            input.attr("type", "password");
          }  
        // $('.overly').fadeIn();
     });

      // $( ".login-btn" ).hover(
      //   function() {
      //         $( '.overly' ).fadeIn( 500 );
      //        },

      //        function() {
      //         $( '.overly' ).fadeOut( 100 );
      //        }
      // );

      

</script>
<script>
    function showFrontendAlert(type, message){
        if(type == 'danger'){
            type = 'error';
        }
        swal({
            position: 'top-end',
            type: type,
            title: message,
            showConfirmButton: false,
            timer: 3000
        });
    }
    @if(Session::has('success'))
    showFrontendAlert('success','{{ Session::get("success")}}');
    @endif
</script>

@foreach (session('flash_notification', collect())->toArray() as $message)
    <script>
        showFrontendAlert('{{ $message['level'] }}', '{{ $message['message'] }}');
    </script>
@endforeach
<script>

    $(document).ready(function() {
        $('.category-nav-element').each(function(i, el) {
            $(el).on('mouseover', function(){
                if(!$(el).find('.sub-cat-menu').hasClass('loaded')){
                    $.post('{{ route('category.elements') }}', {_token: '{{ csrf_token()}}', id:$(el).data('id')}, function(data){
                        $(el).find('.sub-cat-menu').addClass('loaded').html(data);
                    });
                }
            });
        });
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

        if ($('#currency-change').length > 0) {
            $('#currency-change .dropdown-item a').each(function() {
                $(this).on('click', function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var currency_code = $this.data('currency');
                    $.post('{{ route('currency.change') }}',{_token:'{{ csrf_token() }}', currency_code:currency_code}, function(data){
                        location.reload();
                    });

                });
            });
        }
    });

    $('#search').on('keyup', function(){
        search();
    });

    $('#search').on('focus', function(){
        search();
    });

    function search(){
        var search = $('#search').val();
        if(search.length > 0){
            $('body').addClass("typed-search-box-shown");

            $('.typed-search-box').removeClass('d-none');
            $('.search-preloader').removeClass('d-none');
            $.post('{{ route('search.ajax') }}', { _token: '{{ @csrf_token() }}', search:search}, function(data){
                if(data == '0'){
                    // $('.typed-search-box').addClass('d-none');
                    $('#search-content').html(null);
                    $('.typed-search-box .search-nothing').removeClass('d-none').html('Sorry, nothing found for <strong>"'+search+'"</strong>');
                    $('.search-preloader').addClass('d-none');

                }
                else{
                    $('.typed-search-box .search-nothing').addClass('d-none').html(null);
                    $('#search-content').html(data);
                    $('.search-preloader').addClass('d-none');
                }
            });
        }
        else {
            $('.typed-search-box').addClass('d-none');
            $('body').removeClass("typed-search-box-shown");
        }
    }

    function updateNavCart(){
        $.post('{{ route('cart.nav_cart') }}', {_token:'{{ csrf_token() }}'}, function(data){
            $('#cart_items').html(data);
        });
    }

    function removeFromCart(key,productId=""){
        
        $.post('{{ route('cart.removeFromCart') }}', {_token:'{{ csrf_token() }}', key:key,delivery_page:1}, function(data){
            updateNavCart();
            $("."+key).fadeTo("slow",0.7, function(){
                $("."+key).remove();
            });
            $('#cart-summary').html(data);
            if(productId!="")
            {
                $("#btn_add"+productId).removeAttr('style');
                $("#pamount_"+productId).val(0);
            }
            if(parseInt($("#can-process").val())==1)
            {
                $("#process").removeAttr('disabled');
            }

            var max_cod_amount = "{{ env('MAX_COD_AMOUNT') }}";
            var total_amount = parseFloat($('#total_amount').val());
            if(total_amount<=parseInt(max_cod_amount)){
                //enable cod and hide cod not available alert
                $("#cod").show();
                $("#cod_not_avail").hide();
            }

            showFrontendAlert('success', 'Item has been removed from cart');
            //window.location.reload();
            $('#cart_items_sidenav').html(parseInt($('#cart_items_sidenav').html())-1);
        });
    }

    function addToCompare(id){
        $.post('{{ route('compare.addToCompare') }}', {_token:'{{ csrf_token() }}', id:id}, function(data){
            $('#compare').html(data);
            showFrontendAlert('success', "{{ translate('Item has been added to compare list') }}");
            $('#compare_items_sidenav').html(parseInt($('#compare_items_sidenav').html())+1);
        });
    }

    function addToWishList(id){
        @if (Auth::check() && (Auth::user()->user_type == 'customer' || Auth::user()->user_type == 'seller' || Auth::user()->user_type == 'partner'))
            $.post('{{ route('wishlists.store') }}', {_token:'{{ csrf_token() }}', id:id}, function(data){
                if(data != 0){
                    $('#wishlist').html(data);
                    showFrontendAlert('success', "{{ translate('Item has been added to wishlist') }}");
                    window.location.reload();
                }
                else{
                    showFrontendAlert('warning', "{{ translate('Please login first') }}");
                }
            });
        @else
            showFrontendAlert('warning', "{{ translate('Please login first') }}");
        @endif
    }

    function showAddToCartModal(id){
        if(!$('#modal-size').hasClass('modal-lg')){
            $('#modal-size').addClass('modal-lg');
        }
        $('#addToCart-modal-body').html(null);
        $('#addToCart').modal();
        $('.c-preloader').show();
        $.post('{{ route('cart.showCartModal') }}', {_token:'{{ csrf_token() }}', id:id}, function(data){
            $('.c-preloader').hide();
            $('#addToCart-modal-body').html(data);
            $('.xzoom, .xzoom-gallery').xzoom({
                Xoffset: 20,
                bg: true,
                tint: '#000',
                defaultScale: -1
            });
            getVariantPrice();
        });
    }

    $('#option-choice-form input').on('change', function(){
        getVariantPrice();
    });

    function getVariantPrice(){
        // alert('rozana');
        

        if($('#option-choice-form input[name=quantity]').val() > 0 && checkAddToCartValidity()){
            $.ajax({
               type:"POST",
               url: '{{ route('products.variant_price') }}',
               data: $('#option-choice-form').serializeArray(),
               success: function(data){
                   $('#option-choice-form #chosen_price_div').removeClass('d-none');
                   $('#option-choice-form #chosen_price_div #chosen_price').html(data.price);
                   $('#available-quantity').html(data.quantity);
                   $('.input-number').prop('max', data.quantity);
                   //console.log(data.quantity);
                   if(parseInt(data.quantity) < 1 && data.digital  != 1){
                       $('.buy-now').hide();
                       $('.add-to-cart').hide();
                   }
                   else{
                       $('.buy-now').show();
                       $('.add-to-cart').show();
                   }
               }
           });
        }
    }

    function getCartQty()
    {
          $.ajax({
            type:'post',
            url:"{{ route('cart.cart_qty')}}",
            data:{_token:"{{ csrf_token() }}",pid:$("#pid").val()},
            success:function(cart_qty){
                $("#cart_qty").val(cart_qty);
                
               
            }
                });
        

    }

    function totalCartItem()
    {
        $.ajax({
            type:'post',
            url:"{{ route('cart.total_items')}}",
            data:{_token:"{{ csrf_token() }}"},
            success:function(total_items){
                $('#cart_items_sidenav').html(total_items);
                $('#cart_items_sidenav_mobile').text(total_items);
                
            }
                });
    }
    

function checkBuyLimit(cart_qty)
{
                var cartQty=parseInt(cart_qty);
                var limitQty = parseInt($("#limit_qty").val());
        
                var prQty = parseInt($('#option-choice-form input[name=quantity]').val());
                var totalQty = prQty+cartQty;
                if(limitQty < totalQty){
                    showFrontendAlert('danger','Maximum Limit has been reached');
                    return false;
                }
                return true;
               
}


    


    function checkAddToCartValidity(){
        var names = {};
        $('#option-choice-form input:radio').each(function() { // find unique names
              names[$(this).attr('name')] = true;
        });
        var count = 0;
        $.each(names, function() { // then count them
              count++;
        });

        if($('#option-choice-form input:radio:checked').length == count){
            return true;
        }

        return false;
    }

    function addToCart(){
       
        @if(!Cookie::has('pincode'))
            showFrontendAlert('danger','Select Your Location'); 
            $(".quant_add_btn").show();
            return false;
        @endif
        getCartQty();
        if(checkAddToCartValidity()) {
            if(checkBuyLimit($("#cart_qty").val())==false){
                return false;
            }
            $('#addToCart').modal();
            $('.c-preloader').show();
            $.ajax({
               type:"POST",
               url: '{{ route('cart.addToCart') }}',
               data: $('#option-choice-form').serializeArray(),
               success: function(data){
                   $('#addToCart-modal-body').html(null);
                   $('.c-preloader').hide();
                   $('#modal-size').removeClass('modal-lg');
                   $('#addToCart-modal-body').html(data);
                   updateNavCart();
                   getCartQty();
                   totalCartItem();
               }
           });
        }
        else{
            showFrontendAlert('warning', 'Please choose all the options');
        }
    }

    function buyNow(){
        @if(!Cookie::has('pincode'))
            showFrontendAlert('danger','Select Your Location'); 
            $(".quant_add_btn").show();
            return false;
        @endif
        getCartQty();
        if(checkAddToCartValidity()) {
            if(checkBuyLimit($("#cart_qty").val())==false){
                return false;
            }
            $('#addToCart').modal();
            $('.c-preloader').show();
            $.ajax({
               type:"POST",
               url: '{{ route('cart.addToCart') }}',
               data: $('#option-choice-form').serializeArray(),
               success: function(data){
                   //$('#addToCart-modal-body').html(null);
                   //$('.c-preloader').hide();
                   //$('#modal-size').removeClass('modal-lg');
                   //$('#addToCart-modal-body').html(data);
                   updateNavCart();
                   getCartQty();
                   $('#cart_items_sidenav').html(parseInt($('#cart_items_sidenav').html())+1);
                   window.location.replace("{{ route('cart') }}");
               }
           });
        }
        else{
            showFrontendAlert('warning', 'Please choose all the options');
        }
    }

    function show_purchase_history_details(order_id)
    {
        $('#order-details-modal-body').html(null);

        if(!$('#modal-size').hasClass('modal-lg')){
            $('#modal-size').addClass('modal-lg');
        }

        $.post('{{ route('purchase_history.details') }}', { _token : '{{ @csrf_token() }}', order_id : order_id}, function(data){
            $('#order-details-modal-body').html(data);
            $('#order_details').modal();
            $('.c-preloader').hide();
        });
    }

    function show_order_details(order_id)
    {
        $('#order-details-modal-body').html(null);

        if(!$('#modal-size').hasClass('modal-lg')){
            $('#modal-size').addClass('modal-lg');
        }

        $.post('{{ route('orders.details') }}', { _token : '{{ @csrf_token() }}', order_id : order_id}, function(data){
            $('#order-details-modal-body').html(data);
            $('#order_details').modal();
            $('.c-preloader').hide();
        });
    }

    function cartQuantityInitialize(){
        $('.btn-number').click(function() {
            //e.preventDefault();
            fieldName = $(this).attr('data-field');
            console.log(fieldName);
            type = $(this).attr('data-type');
            var input = $("input[name='" + fieldName + "']");
            var currentVal = parseInt(input.val());

            if (!isNaN(currentVal)) {
                if (type == 'minus') {
                    console.log('minus');
                    if (currentVal > input.attr('min')) {
                        console.log('current value is greater than min value');
                        input.val(currentVal - 1).change();
                    }
                    if (parseInt(input.val()) == input.attr('min')) {
                       console.log('current value is equal to min');
                        $(this).attr('disabled', true);
                    }

                } else if (type == 'plus') {
                    console.log('plus');
                    if (currentVal < input.attr('max')) {
                        console.log('current value is less than max value ');
                        input.val(currentVal + parseInt(1)).change();
                    }
                    if (parseInt(input.val()) == input.attr('max')) {
                        console.log('current value is equal to max value ');                       
                        showFrontendAlert('info','Sorry, the maximum limit was reached');
                        //$(this).attr('disabled', true);
                    }

                }
            } else {
                console.log("value set to 0");
                input.val(0);
            }
        });

        $('.input-number').focusin(function() {
          console.log('old value focus in');
            $(this).data('oldValue', $(this).val());
        });

        $('.input-number').change(function() {
            minValue = parseInt($(this).attr('min'));
            maxValue = parseInt($(this).attr('max'));
            valueCurrent = parseInt($(this).val());
            name = $(this).attr('name');
            if (valueCurrent >= minValue) {
                //console.log('current value is greater than =  min Value');
                $(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
            } else {
                //console.log('current value is less than min value');
                alert('Sorry, the minimum limit was reached');
                $(this).val($(this).data('oldValue'));
            }
            if (valueCurrent <= maxValue) {
                //console.log('current value is less than max value');
                $(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
            } else {
                //console.log('current value is greater than max value');
                alert('Sorry, the maximum limit was reached');
                $(this).val($(this).data('oldValue'));
            }

        });

        $(".input-number").keydown(function(e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                // let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    }

     function imageInputInitialize(){
         $('.custom-input-file').each(function() {
             var $input = $(this),
                 $label = $input.next('label'),
                 labelVal = $label.html();

             $input.on('change', function(e) {
                 var fileName = '';

                 if (this.files && this.files.length > 1)
                     fileName = (this.getAttribute('data-multiple-caption') || '').replace('{count}', this.files.length);
                 else if (e.target.value)
                     fileName = e.target.value.split('\\').pop();

                 if (fileName)
                     $label.find('span').html(fileName);
                 else
                     $label.html(labelVal);
             });

             // Firefox bug fix
             $input
                 .on('focus', function() {
                     $input.addClass('has-focus');
                 })
                 .on('blur', function() {
                     $input.removeClass('has-focus');
                 });
         });
     }

     function cart_loader(status,id){

        var loading = "<div class='spinner-border text-success' role='status'>\
                                                    <span class='sr-only'>Loading...</span>\
                                                </div>";
        $("#cart_loader"+id).html(loading);   
        
        if(status=="show"){
            $("#cart_loader"+id).show();
            $("#button-group"+id).addClass('disabledbutton');
        }
        else if(status=="hide"){
            $("#cart_loader"+id).hide();
            $("#button-group"+id).removeClass('disabledbutton');
        }
        else{
            $("#cart_loader"+id).hide(); 
            $("#button-group"+id).removeClass('disabledbutton');
        }
    }

function removeMultipleProduct(){
            var someObj = {};
                    someObj.fruitsGranted = [];
                    $("input[name='productsid[]']:checked:enabled").each(function () {
                    someObj.fruitsGranted.push($(this).val());
                });
                     //alert(someObj.fruitsGranted);
                    $.post('{{ route('cart.remove_cart_products') }}',{_token:'{{ csrf_token() }}', p_id:someObj.fruitsGranted}, function(data){
                   if(data==1){
                        location.reload();
                        showFrontendAlert('success','Product removed successfully');
                   }else{
                        showFrontendAlert('danger','Something went wrong');
                   }
                });
}

function selectAll(obj){
    if($(obj).prop('checked') == true){
    $('.selectedId').prop('checked', 'checked');
       
    }
    else{
        $('.selectedId').removeAttr('checked');
    }
    if($('.selectedId').filter(":checked").length>0){
            $(".remove_btn").show();
        }
        else{
            $(".remove_btn").hide();
        }
    
}

function singleCheck(){
    var check = ($('.selectedId').filter(":checked").length == $('.selectedId').length);
        $('#selectall').prop("checked", check);
        if($('.selectedId').filter(":checked").length>0){
            $(".remove_btn").show();
        }
        else{
            $(".remove_btn").hide();
        }
}


</script>

@yield('script')

</body>
</html>
