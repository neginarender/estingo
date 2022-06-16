<!-- FOOTER -->
<footer id="footer" class="footer">
   
    <div class="container">
        <div class="footer-top">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                  <div class="col-md-3 col-lg-3 col-sm-6 col-6">
                    <div class="col text-center text-md-left brdr-r">
                       <h4 class="heading heading-xs strong-600 text-uppercase mb-2">
                          Useful Links
                       </h4>

                       <ul class="footer-links">
                            @if (Auth::check())
                                <li>
                                    <a href="{{ route('dashboard') }}">
                                        My Account
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route('user.login') }}">
                                        Login
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a href="{{ route('purchase_history.index') }}">
                                    Order History
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('wishlists.index') }}">
                                    My Wishlist
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('orders.track') }}">
                                    Track Order
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('careers.index') }}">
                                    Careers
                                </a>
                            </li>
                            @if (\App\Addon::where('unique_identifier', 'affiliate_system')->first() != null && \App\Addon::where('unique_identifier', 'affiliate_system')->first()->activated)
                                <li>
                                    <a href="{{ route('affiliate.apply') }}">Be an affiliate partner</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                   
                </div>
                 <div class="col-lg-3 col-md-3 col-sm-6 col-6">
                    <div class="col text-center text-md-left brdr-r">
                        <h4 class="heading heading-xs strong-600 text-uppercase mb-2">
                            Help
                        </h4>
                        <ul class="footer-links">
                                 <li>
                                    <a href="{{ route('aboutus') }}" title="">
                                        About
                                    </a>
                                </li>
                                 {{-- <li>
                                    <a href="{{ route('sellerpolicy') }}" title="">
                                        FAQs
                                    </a>
                                </li> --}}
                                 <li>
                                    <a href="{{ route('contactus') }}" title="">
                                        Contact Us
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('privacypolicy') }}" title="">
                                        Privacy Policy
                                    </a>
                                </li>
                                {{-- <li>
                                    <a href="{{ route('returnpolicy') }}" title="">
                                        Return Policy
                                    </a>
                                </li> --}}
                                <li>
                                    <a href="{{ route('terms') }}" title="">
                                        Terms and Conditions
                                    </a>
                                </li>
                                {{-- <li>
                                    <a href="{{ route('sellerpolicy') }}" title="">
                                        Seller Policy
                                    </a>
                                </li> --}}
                                <li>
                                    <a href="{{ route('peer-partner.create') }}" title="Become a peer partner">
                                        Become a Partner
                                    </a>
                                </li>
                        </ul>
                    </div>
                </div>
                @php
                    $generalsetting = \App\GeneralSetting::first();
                @endphp
                <div class="col-lg-6 col-xl-6 col-md-6   text-md-left">
                    <div class="col ">
                          <h4 class="heading heading-xs strong-600 text-uppercase mb-2">
                            {{ translate('Download Our App') }}
                          </h4>  
                           <a href="https://play.google.com/store/apps/details?id=com.rozana.customer"> <img style="max-width: 125px"  class="  lazyload  " src="{{ static_asset('frontend/images/homepage/footer/play_store.png') }}" alt="{{ env('APP_NAME') }}"></a>
                           <a href="https://apps.apple.com/us/app/rozana-in/id1602674271" class=" ml-1 "> <img style="max-width: 125px" class="lazyload " src="{{ static_asset('frontend/images/homepage/footer/apple (1).png') }}" alt="{{ env('APP_NAME') }}"></a>  
                             <div class="sep"></div>  
                            <h4 class="heading heading-xs strong-600 text-uppercase mb-2">
                            {{ translate('Follow us on') }}
                          </h4>
                        <ul class=" my-3 mt-4 my-md-0 social-nav model-2">
                        @if ($generalsetting->facebook != null)
                            <li>
                                <a href="{{ $generalsetting->facebook }}"   target="_blank" data-toggle="tooltip" data-original-title="Facebook">
                                     <img class=" ml-1 lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/footer/fb.png') }}" alt="{{ env('APP_NAME') }}">
                                </a>
                            </li>
                        @endif
                        @if ($generalsetting->instagram != null)
                            <li>
                                <a href="{{ $generalsetting->instagram }}"   target="_blank" data-toggle="tooltip" data-original-title="Instagram">
                                   <img class=" ml-1 lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/footer/insta.png') }}" alt="{{ env('APP_NAME') }}">
                                </a>
                            </li>
                        @endif

                            <li>
                                <a href="https://www.linkedin.com/company/rozana/"   target="_blank" data-toggle="tooltip" data-original-title="Linkedin">
                                    <img class=" ml-1 lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/footer/linkedin.png') }}" alt="{{ env('APP_NAME') }}">
                                </a>
                            </li>
                        
                        @if ($generalsetting->twitter != null)
                            <li>
                                <a href="{{ $generalsetting->twitter }}"   target="_blank" data-toggle="tooltip" data-original-title="Twitter">
                                    <img class=" ml-1 lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/footer/twiiter.png') }}" alt="{{ env('APP_NAME') }}">
                                </a>
                            </li>
                        @endif
                  
                    </ul>
                        
                    </div>
                </div>
                
              
             
                
                <!--term and condition end-->
 
            </div>
        </div>
    </div>

    <div class="footer-bottom py-3 sct-color-3">
        <div class="container">
            <div class="row align-items-center">
                 <div class="col-md-6">
                    <div class="copyright    ">
                         <img class=" ml-1 lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/footer/amex-332bc23.png') }}" alt="{{ env('APP_NAME') }}">
                          <img class=" ml-1 lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/footer/bhim-upi-3c1ef19.png') }}" alt="{{ env('APP_NAME') }}">
                           <img class=" ml-1 lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/footer/maestro-be32af5.png') }}" alt="{{ env('APP_NAME') }}">
                            <img class=" ml-1 lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/footer/mastercard-fafd4ad.png') }}" alt="{{ env('APP_NAME') }}">
                             <img class=" ml-1 lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/footer/mobikwik-6d9eed3.png') }}" alt="{{ env('APP_NAME') }}"> 
                             <img class=" ml-1 lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/footer/paytm-1cc911c.png') }}" alt="{{ env('APP_NAME') }}">
                             <img class=" ml-1 lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/footer/rupay-77f4f26.png') }}" alt="{{ env('APP_NAME') }}">
                             <img class=" ml-1 lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/footer/visa-42f212a.png') }}" alt="{{ env('APP_NAME') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="copyright text-right   ">
                        <ul class="copy-links no-margin text-white">
                            <li>
                               Copyright © Rozana Social Commerce Private Limited
                            </li>
                             
                        </ul>
                    </div>
                </div> 
                
            </div>
        </div>
    </div>
