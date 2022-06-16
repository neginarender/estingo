@extends('frontend.layouts.app')

@section('content')
    <section class="home-banner-area ">
        <div class="">
            <div class="row no-gutters position-relative">
                <!-- <div class="col-lg-2 position-static order-2 order-lg-0">
                    <div class="category-sidebar">
                        <div class="all-category d-none d-lg-block">
                            <span >{{ translate('Categories') }}</span>
                            <a href="{{ route('categories.all') }}">
                                <span class="d-none d-lg-inline-block">{{ translate('See All') }} ></span>
                            </a>
                        </div>
                        <ul class="categories no-scrollbar">
                            <li class="d-lg-none">
                                <a href="{{ route('categories.all') }}" class="text-truncate">
                                    <img class="cat-image lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{ static_asset('frontend/images/icons/list.png') }}" width="30" alt="{{ translate('All Category') }}">
                                    <span class="cat-name">{{ translate('All') }} <br> {{ translate('Categories') }}</span>
                                </a>
                            </li>
                            @foreach (\App\Category::all()->take(11) as $key => $category)
                                @php
                                    $brands = array();                                          
                                @endphp
                                <li class="category-nav-element" data-id="{{ $category->id }}">
                                    <a href="{{ route('products.category', $category->slug) }}" class="text-truncate">
                                        <img class="cat-image lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{Storage::disk('s3')->url($category->icon) }}" width="30" alt="{{ __($category->name) }}">
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
                </div> -->

                @php
                    $num_todays_deal = count(filter_products(\App\Product::where('published', 1)->where('todays_deal', 1 ))->get());
                    $featured_categories = \App\Category::where('featured', 1)->get();
                @endphp

                <div class="@if($num_todays_deal > 0) col-lg-12 @else col-lg-12 @endif   @if(count($featured_categories) == 0) home-slider-full @endif">
                    <div class="home-slide">
                        <div class="home-slide">
                            <div class="slick-carousel" data-slick-arrows="true" data-slick-dots="true" data-slick-autoplay="true">
                                @foreach (\App\Slider::where('published', 1)->get() as $key => $slider)
                                    <div class="home-slide-item" style="height:275px;">
                                        <div class="container position-relative">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <div class="ovrlay_cont">
                                                        <h1>Fresh <span>Grocery</span> Shopping</h1>
                                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                                            hendrerit nisi sed sollicitudin pellentesque. Nunc posuere
                                                            purus rhoncus pulvinar aliquam.</p>
                                                            <a href="" class="btn btn-success btn-lg">Order Now</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="{{ $slider->link }}" target="_blank">

                                        <img class="d-block w-100 h-100 lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder-rect.jpg')}}" data-src="{{Storage::disk('s3')->url($slider->photo)}}" alt="{{ env('APP_NAME')}} promo">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                   
                </div>

              <!--   @if($num_todays_deal > 0)
                <div class="col-lg-2 d-none d-lg-block">
                    <div class="flash-deal-box bg-white h-100">
                        <div class="title text-center p-2 gry-bg">
                            <h3 class="heading-6 mb-0">
                                {{ translate('Todays Deal') }}
                                <span class="badge badge-danger">{{ translate('Hot') }}</span>
                            </h3>
                        </div>
                        <div class="flash-content c-scrollbar c-height">
                            @foreach (filter_products(\App\Product::where('published', 1)->where('todays_deal', '1'))->get() as $key => $product)
                                @if ($product != null)
                                    <a href="{{ route('product', $product->slug) }}" class="d-block flash-deal-item">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col">
                                                <div class="img">
                                                    <img class="lazyload img-fit" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{ Storage::disk('s3')->url($product->thumbnail_img)}}" alt="{{ __($product->name) }}">
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="price">
                                                    <span class="d-block">{{ home_discounted_base_price($product->id) }}</span>
                                                    @if(home_base_price($product->id) != home_discounted_base_price($product->id))
                                                        <del class="d-block">{{ home_base_price($product->id) }}</del>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif -->

            </div>
        </div>
    </section>

    @php
        $flash_deal = \App\FlashDeal::where('status', 1)->where('featured', 1)->first();
    @endphp
    @if($flash_deal != null && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date)
    <section class="mb-4">

        <div class="container">
            <div class="px-2 py-4 p-md-4 bg-white shadow-sm">
                <div class="section-title-1 clearfix ">
                    <h3 class="heading-5 strong-700 mb-0 float-left">
                        {{ translate('Flash Sale') }}
                    </h3>
                    <div class="flash-deal-box float-left">
                        <div class="countdown countdown--style-1 countdown--style-1-v1 " data-countdown-date="{{ date('m/d/Y', $flash_deal->end_date) }}" data-countdown-label="show"></div>
                    </div>
                    <ul class="inline-links float-right">
                        <li><a href="{{ route('flash-deal-details', $flash_deal->slug) }}" class="active">{{ translate('View More') }}</a></li>
                    </ul>
                </div>
                <div class="caorusel-box arrow-round gutters-5">
                    <div class="slick-carousel" data-slick-items="6" data-slick-xl-items="5" data-slick-lg-items="4"  data-slick-md-items="3" data-slick-sm-items="2" data-slick-xs-items="2">
                    @foreach ($flash_deal->flash_deal_products as $key => $flash_deal_product)
                        @php
                            $product = \App\Product::find($flash_deal_product->product_id);
                        @endphp
                        @if ($product != null && $product->published != 0)
                            <div class="caorusel-card">
                                <div class="product-card-2 card card-product shop-cards">
                                    <div class="card-body p-0">
                                        <div class="card-image">
                                            <a href="{{ route('product', $product->slug) }}" class="d-block">
                                                <img class="img-fit lazyload mx-auto" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{Storage::disk('s3')->url($product->thumbnail_img)}}" alt="{{ __($product->name) }}">
                                            </a>
                                        </div>

                                        <div class="p-md-3 p-2">
                                            <div class="price-box">
                                                @if(home_base_price($product->id) != home_discounted_base_price($product->id))
                                                    <del class="old-product-price strong-400">{{ home_base_price($product->id) }}</del>
                                                @endif
                                                <span class="product-price strong-600">{{ home_discounted_base_price($product->id) }}</span>
                                            </div>
                                            <div class="star-rating star-rating-sm mt-1">
                                                {{ renderStarRating($product->rating) }}
                                            </div>
                                            <h2 class="product-title p-0">
                                                <a href="{{ route('product', $product->slug) }}" class=" text-truncate">{{ __($product->name) }}</a>
                                            </h2>
                                            @if (\App\Addon::where('unique_identifier', 'club_point')->first() != null && \App\Addon::where('unique_identifier', 'club_point')->first()->activated)
                                                <div class="club-point mt-2 bg-soft-base-1 border-light-base-1 border">
                                                    {{ translate('Club Point') }}:
                                                    <span class="strong-700 float-right">{{ $product->earn_point }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif




   
   <div class="categoriesss bg-white pt-5 pb-5">
        <div class="container"> 
         <div class="row">
                <div class="col-md-12  "> 
                    <div class="sec_title  ">
                         <a href="" class="btn btn-success btn-sm float-right mt-1">All Categories</a>
                         <h4>What do you looking for?</h4>
                    </div> 
                </div>

              <div class="col-md-12 mt-2">      
                  
                    <div class="trending-category  d-none d-lg-block">
                        <div class="caorusel-box arrow-round gutters-5">
                            <div class="slick-carousel" data-slick-items="6" data-slick-xl-items="6" data-slick-lg-items="6"  data-slick-md-items="3" data-slick-sm-items="2" data-slick-xs-items="2">
                                
                                    <div class="caorusel-card">
                                         <div class="trend-category-single">
                                            <a href="" class="d-block">
                                                <div class="img">
                                                   <img src="{{ static_asset('frontend/images/homepage/sec1/gorcery_staple.jpg') }}" alt="{{ env('APP_NAME') }}">
                                                </div>
                                                 <div class="name">
                                                    Grocery & Staple
                                                 </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="caorusel-card">
                                         <div class="trend-category-single">
                                            <a href="" class="d-block">
                                                <div class="img">
                                                   <img src="{{ static_asset('frontend/images/homepage/sec1/household_items.jpg') }}" alt="{{ env('APP_NAME') }}">
                                                </div>
                                                 <div class="name">
                                                    Household Items
                                                 </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="caorusel-card">
                                         <div class="trend-category-single">
                                            <a href="" class="d-block">
                                                <div class="img">
                                                   <img src="{{ static_asset('frontend/images/homepage/sec1/snacks.jpg') }}" alt="{{ env('APP_NAME') }}">
                                                </div>
                                                 <div class="name">
                                                    Biscuits, Snacks & Choclates
                                                 </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="caorusel-card">
                                         <div class="trend-category-single">
                                            <a href="" class="d-block">
                                                <div class="img">
                                                   <img src="{{ static_asset('frontend/images/homepage/sec1/veggi.jpg') }}" alt="{{ env('APP_NAME') }}">
                                                </div>
                                                 <div class="name">
                                                   Vegetables & Fruits
                                                 </div>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="caorusel-card">
                                         <div class="trend-category-single">
                                            <a href="" class="d-block">
                                                <div class="img">
                                                   <img src="{{ static_asset('frontend/images/homepage/sec1/personal_care.jpg') }}" alt="{{ env('APP_NAME') }}">
                                                </div>
                                                 <div class="name">
                                                   Personal Care
                                                 </div>
                                            </a>
                                        </div>
                                    </div>
                                     <div class="caorusel-card">
                                         <div class="trend-category-single">
                                            <a href="" class="d-block">
                                                <div class="img">
                                                   <img src="{{ static_asset('frontend/images/homepage/sec1/kitchen_dining.jpg') }}" alt="{{ env('APP_NAME') }}">
                                                </div>
                                                 <div class="name">
                                                   Kitchen & Dining Needs
                                                 </div>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="caorusel-card">
                                         <div class="trend-category-single">
                                            <a href="" class="d-block">
                                                <div class="img">
                                                   <img src="{{ static_asset('frontend/images/homepage/sec1/breakfast_dairy.jpg') }}" alt="{{ env('APP_NAME') }}">
                                                </div>
                                                 <div class="name">
                                                   Breakfast & Dairy
                                                 </div>
                                            </a>
                                        </div>
                                    </div>

                                     <div class="caorusel-card">
                                         <div class="trend-category-single">
                                            <a href="" class="d-block">
                                                <div class="img">
                                                   <img src="{{ static_asset('frontend/images/homepage/sec1/beverages.jpg') }}" alt="{{ env('APP_NAME') }}">
                                                </div>
                                                 <div class="name">
                                                   Beverages
                                                 </div>
                                            </a>
                                        </div>
                                    </div>
                                     

                                
                            </div>
                        </div>
                         
                    </div>
                
            </div>
        </div>
     </div>

   </div>

   <section class="pt-4 bg-white pb-4" >
     <div class="container">
        <div class="row">
            <div class="col-md-12  "> 
                    <div class="sec_title  ">
                         <a href="" class="btn btn-success btn-sm float-right mt-1">All Bestseller</a>
                         <h4>Bestseller</h4>
                    </div> 
                </div>
        </div>
       
        
            <div class="caorusel-box arrow-round gutters-5 mt-2 mb-5">
                <div class="slick-carousel" data-slick-items="6" data-slick-xl-items="6" data-slick-lg-items="6"  data-slick-md-items="3" data-slick-sm-items="2" data-slick-xs-items="2">
                   
                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/bannana.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Grocery</span>
                                        <a href="" class="text-truncate">Organic Sweet Banana</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 20</span>
                                          <del class="old-product-price strong-400"> 15</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/dettol.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Dettol Bathing Soap</a>
                                        <span class="quant">pack of 5</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 160</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/hair_color.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Garnier Color Naturals Crème Hair Colour</a>
                                        <span class="quant">70 ml</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 160</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/organic_green_cabbage.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Organic Green Cabbage</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 50</span>
                                          <del class="old-product-price strong-400"> 55</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/tide.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Tide Plus Extra Power</a>
                                        <span class="quant">1 kg</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 155</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/Image 2.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Mother's Choice Refined</a>
                                        <span class="quant">1 kg</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 155</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/organic_green_cabbage.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Organic Green Cabbage</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 50</span>
                                          <del class="old-product-price strong-400"> 55</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>
        
     </div>
  </section>


 <section class="  pt-1 bg-white sale-section pb-4 offers" >
    <div class="container">
        <div class="row">
            <div class="col-md-4  "> 
                     <img class=" lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec4/1.jpg') }}" alt="{{ env('APP_NAME') }}">
             </div>
              <div class="col-md-4  "> 
                     <img class="  lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec4/2.jpg') }}" alt="{{ env('APP_NAME') }}">
             </div>
              <div class="col-md-4  "> 
                     <img class="  lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec4/3.jpg') }}" alt="{{ env('APP_NAME') }}">
             </div>
        </div>
      </div>
  </section>


   <section class="pt-5 bg-white pb-4 banners"   >
     <div class="container">
        
        
          <div class="caorusel-box arrow-round gutters-5 mt-2 mb-5">
                <div class="slick-carousel" data-slick-items="1" data-slick-xl-items="1" data-slick-lg-items="1"  data-slick-md-items="1" data-slick-sm-items="1" data-slick-xs-items="1">
                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a  class="d-block">
                                        <img style="border-radius: 10px;" class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/banner2.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                 
                            </div>
                        </div>
                    </div>
                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a  class="d-block">
                                        <img style="border-radius: 10px;" class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/banner2.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                 
                            </div>
                        </div>
                    </div>

                   

                   
                   

                     
                   
                </div>
            </div>
        
     </div>
  </section>


   <section class="pt-0 bg-white pb-4" >
     <div class="container">
        <div class="row">
            <div class="col-md-12  "> 
                    <div class="sec_title  ">
                         <a href="" class="btn btn-success btn-sm float-right mt-1">Explore All</a>
                         <h4>Fresh Fruits & Vegetables</h4>
                    </div> 
                </div>
        </div>

            <div class="caorusel-box arrow-round gutters-5 mt-2 mb-5">
                <div class="slick-carousel" data-slick-items="6" data-slick-xl-items="6" data-slick-lg-items="6"  data-slick-md-items="3" data-slick-sm-items="2" data-slick-xs-items="2">
                   
                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/bannana.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Fruits & Vegetables</span>
                                        <a href="" class="text-truncate">Organic Sweet Banana</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 20</span>
                                          <del class="old-product-price strong-400"> 15</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec5/apple.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Fruits & Vegetables</span>
                                        <a href="" class="text-truncate">Apple</a>
                                        <span class="quant">1 kg</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 160</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec5/grapes.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Fruits & Vegetables</span>
                                        <a href="" class="text-truncate">Grapes</a>
                                        <span class="quant">1 kg</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 100</span>
                                          <del class="old-product-price strong-400"> 120</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/organic_green_cabbage.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Fruits & Vegetables</span>
                                        <a href="" class="text-truncate">Organic Green Cabbage</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 50</span>
                                          <del class="old-product-price strong-400"> 55</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec5/sweetcorn.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Fruits & Vegetables</span>
                                        <a href="" class="text-truncate">Organic Sweet Corn</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 10</span>
                                          <del class="old-product-price strong-400"> 15</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec5/organic_grape_tomato.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Fruits & Vegetables</span>
                                        <a href="" class="text-truncate">Organic Grape Tomatoe</a>
                                        <span class="quant">1 kg</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 50</span>
                                          <del class="old-product-price strong-400"> 55</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/organic_green_cabbage.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Organic Green Cabbage</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 50</span>
                                          <del class="old-product-price strong-400"> 55</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>
        
     </div>
  </section>


   <section class="pt-0 bg-white pb-4" >
     <div class="container">
        <div class="row">
            <div class="col-md-12  "> 
                    <div class="sec_title  ">
                         <a href="" class="btn btn-success btn-sm float-right mt-1">Explore All</a>
                         <h4>Everyday Staples</h4>
                    </div> 
                </div>
        </div>
       
        
            <div class="caorusel-box arrow-round gutters-5 mt-2 mb-5">
                <div class="slick-carousel" data-slick-items="6" data-slick-xl-items="6" data-slick-lg-items="6"  data-slick-md-items="3" data-slick-sm-items="2" data-slick-xs-items="2">
                   
                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/bannana.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Grocery</span>
                                        <a href="" class="text-truncate">Organic Sweet Banana</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 20</span>
                                          <del class="old-product-price strong-400"> 15</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/dettol.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Dettol Bathing Soap</a>
                                        <span class="quant">pack of 5</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 160</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/hair_color.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Garnier Color Naturals Crème Hair Colour</a>
                                        <span class="quant">70 ml</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 160</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/organic_green_cabbage.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Organic Green Cabbage</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 50</span>
                                          <del class="old-product-price strong-400"> 55</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/tide.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Tide Plus Extra Power</a>
                                        <span class="quant">1 kg</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 155</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/Image 2.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Mother's Choice Refined</a>
                                        <span class="quant">1 kg</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 155</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/organic_green_cabbage.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Organic Green Cabbage</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 50</span>
                                          <del class="old-product-price strong-400"> 55</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>
        
     </div>
  </section>

    <section class="pt-0 bg-white pb-4" >
     <div class="container">
        <div class="row">
            <div class="col-md-12  "> 
                    <div class="sec_title  ">
                         <a href="" class="btn btn-success btn-sm float-right mt-1">Explore All</a>
                         <h4>Snack, Drinks, Dairy & More</h4>
                    </div> 
                </div>
        </div>
       
        
            <div class="caorusel-box arrow-round gutters-5 mt-2 mb-5">
                <div class="slick-carousel" data-slick-items="6" data-slick-xl-items="6" data-slick-lg-items="6"  data-slick-md-items="3" data-slick-sm-items="2" data-slick-xs-items="2">
                   
                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/bannana.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Grocery</span>
                                        <a href="" class="text-truncate">Organic Sweet Banana</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 20</span>
                                          <del class="old-product-price strong-400"> 15</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/dettol.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Dettol Bathing Soap</a>
                                        <span class="quant">pack of 5</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 160</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/hair_color.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Garnier Color Naturals Crème Hair Colour</a>
                                        <span class="quant">70 ml</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 160</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/organic_green_cabbage.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Organic Green Cabbage</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 50</span>
                                          <del class="old-product-price strong-400"> 55</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/tide.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Tide Plus Extra Power</a>
                                        <span class="quant">1 kg</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 155</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/Image 2.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Mother's Choice Refined</a>
                                        <span class="quant">1 kg</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 155</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/organic_green_cabbage.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Organic Green Cabbage</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 50</span>
                                          <del class="old-product-price strong-400"> 55</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>
        
     </div>
  </section>


   <section class="pt-0 bg-white pb-4" >
     <div class="container">
        <div class="row">
            <div class="col-md-12  "> 
                    <div class="sec_title  ">
                         <a href="" class="btn btn-success btn-sm float-right mt-1">Explore All</a>
                         <h4>Everyday Household Items</h4>
                    </div> 
                </div>
        </div>
       
        
            <div class="caorusel-box arrow-round gutters-5 mt-2 mb-5">
                <div class="slick-carousel" data-slick-items="6" data-slick-xl-items="6" data-slick-lg-items="6"  data-slick-md-items="3" data-slick-sm-items="2" data-slick-xs-items="2">
                   
                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/bannana.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Grocery</span>
                                        <a href="" class="text-truncate">Organic Sweet Banana</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 20</span>
                                          <del class="old-product-price strong-400"> 15</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/dettol.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Dettol Bathing Soap</a>
                                        <span class="quant">pack of 5</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 160</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/hair_color.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Garnier Color Naturals Crème Hair Colour</a>
                                        <span class="quant">70 ml</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 160</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/organic_green_cabbage.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Organic Green Cabbage</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 50</span>
                                          <del class="old-product-price strong-400"> 55</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/tide.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Tide Plus Extra Power</a>
                                        <span class="quant">1 kg</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 155</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/Image 2.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Mother's Choice Refined</a>
                                        <span class="quant">1 kg</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 150</span>
                                          <del class="old-product-price strong-400"> 155</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="caorusel-card">
                        <div class="product-card-2  shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec2/organic_green_cabbage.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <span>Category</span>
                                        <a href="" class="text-truncate">Organic Green Cabbage</a>
                                        <span class="quant">1 pc</span>
                                    </h2>
                                    <div class="price-box">
                                           <i class=" fa fa-inr"></i> 
                                          <span class="product-price strong-600"> 50</span>
                                          <del class="old-product-price strong-400"> 55</del>

                                          <div class="quantity buttons_added">
                                            <input type="button" value="-" class="minus"><input type="number" step="1" min="1" max="" name="quantity" value="1" title="Qty" class="input-text qty text" size="4" pattern="" inputmode=""><input type="button" value="+" class="plus">

                                            <a href="" class="float-right"> <img  src="{{ static_asset('frontend/images/homepage/sec2/cart.png') }}" alt="{{ env('APP_NAME') }}"></a>
                                        </div>
                                           
                                    </div>
                             
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>
        
     </div>
  </section>

  <section class="pt-1 bg-white pb-4 banners" >
     <div class="container">
          <div class="caorusel-box arrow-round gutters-5 mt-2 mb-5">
                <div class="slick-carousel" data-slick-items="1" data-slick-xl-items="1" data-slick-lg-items="1"  data-slick-md-items="1" data-slick-sm-items="1" data-slick-xs-items="1">
                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a  class="d-block">
                                        <img style="border-radius: 10px;" class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/banner3.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                 
                            </div>
                        </div>
                    </div>
                    <div class="caorusel-card">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a  class="d-block">
                                        <img style="border-radius: 10px;" class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/banner3.jpg') }}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>

                                 
                            </div>
                        </div>
                    </div>
 
                </div>
            </div>
        
     </div>
  </section>

  <section class="  pt-1 bg-white sale-section pb-4 offers" >
    <div class="container">
        <div class="row">
            <div class="col-md-3  "> 
                     <img class=" lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec8/Mask Group 16.jpg') }}" alt="{{ env('APP_NAME') }}">
             </div>
              <div class="col-md-3  "> 
                     <img class="  lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec8/Mask Group 17.jpg') }}" alt="{{ env('APP_NAME') }}">
             </div>
              <div class="col-md-3  "> 
                     <img class="  lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec8/Mask Group 18.jpg') }}" alt="{{ env('APP_NAME') }}">
             </div>
             <div class="col-md-3  "> 
                     <img class="  lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/sec8/Mask Group 19.jpg') }}" alt="{{ env('APP_NAME') }}">
             </div>
        </div>
      </div>
  </section>


    <section class="  pt-5 bg-white sale-section pb-4 offers" >
        <div class="container">
            <div class="row">
                <div class="col-md-12 ">
                      <img class="  lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/banner4.jpg') }}" alt="{{ env('APP_NAME') }}">
                </div>

                <div class="col-md-12 mt-4">
                      <img class="  lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/banner5.jpg') }}" alt="{{ env('APP_NAME') }}">
                </div>

                 <div class="col-md-12 mt-4">
                      <img class="  lazyload mx-auto" src="{{ static_asset('frontend/images/homepage/banner6.jpg') }}" alt="{{ env('APP_NAME') }}">
                </div>
            </div>
        </div>
    </section>



    <section class="  pt-5 bg-white sale-section pb-4 brandss" >
        <div class="container">
            <div class="row">
                <div class="col-md-12  "> 
                    <div class="sec_title mb-3  ">
                         <h4>Brands</h4>
                    </div> 
                </div>
                <div class="col-md-12 ">
                    <span>Havemore</span> <span>O'range</span> <span>Savemore 24</span> <span> Mantra</span><span> Aashirvaad</span><span> Act II</span><span> Amul</span><span> Axe</span><span> Bambino</span>  <span>Havemore</span> <span>O'range</span> <span>Savemore 24</span> <span> Mantra</span><span> Aashirvaad</span><span> Act II</span><span> Amul</span><span> Axe</span><span> Bambino</span>  <span>Havemore</span> <span>O'range</span> <span>Savemore 24</span> <span> Mantra</span><span> Aashirvaad</span><span> Act II</span><span> Amul</span><span> Axe</span><span> Bambino</span>  <span>Havemore</span> <span>O'range</span> <span>Savemore 24</span> <span> Mantra</span><span> Aashirvaad</span><span> Act II</span><span> Amul</span><span> Axe</span><span> Bambino</span> <span>Havemore</span> <span>O'range</span> <span>Savemore 24</span> <span> Mantra</span><span> Aashirvaad</span><span> Act II</span><span> Amul</span><span> Axe</span><span> Bambino</span>  <span>Havemore</span> <span>O'range</span> <span>Savemore 24</span> <span> Mantra</span><span> Aashirvaad</span><span> Act II</span><span> Amul</span><span> Axe</span><span> Bambino</span>  <span>Havemore</span> <span>O'range</span> <span>Savemore 24</span> <span> Mantra</span><span> Aashirvaad</span><span> Act II</span><span> Amul</span><span> Axe</span><span> Bambino</span>  <span>Havemore</span> <span>O'range</span> <span>Savemore 24</span> <span> Mantra</span><span> Aashirvaad</span><span> Act II</span><span> Amul</span><span> Axe</span><span> Bambino</span> 
                </div>

                <div class="col-md-12 ">
                    <div class="sep"></div>
                </div>

                 <div class="col-md-12  "> 
                    <div class="sec_title mb-3  ">
                         <h4>ROZANA</h4>
                         <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent et turpis vel enim iaculis pretium. Aliquam non rutrum eros. Suspendisse ultrices risus sit amet libero mattis, eget volutpat magna eleifend. Mauris nec consectetur
                        nunc. Suspendisse ipsum dolor, pretium et erat non, laoreet consequat purus. Sed sem ligula, tincidunt et ex eget, vestibulum fermentum dui. Quisque aliquet placerat velit, in luctus ligula auctor ut. </p>
                        <p>Nullam dapibus est lectus, in egestas leo ultrices eu. Nulla nec pretium turpis, sed hendrerit nisi. Maecenas quis placerat elit. Integer ut efficitur mauris, ut dignissim eros. Quisque eget ex ante. Aenean vehicula, leo nec molestie
                        sollicitudin, ligula enim pellentesque libero, eget fermentum neque diam lacinia turpis. Nam quis nisi hendrerit nunc blandit aliquam. Integer rutrum porta consequat. Suspendisse hendrerit ipsum augue, at lobortis nisl blandit
                        efficitur. Fusce euismod cursus libero vitae porta. Suspendisse at orci vitae sapien consequat consequat at sed risus. Phasellus ut libero blandit erat lobortis eleifend. Praesent luctus magna eu urna imperdiet venenatis. Nulla
                        dapibus sodales euismod. Integer et arcu dui. Quisque id neque quis massa tristique placerat at ac ligula. </p>
                        <p>Phasellus accumsan lectus bibendum fringilla ornare. Duis ullamcorper non ante in sagittis. Duis ultricies scelerisque porttitor. Aenean facilisis justo id faucibus suscipit. Fusce et efficitur sapien. Mauris non leo metus. Donec nisi ex,
                        pretium vel ipsum quis, hendrerit luctus augue</p>
                        <a href="" class="btn btn-success ">Read more</a>
                    </div> 
                </div>
            </div>
        </div>
    </section>












   <!--  <div id="section_featured" class=" bg-white " style="margin-top: -50px;">

    </div>

    <div id="section_best_selling" style="background: #ff9900;  margin-top: -20px; padding: 30px 0">

    </div> -->

   <!--  <div id="section_home_categories">

    </div> -->

    @if(\App\BusinessSetting::where('type', 'classified_product')->first()->value == 1)
        @php
            $customer_products = \App\CustomerProduct::where('status', '1')->where('published', '1')->take(10)->get();
        @endphp
       @if (count($customer_products) > 0)
           <section class="">
               <div class="container">
                   <div class="px-2 py-4 p-md-4 bg-white shadow-sm">
                       <div class="section-title-1 clearfix">
                           <h3 class="heading-5 strong-700 mb-0 float-left">
                               <span class="mr-4">{{ translate('Classified Ads') }}</span>
                           </h3>
                           <ul class="inline-links float-right">
                               <li><a href="{{ route('customer.products') }}" class="active">{{ translate('View More') }}</a></li>
                           </ul>
                       </div>
                       <div class="caorusel-box arrow-round">
                           <div class="slick-carousel" data-slick-items="6" data-slick-xl-items="5" data-slick-lg-items="4"  data-slick-md-items="3" data-slick-sm-items="2" data-slick-xs-items="2">
                               @foreach ($customer_products as $key => $customer_product)
                                   <div class="product-card-2 card card-product my-2 mx-1 mx-sm-2 shop-cards shop-tech">
                                       <div class="card-body p-0">
                                           <div class="card-image">
                                               <a href="{{ route('customer.product', $customer_product->slug) }}" class="d-block">
                                                   <img class="img-fit lazyload mx-auto" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{Storage::disk('s3')->url($customer_product->thumbnail_img)}}" alt="{{ __($customer_product->name) }}">
                                               </a>
                                           </div>

                                           <div class="p-sm-3 p-2">
                                               <div class="price-box">
                                                   <span class="product-price strong-600">{{ single_price($customer_product->unit_price) }}</span>
                                               </div>
                                               <h2 class="product-title p-0 text-truncate-1">
                                                   <a href="{{ route('customer.product', $customer_product->slug) }}">{{ __($customer_product->name) }}</a>
                                               </h2>
                                               <div>
                                                   @if($customer_product->conditon == 'new')
                                                       <span class="product-label label-hot">{{translate('new')}}</span>
                                                   @elseif($customer_product->conditon == 'used')
                                                       <span class="product-label label-hot">{{translate('Used')}}</span>
                                                   @endif
                                               </div>
                                           </div>
                                       </div>
                                   </div>
                               @endforeach
                           </div>
                       </div>
                   </div>
               </div>
           </section>
       @endif
   @endif

    <div class="">
        <div class="container">
            <div class="row gutters-10">
                @foreach (\App\Banner::where('position', 2)->where('published', 1)->get() as $key => $banner)
                    <div class="col-lg-{{ 12/count(\App\Banner::where('position', 2)->where('published', 1)->get()) }}">
                        <div class="media-banner mb-3 mb-lg-0">
                            <a href="{{ $banner->url }}" target="_blank" class="banner-container">
                                <img src="{{Storage::disk('s3')->url('frontend/images/placeholder-rect.jpg')}}" data-src="{{Storage::disk('s3')->url($banner->photo)}}" alt="{{ env('APP_NAME') }} promo" class="img-fluid lazyload">
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if (\App\BusinessSetting::where('type', 'vendor_system_activation')->first()->value == 1)
    <div id="section_best_sellers">

    </div>
    @endif

    @if(count(\App\Category::where('top', 1)->get()) != null && count(\App\Brand::where('top', 1)->get()) != null)
    <!-- <section class="mb-3">
        <div class="container">
            <div class="row gutters-10">
                <div class="col-lg-6">
                    <div class="section-title-1 clearfix">
                        <h3 class="heading-5 strong-700 mb-0 float-left">
                            <span class="mr-4">{{translate('Top 10 Catogories')}}</span>
                        </h3>
                        <ul class="float-right inline-links">
                            <li>
                                <a href="{{ route('categories.all') }}" class="active">{{translate('View All Catogories')}}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row gutters-5">
                        @foreach (\App\Category::where('top', 1)->get() as $category)
                            <div class="mb-3 col-6">
                                <a href="{{ route('products.category', $category->slug) }}" class="bg-white border d-block c-base-2 box-2 icon-anim pl-2">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col-3 text-center">
                                             
                                            <img src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{Storage::disk('s3')->url($category->banner)}}" alt="{{ __($category->name) }}" class="img-fluid img lazyload">
                                        </div>
                                        <div class="info col-7">
                                            <div class="name text-truncate pl-3 py-4">{{ __($category->name) }}</div>
                                        </div>
                                        <div class="col-2 text-center">
                                            <i class="la la-angle-right c-base-1"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="section-title-1 clearfix">
                        <h3 class="heading-5 strong-700 mb-0 float-left">
                            <span class="mr-4">{{translate('Top 10 Brands')}}</span>
                        </h3>
                        <ul class="float-right inline-links">
                            <li>
                                <a href="{{ route('brands.all') }}" class="active">{{translate('View All Brands')}}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row gutters-5">
                        @foreach (\App\Brand::where('top', 1)->get() as $brand)
                            <div class="mb-3 col-6">
                                <a href="{{ route('products.brand', $brand->slug) }}" class="bg-white border d-block c-base-2 box-2 icon-anim pl-2">
                                    <div class="row align-items-center no-gutters">
                                        <div class="col-3 text-center">
                                             
                                            <img src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{Storage::disk('s3')->url($brand->logo) }}" alt="{{ __($brand->name) }}" class="img-fluid img lazyload">
                                        </div>
                                        <div class="info col-7">
                                            <div class="name text-truncate pl-3 py-4">{{ __($brand->name) }}</div>
                                        </div>
                                        <div class="col-2 text-center">
                                            <i class="la la-angle-right c-base-1"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section> -->
    @endif
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            $.post('{{ route('home.section.featured') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#section_featured').html(data);
                slickInit();
            });

            $.post('{{ route('home.section.best_selling') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#section_best_selling').html(data);
                slickInit();
            });

            $.post('{{ route('home.section.home_categories') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#section_home_categories').html(data);
                slickInit();
            });

            @if (\App\BusinessSetting::where('type', 'vendor_system_activation')->first()->value == 1)
            $.post('{{ route('home.section.best_sellers') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#section_best_sellers').html(data);
                slickInit();
            });
            @endif
        });


        function wcqib_refresh_quantity_increments() {
    jQuery("div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)").each(function(a, b) {
        var c = jQuery(b);
        c.addClass("buttons_added"), c.children().first().before('<input type="button" value="-" class="minus" />'), c.children().last().after('<input type="button" value="+" class="plus" />')
    })
}
String.prototype.getDecimals || (String.prototype.getDecimals = function() {
    var a = this,
        b = ("" + a).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
    return b ? Math.max(0, (b[1] ? b[1].length : 0) - (b[2] ? +b[2] : 0)) : 0
}), jQuery(document).ready(function() {
    wcqib_refresh_quantity_increments()
}), jQuery(document).on("updated_wc_div", function() {
    wcqib_refresh_quantity_increments()
}), jQuery(document).on("click", ".plus, .minus", function() {
    var a = jQuery(this).closest(".quantity").find(".qty"),
        b = parseFloat(a.val()),
        c = parseFloat(a.attr("max")),
        d = parseFloat(a.attr("min")),
        e = a.attr("step");
    b && "" !== b && "NaN" !== b || (b = 0), "" !== c && "NaN" !== c || (c = ""), "" !== d && "NaN" !== d || (d = 0), "any" !== e && "" !== e && void 0 !== e && "NaN" !== parseFloat(e) || (e = 1), jQuery(this).is(".plus") ? c && b >= c ? a.val(c) : a.val((b + parseFloat(e)).toFixed(e.getDecimals())) : d && b <= d ? a.val(d) : b > 0 && a.val((b - parseFloat(e)).toFixed(e.getDecimals())), a.trigger("change")
});
    </script>
@endsection
