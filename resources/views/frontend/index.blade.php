@extends('frontend.layouts.app')

@section('content')
  <style type="text/css">.body-wrap{background: #fff!important}
   .news {height: 30px; color: #009245; font-size: 16px; font-weight: 700; background: #eee; padding: 3px 0;  overflow: hidden;box-sizing: border-box; white-space: nowrap;}
    
    .news p {
        display:  block;
        padding-left: 100%;
        animation: marquee 18s linear infinite; font-size: 16px; 
    }
    @keyframes marquee {
        0%   { transform: translate(0, 0); }
        100% { transform: translate(-140%, 0); }
    }
    @media(max-width: 640px){
        .news  {padding: 4px 0; margin-top: -10px}
        .news p {animation: marquee 10s linear infinite;}
        @keyframes marquee {
        0%   { transform: translate(0, 0); }
        100% { transform: translate(-200%, 0); }
     }
    }
   .spinner-border{position: absolute; left: 45%; bottom: 20px;}  

   #hhf{
            font-size: 22px!important;
    }
    .hf{
        font-size: 16px!important;
    }
   
</style>
    <section class="home-banner-area ">
        <div class="">
            <div class="no-gutters position-relative">
                
                
                
               <div class="categoriesss bg-white pt-md-2 pb-md-3">
                    <div class="container"> 
                         <div class="row">
                            <div class="col-md-12 mt-md-2">                 
                                @if (count($featured_categories) > 0)
                                    <div class="trending-category  ">
                                        <div class="caorusel-box arrow-round gutters-5">
                                            <div class="slick-carousel home-cat" data-slick-items="8" data-slick-xl-items="8" data-slick-lg-items="8"  data-slick-md-items="6" data-slick-sm-items="4" data-slick-xs-items="3"  >
                                                @foreach ($featured_categories as $key => $category)
                                                
                                                    <div class="caorusel-card">
                                                         <div class="trend-category-single">
                                                            <a href="{{ route('products.category', $category->slug) }}" class="d-block">
                                                                <div class="img">
                                                                   <img class="img-fit lazyload mx-auto" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{Storage::disk('s3')->url($category->banner)}}" alt="{{ __($category->name) }}">
                                                                   <!-- <img class="img-fit lazyload mx-auto" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" alt="{{ __($category->name) }}"> -->
                                                                </div>
                                                                 <div class="name">
                                                                    {{ __($category->name) }}
                                                                 </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
               </div>

               
             
                <div class="col-lg-12   @if(count($featured_categories) == 0) home-slider-full @endif">
                    <div class="home-slide  d-none d-md-block">
                        <div class="home-slide">
                            <div class="slick-carousel" data-slick-arrows="true" data-slick-dots="true" data-slick-autoplay="true">
                                @foreach ($getSlider as $key => $slider)
                                    <div class="home-slide-item">
                                        <div class="container position-relative">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <div class="ovrlay_cont">
                                                        <!-- <h1>Fresh <span>Grocery</span> Shopping</h1>
                                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                                            hendrerit nisi sed sollicitudin pellentesque. Nunc posuere
                                                            purus rhoncus pulvinar aliquam.</p>
                                                            <a href="" class="btn btn-success btn-lg">Order Now</a> -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="{{ $slider->link }}" target="_blank">

                                       <img class="d-block w-100 h-100"  src="{{Storage::disk('s3')->url($slider->photo)}}" alt="{{ env('APP_NAME')}} promo">
                                        <!-- <img class="d-block w-100 h-100"  src="{{ asset('public/uploads/sliders/1ce876d5-85ce-4c28-80ae-0db6b4dab779.jfif') }}" alt="{{ env('APP_NAME')}} promo"> -->
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>    

                     <div class="home-slide d-block d-md-none">
                        <div class="home-slide">
                            <div class="slick-carousel" data-slick-arrows="true" data-slick-dots="true" data-slick-autoplay="true">
                            @foreach ($getSlider as $key=>$mobile_slider)
                                
                            
                                <div class="home-slide-item"  >
                                   <a href="{{ $mobile_slider->link }}" target="_blank">
                                   <img class="d-block w-100 h-100 lazyload" decoding="async" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{Storage::disk('s3')->url($mobile_slider->mobile_photo)}}" alt=""> 
                                   <!-- <img class="d-block w-100 h-100 lazyload" decoding="async" src="{{ asset('public/uploads/sliders/mobile-img.jfif') }}" alt="">    -->
                                </a>
                               </div>
                            @endforeach
                                    
                                
                            </div>
                        </div>
                    </div>              
                </div>

             

            </div>
        </div>
    </section>


    
 <marquee class="news" direction="left">
 @if(!empty($getNews))
            {{$getNews['news']}}
            @else
            Register with rozana.in as a partner at 0 investment | Early morning delivery of fresh fruits, vegetables and dairy | Save big on your daily essentials!
            @endif
 </marquee>
 
   <!--  <div class="news">

        <p  >
            @if(!empty($getNews))
            {{$getNews['news']}}
            @else
            Delivery from 9:00 AM to 9:00 PM
            @endif
        </p>
    </div> -->
  
      @if(Session::has('referal_discount') && Cookie::has('pincode'))
     @php
        $flash_deal = \App\FlashDeal::where('status', 1)->where('featured', 1)->where('sorting_hub_id',$shortId['sorting_hub_id'])->first();
        
    @endphp
    @if($flash_deal != null && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date)
    <section class="mb-4">

        <div class="container">
            <div class="px-2 py-4 p-md-4 bg-white shadow-sm">
                <div class="section-title-1 clearfix ">
                    <h3 class="heading-5 strong-700 mb-0 float-left">
                        Flash Sale
                    </h3>
                    <div class="flash-deal-box float-left">
                        <div class="countdown countdown--style-1 countdown--style-1-v1 " data-countdown-date="{{ date('m/d/Y', $flash_deal->end_date) }}" data-countdown-label="show"></div>
                    </div>
                    <a href="{{ route('flash-deal-details', $flash_deal->slug) }}" class="btn btn-success btn-sm float-right mt-1">Explore All</a>
                </div>
                <div class="caorusel-box arrow-round gutters-5">
                    <div class="slick-carousel" data-slick-items="6" data-slick-xl-items="6" data-slick-lg-items="6"  data-slick-md-items="3" data-slick-sm-items="2" data-slick-xs-items="2">
                    @foreach ($flash_deal->flash_deal_products as $key => $flash_deal_product)
                        @php
                            $product = \App\Product::find($flash_deal_product->product_id);
                        @endphp
                        @if ($product != null && $product->published != 0)

                            <form id="option-choice-form_{{$product->id}}">
                                <input type="hidden" id="option_form{{ $product->id }}" value="" />
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">      
                                <input type="hidden" name="id" value="{{ $product->id }}">
                                @php 
                                $attr = json_decode($product->choice_options);
                                
                                @endphp
                                @foreach($attr as $key => $choice)
                                    @foreach ($choice->values as $key => $value)
                             <input type="hidden" name="attribute_id_{{$choice->attribute_id}}" value="{{ $value }}">
                                    @endforeach
                             @endforeach

                            <div class="caorusel-card">
                                <div class="product-card-2 shop-cards shop-tech">
                                    <div class="card-body p-0">
                                        <div class="card-image">
                                            <a href="{{ route('product', $product->slug) }}" class="d-block">
                                                <img class="img-fit lazyload mx-auto" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{Storage::disk('s3')->url($product->thumbnail_img)}}" alt="{{ __($product->name) }}">
                                            </a>
                                        </div>

                                        <div class="p-md-3 p-2 prod_info">
                                            @if(Session::has('referal_discount'))
                                            @php
                                                $referral_price = peer_discounted_newbase_price($product->id,$shortId);
                                                $main_price = main_price_percent($product->id,$shortId);

                                                $difference = ($main_price - $referral_price)/$main_price;
                                                $percent_price = $difference*100;
                                            @endphp
                                            <br />
                                                <div class="discount homep" >
                                                <span><?php echo round($percent_price, 2); ?>% Off</span>
                                                    <img  src="{{ static_asset('frontend/images/discount.png') }}" >
                                                </div>
                                             @endif    
                                            <h2 class="product-title p-0">
                                                <span>{{ ucwords($product->category->name) }}</span>
                                                <a href="{{ route('product', $product->slug) }}" class=" text-truncate">{{ __($product->name) }}</a>
                                                <span class="quant">
                                                    @if ($product->choice_options != null)
                                                        @foreach (json_decode($product->choice_options) as $key => $choice)
                                                            @foreach ($choice->values as $key => $value)
                                                                {{ $value }}
                                                            @endforeach   
                                                        @endforeach
                                                    @endif
                                                </span>
                                                
                                            </h2>
                                            <div class="price-box">
                                                @if(Session::has('referal_discount'))
                                                    @if(!empty($shortId))  
                                                            <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($product->id,$shortId)) }}</span>
                                                            @php
                                                            $mappedProductPrice = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$product->id])->first(); 
                                                            @endphp
                                                            @if($mappedProductPrice['selling_price'] !=0)
                                                            <span class="old-product-price strong-400">{{ format_price(@$mappedProductPrice['selling_price']) }}</span>
                                                            @else
                                                            <span class="old-product-price strong-400">{{ format_price(@$product->stocks[0]->price) }}</span>
                                                            @endif
                                                        @else
                                                            <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($product->id,$shortId)) }}</span>
                                                            <del class="old-product-price strong-400">{{ format_price(@$product->stocks[0]->price) }}</del>
                                                        @endif
                                                    @else    
                                                        @if(!empty($shortId))
                                                            @php
                                                            $mappedProductPrice = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$product->id])->first(); 
                                                            @endphp
                                                                @if($mappedProductPrice['selling_price'] !=0)
                                                                <span class="product-price strong-600">{{ format_price(@$mappedProductPrice['selling_price']) }}</span>
                                                                @else
                                                                <span class="product-price strong-600">{{ format_price(@$product->stocks[0]->price) }}</span>
                                                                @endif
                                                            
                                                            @else
                                                            <span class="product-price strong-600">{{ format_price(@$product->stocks[0]->price) }}</span>
                                                            @endif 
                                                    @endif

                                                    <input type="hidden" id="stock_qty_{{$product->id}}" name="stock_qty_{{$product->id}}" value="@if(!empty(Cookie::has('pincode'))){{ mapped_product_stock($shortId['sorting_hub_id'],$product->id)}} @else 0 @endif">
                                                <input type="hidden" id="limit_qty_{{$product->id}}" name="limit_qty_{{$product->id}}" value="@if(!empty($product->max_purchase_qty)){{ $product->max_purchase_qty}} @else 0 @endif">
                                                <div id="cart_loader{{$product->id}}"></div>
                                                   <div class="quantity buttons_added new" id="button-group{{ $product->id }}">
                                                   @php
                                                    $class = "";
                                                    if(get_product_cart_qty($product->id)>0)
                                                    {
                                                        $class = "display-none";
                                                    }
                                                    @endphp
                                                    <input type="button" value="Add" class="quant_add_btn plus {{$class}}" id="btn_add{{ $product->id }}" onclick="addToCartF('{{$product->id}}')">
                                                    <input type="button" value="-" class="minus" onclick="add_qty('{{ $product->id }}'),updateCartF('{{ $product->id }}')">
                                                    <input type="number" step="1" min="0" max="@if(!empty($product->max_purchase_qty))
                                                    {{ $product->max_purchase_qty}} @endif" onkeypress="add_qty(this.id),addToCartF('{{$product->id}}')" id="pamount_{{ $product->id}}" name="quantity{{$product->id}}" value="{{ get_product_cart_qty($product->id) }}" title="Qty" class="input-text qty text" size="4" pattern="" inputmode="">
                                                    <input type="button" value="+" class="plus" id="plus-button{{ $product->id }}" onclick="add_qty('{{ $product->id }}'),addToCartF('{{$product->id}}')">
                                                    <div class="clearfix"></div>
                                                    
                                                </div> 


                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @endif
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
  
    @endif
    @endif
     
    <div id="section_best_selling" class="bg-white" >
    </div>
   
    <div id="section_home_categories" class="bg-white">
        <div class="home_categories"></div>
        <input type="hidden" id="start" value="0" />
        <input type="hidden" id="total_cat" value="6" />
        <input type="hidden" id="scroll" value="600" />
        <div class='spinner-border text-success' role='status' id="loading" style="display:none;"><span class='sr-only'>Loading...</span></div>
   
    </div>
    
    <div id="section_banner_slider">
    </div>

   <!--  <div id="section_finance_banner">
    </div> -->

    <div id="section_master_banner">
    </div>

    {{-- <section class="  pt-5 bg-white sale-section pb-4 brandss" >
        <div class="container">
            <div class="row">
                <div class="col-md-12  "> 
                    <div class="sec_title mb-3  ">
                         <h4>Brands</h4>
                    </div> 
                </div>
                <!-- <div class="col-md-12 ">
                    @foreach (\App\Brand::all() as $key => $brand)
                        <span>{{ ucwords($brand->name) }}</span>
                    @endforeach
                   
                </div> -->
                <div class="col-md-12 mt-2">                 
                    @if (count($brand_sliders) > 0)
                        <div class="trending-category brandss">
                            <div class="caorusel-box arrow-round gutters-5">
                                <div class="slick-carousel" data-slick-items="6" data-slick-xl-items="6" data-slick-lg-items="6"  data-slick-md-items="3" data-slick-sm-items="2" data-slick-xs-items="2" data-slick-autoplay="true">
                                    @foreach ($brand_sliders->take(10) as $key => $category)
                                        <div class="caorusel-card">
                                             <div class="trend-category-single">
                                                    <div class="img">
                                                       <img src="{{Storage::disk('s3')->url($category->banner)}}" data-src="{{Storage::disk('s3')->url($category->banner)}}" alt="{{ __($category->name) }}">
                                                    </div>
                                                     <!-- <div class="name">
                                                        {{ __($category->name) }}
                                                     </div> -->
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-md-12 ">
                    <div class="sep"></div>
                </div>


            </div>
        </div>
    </section> --}}

    <section class="   bg-white   pb-4  " >
        <div class="container">
            <div class="row">
                    <div class="col-md-12">
                    <h4 id="hhf"> {{translate("ROZANA")}} </h4>
                    <p class="hf">{{translate("Rozana.in is a P2P rural commerce startup that leverages Tech and Data Science to cater to unique local demands of 1 billion Indians outside the scope of online commerce. This silent tech revolution is not only empowering the end consumer but also working towards incubating over 10 million tech micro-entrepreneurs all over the country.")}} </p>

                    <p class="hf">{{translate("We enable micro-entrepreneurs to give wider, innovative and competitive offerings to end users. Our entrepreneurs use the platform to onboard customers, share latest deals and help them place online orders, making last mile delivery efficient.")}}</p>

                    <p class="hf">{{translate("Aspirations in rural India are on the rise and we are racing against time to ensure the effective delivery of essential services to these areas. Rozana.in aims to empower rural communities of the country and connect them to online commerce through a network of micro-entrepreneurs, and envisions to become the leading P2P rural commerce platform in India.")}}</p>


                    <!-- <a href="" class="btn btn-dark btn-sm mt-3">{{translate("Read more")}}</a> -->
                </div>
            </div>
        </div>
    </section>
 
  

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
                               <span class="mr-4">Classified Ads</span>
                           </h3>
                           <ul class="inline-links float-right">
                               <li><a href="{{ route('customer.products') }}" class="active">View More</a></li>
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
                                                       <span class="product-label label-hot">new</span>
                                                   @elseif($customer_product->conditon == 'used')
                                                       <span class="product-label label-hot">Used</span>
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
    @php 
                $user_id = session::get('user_id');
                $is_old = \App\User::Where('id',$user_id)->pluck('is_old')->first();
            @endphp
            @if($is_old === 1)
    <div class="modal fade" id="wallet_modal_new" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabels" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal modal-lg" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title strong-600 heading-5">{{ translate('Fill Address Form')}}</h5>
                    <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> -->
                </div>
                <form class="" action="{{ route('updateaddressbycallcenter') }}" method="post">
                    @csrf
                    <div class="modal-body gry-bg px-3 pt-3">
                        

                    <!-- <div class="form-group">
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
                        
                    </div> -->
                    <div class="form-group row" id="subsubcategory">
                        <label class="col-lg-2 control-label">{{translate('Address')}} <span class="error">*</span></label>
                        <div class="col-lg-7">
                        <input type="text" class="form-control" name="address" value="{{ old('address') }}" placeholder="{{ translate('Address') }}" required>
                        
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
                    <input type="hidden" name="user_id" value="{{ session::get('user_id') }}" />
                    <div class="form-group row">
                        <label class="col-lg-2 control-label">{{translate('State')}} <span class="error">*</span></label>
                        <div class="col-lg-7">
                            <input type="hidden" name="state" id="state" value="" />
                            <select class="form-control demo-select2 state_id" name="state_id" id="state_id" onchange="loadList(this)" required>
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

                    <div class="form-group row">
                        <label class="col-lg-2 control-label">{{translate('City/District')}} <span class="error">*</span></label>
                        <div class="col-lg-7">
                        <input type="hidden" name="city" id="city" value="" />
                        <select class="form-control demo-select2 city_id" name="city_id" id="city_idd" onchange="loadList(this)" >
                                <option value="">Select City/District</option>
                                
                            </select>
                            @if($errors->has('city'))
                            <div class="error  mr-top">{{ $errors->first('city') }}</div>
                            @endif
                        </div>
                       
                    </div>

                    <div class="form-group row">
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

                    <div class="form-group row">
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
                    
                    <div class="form-group row">
                        <label class="col-lg-2 control-label">{{translate('Pincode')}} <span class="error">*</span></label>
                        <div class="col-lg-7">
                            <select class="form-control pindata demo-select2" name="pincode" id="pincode_id" >
                                <option value="">Select Pincode</option>
                            </select>
                            
                            @if($errors->has('pincode'))
                            <div class="error mr-top">{{ $errors->first('pincode') }}</div>
                             @endif
                        </div>
                        
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-lg-2 control-label">{{translate('Zone')}}</label>
                        <div class="col-lg-7">
                            <input type="text" class="form-control zonedata" id="zone" name="zone" placeholder="{{ translate('Zone') }}" readonly>
                        </div>
                    </div>


                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-base-1">{{ translate('Confirm')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
      </div>