</footer>


<div class="aiz-mobile-bottom-nav d-md-none fixed-bottom bg-white shadow-lg border-top">

    <div class="d-flex justify-content-around align-items-center">

        <a href="{{ route('home') }}" class="text-reset flex-grow-1 text-center py-3 border-right bg-soft-primary">

            <i class="la la-home la-2x"></i>

        </a>

        <a href="{{ url('/') }}/categories" class="text-reset flex-grow-1 text-center py-3 border-right ">

            <span class="d-inline-block position-relative px-2">

                <i class="la la-list-ul la-2x"></i>

            </span>

        </a>

        <a href="{{ route('cart') }}" class="text-reset flex-grow-1 text-center py-3 border-right ">

            <span class="d-inline-block position-relative px-2">

                <i class="la la-shopping-cart la-2x"></i>

                    @if(Session::has('cart'))
                        <span class="badge badge-circle badge-success position-absolute absolute-top-right" id="cart_items_sidenav_mobile">{{ count(Session::get('cart'))}}</span>
                    @else
                        <span class="badge badge-circle badge-success position-absolute absolute-top-right" id="cart_items_sidenav_mobile">0</span>
                    @endif

                
            </span>

        </a>

        
            <a href="{{ route('dashboard') }}" class="text-reset flex-grow-1 text-center py-2">

                <span class="avatar avatar-xs d-block mx-auto">
 
                    <img width="30px"  class="bd-radius" src="{{ static_asset('frontend/images/icons/user-placeholder.jpg') }}">

                </span>

            </a>

        
    </div>

</div>