@endif
@endsection

@section('script')

    <script>

function loadList(el){
    var id =$(el).attr('id');
    $("#"+id).prev("input").val($("#"+id+" option:selected").text());
    var url = "";

    var keyval = $(el).val();
    if(id =="state_id"){
        url = "{{ route('citylist') }}";
        data = {state_id:keyval};
        var loadid = "city_idd";
    }

    if(id=="city_idd"){
        url = "{{ route('blocklist') }}";
        data = {city_id:keyval};
        var loadid = "block_id";
    }

    if(id=="block_id"){
        url = "{{ route('pincodelist') }}";
        data = {block_id:keyval};
        var loadid = "pincode_id";
    }
//console.log(loadid)
    $.ajax({
    url: url,
    type: "get", //send it through get method
    data: data,
    success: function(response) {
        //Do Something
        $("#"+loadid).empty();
        $("#"+loadid).append("<option value=''>Select</option>");
        $.map(response.state.data,function(item){
            if(id=="city_idd"){
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

        $(document).ready(function(){
            $('#wallet_modal_new').modal({
                backdrop: 'static',
                keyboard: true, 
                show: true
            });
            $(window).scroll(function(){
                
                //var element = $("#section_best_selling");
                //console.log("scroll-top"+$(document).scrollTop());
                //console.log("element"+element.offset().top + element.height());
                var scroll = parseInt($("#scroll").val());
                if ($(document).scrollTop() > scroll) {
                    var start = parseInt($("#start").val());
                    var total_cat = parseInt($("#total_cat").val());
                    start = start;
                    if(start <= total_cat){
                        $("#start").val(start+1);
                        $("#scroll").val(scroll+400);
                        //$(".home_categories:last").after(data).show().fadeIn("slow");
                        $("#loading").show();
                        $.ajax({
                            url: '{{ route('home.section.home_categories') }}',
                            type: 'post',
                            data: {_token:'{{ csrf_token() }}',offset:start},
                            cache:true,
                            async:true,
                            success:function(data){
                                $(".home_categories:last").before(data).show().fadeIn("slow");
                                $("#loading").hide();
                                slickInit();
                            }
                            });
                    }
                    /// do something ///
                }
               

            });

           $("#section_best_selling").html("<div class='spinner-border text-success' role='status'><span class='sr-only'>Loading...</span></div>");
             
            // $.post('{{ route('home.section.featured') }}', {_token:'{{ csrf_token() }}'}, function(data){
            //     $('#section_featured').html(data);
            //     slickInit();
            // });

            // $.post('{{ route('home.section.best_selling') }}', {_token:'{{ csrf_token() }}'}, function(data){
            //     $('#section_best_selling').html(data);
            //     slickInit();
            // });

            // $.post('{{ route('home.section.home_categories') }}', {_token:'{{ csrf_token() }}'}, function(data){
            //     $('#section_home_categories').html(data);
            //     slickInit();
            // });

            
            $.ajax({
            url: '{{ route('home.section.best_selling') }}',
            type: 'post',
            data: {_token:'{{ csrf_token() }}'} ,
            cache:true,
            async:true,
            success:function(data){
                $('#section_best_selling').html(data);
                slickInit();
            }
            });

            // $.ajax({
            // url: '{{ route('home.section.home_categories') }}',
            // type: 'post',
            // data: {_token:'{{ csrf_token() }}'} ,
            // cache:true,
            // async:false,
            // success:function(data){
            //     $('#section_home_categories').html(data);
            //     slickInit();
            // }
            // });

            
            $.post('{{ route('home.section.banner_slider') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#section_banner_slider').html(data);
                slickInit();
            });

            // $.post('{{ route('home.section.finance_banner') }}', {_token:'{{ csrf_token() }}'}, function(data){
            //     $('#section_finance_banner').html(data);
            //     slickInit();
            // });

            $.post('{{ route('home.section.master_banner') }}', {_token:'{{ csrf_token() }}'}, function(data){
                $('#section_master_banner').html(data);
                slickInit();
            });

            
            {{--@if (\App\BusinessSetting::where('type', 'vendor_system_activation')->first()->value == 1)
            // $.post('{{ route('home.section.best_sellers') }}', {_token:'{{ csrf_token() }}'}, function(data){
            //     $('#section_best_sellers').html(data);
            //     slickInit();
            // });
            @endif --}}
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
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

    <script type="text/javascript">
        function add_qty(id)
        {

            var timeout = {};
            
            var stock_qty = $("#stock_qty_"+id).val();

            clearTimeout(timeout);
             timeout = setTimeout(function () {
             var pr_qty = $("#pamount_"+id).val();
            if(pr_qty<=0){
               pr_qty= 1;
               $("#"+id).val(1);  
            }

            var date = new Date();
            date.setTime(date.getTime() + (60 * 1000));
            $.cookie(id, pr_qty,{ expires : date });
            
     }, 1000);
            // if(pr_qty>stock_qty){
                   
            //         showFrontendAlert('danger','Product quantity unavailable');
            //          $("#"+id).val(1);
            //         //console.log('Product quantity unavailable')
            //     }
                if(stock_qty<=0){
                   
                    showFrontendAlert('danger','Product out of stock');
                    $("#btn_add"+id).next().attr('disabled','disabled');
                    $("#plus-button"+id).attr('disabled','disabled');
                    $("#"+id).val(1);
                    $("#pamount_"+id).val(1);
                    return false;
                }

        }

    function addToCartF(id){
        
        @if(!Cookie::has('pincode'))
            showFrontendAlert('danger','Select Your Delivery Location'); 
            $(".quant_add_btn").show();
            return false;
        @endif
        var stock_qty = $("#stock_qty_"+id).val();
        console.log(stock_qty);
        if(stock_qty<=0){
                   
                    showFrontendAlert('danger','Product out of stock');
                    $("#btn_add"+id).next().attr('disabled','disabled');
                    $("#plus-button"+id).attr('disabled','disabled');
                    $("#"+id).val(1);
                    //$("#pamount_"+id).val(1);
                    return false;
                    //console.log('Product out of stock');
                }

        var limit_qty = $("#limit_qty_"+id).val();
            var pr_qty = $("#pamount_"+id).val();
            var pr_qty = parseInt(pr_qty)+1;
            if(limit_qty < pr_qty){
                showFrontendAlert('danger','Maximum Limit has been reached');
                return false;

            }
        
        if(checkAddToCartValidity()) {
            var data = $("#option_form"+id).parent().serializeArray();
            data.push({
            name: "quantity",
            value: 1
        });
        values = jQuery.param(data);
        cart_loader('show',id);
            $.ajax({
               type:"POST",
               url: '{{ route('cart.addToCart') }}',
               data: data,
               success: function(data){
                   
                   updateNavCart();
                   totalCartItem();
                   cart_loader('hide',id);
               }
           });
        }
        else{
            showFrontendAlert('warning', 'Please choose all the options');
        }
    }

    function updateCartF(id)
    {
        @if(!Cookie::has('pincode'))
            showFrontendAlert('danger','Select Your Delivery Location'); 
            $(".quant_add_btn").show();
            return false;
        @endif
        
        if(checkAddToCartValidity()) {
            var data = $("#option_form"+id).parent().serializeArray();
            //console.log(data);
            data.push({
            name: "quantity",
            value: 1
        });
        values = jQuery.param(data);
        cart_loader('show',id);
            $.ajax({
               type:"POST",
               url: '{{ route('cart.updatecartq') }}',
               data: data,
               success: function(data){
                   updateNavCart();
                   totalCartItem();
                   cart_loader('hide',id);
               }
           });
        }
        else{
            showFrontendAlert('warning', 'Please choose all the options');
        }
    }

    </script>
@endsection
