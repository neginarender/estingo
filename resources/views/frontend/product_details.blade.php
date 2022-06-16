@extends('frontend.layouts.app')

@section('meta_title'){{ $detailedProduct->meta_title }}@stop

@section('meta_description'){{ $detailedProduct->meta_description }}@stop

@section('meta_keywords'){{ $detailedProduct->tags }}@stop
@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $detailedProduct->meta_title }}">
    <meta itemprop="description" content="{{ $detailedProduct->meta_description }}">
    <meta itemprop="image" content="{{ my_asset($detailedProduct->meta_img) }}">

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product">
    <meta name="twitter:site" content="@publisher_handle">
    <meta name="twitter:title" content="{{ $detailedProduct->meta_title }}">
    <meta name="twitter:description" content="{{ $detailedProduct->meta_description }}">
    <meta name="twitter:creator" content="@author_handle">
    <meta name="twitter:image" content="{{ my_asset($detailedProduct->meta_img) }}">
    <meta name="twitter:data1" content="{{ single_price($detailedProduct->unit_price) }}">
    <meta name="twitter:label1" content="Price">

    <!-- Open Graph data -->
    {{-- <meta property="og:title" content="{{ $detailedProduct->meta_title }}" />
    <meta property="og:type" content="og:product" />
    <meta property="og:url" content="{{ route('product', $detailedProduct->slug) }}" />
    <meta property="og:image" content="{{ my_asset($detailedProduct->meta_img) }}" />
    <meta property="og:description" content="{{ $detailedProduct->meta_description }}" />
    <meta property="og:site_name" content="{{ env('APP_NAME') }}" />
    <meta property="og:price:amount" content="{{ single_price($detailedProduct->unit_price) }}" />
    <meta property="product:price:currency" content="{{ \App\Currency::findOrFail(\App\BusinessSetting::where('type', 'system_default_currency')->first()->value)->code }}" />
    <meta property="fb:app_id" content="{{ env('FACEBOOK_PIXEL_ID') }}"> --}}
@endsection

@section('content')
<style type="text/css">
    .minus-btn .btn {border-radius: 15px 0 0 15px!important; border-color: #e6e6e6!important; border-right: 0!important}

    .caorusel-box .slick-slide {height: auto}
</style>
 @php
 $categories ="";
 $shortId = "";
 $productIds = "";
    if(!empty(Cookie::get('pincode'))){ 
        $pincode = Cookie::get('pincode');

        $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        if(!empty($shortId)){
            $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->pluck('product_id')->all();
            $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
            $categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->whereIn('id',$categoryIds)->get();

        }


    }else{
        $categoryIds = array();
        $productIds = array();
        $categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->get();
    }  
@endphp
    <!-- SHOP GRID WRAPPER -->
    <section class="product-details-area gry-bg">
        <div class="container">

            <div class="bg-white">

                <!-- Product gallery and Description -->
                <div class="row no-gutters cols-xs-space cols-sm-space cols-md-space">
                    <div class="col-lg-5">
                        <div class="product-gal sticky-top  ">
                            <input type="hidden" id="pid" value="{{ $detailedProduct->id }}">
                            @if(is_array(json_decode($detailedProduct->photos)) && count(json_decode($detailedProduct->photos)) > 0)
                                <div class="product-gal-img">
                                    <img src="{{static_asset('frontend/images/placeholder.jpg')}}" class="xzoom img-fluid lazyload" src="{{static_asset('frontend/images/placeholder.jpg')}}" data-src="{{Storage::disk('s3')->url(json_decode($detailedProduct->photos)[0])}}" xoriginal="{{Storage::disk('s3')->url(json_decode($detailedProduct->photos)[0])}}" />
                                   <!--<img src="{{static_asset('frontend/images/placeholder.jpg')}}" class="xzoom img-fluid lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" xoriginal="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" /> -->
                                </div>
                                <div class="product-gal-thumb">
                                    <div class="xzoom-thumbs">
                                        @foreach (json_decode($detailedProduct->photos) as $key => $photo)
                                            <a href="{{ my_asset($photo) }}">
                                                <img src="{{static_asset('frontend/images/placeholder.jpg')}}" class="xzoom-gallery lazyload" src="{{static_asset('frontend/images/placeholder.jpg')}}"   data-src="{{Storage::disk('s3')->url($photo)}}"  @if($key == 0) xpreview="{{Storage::disk('s3')->url($photo)}}" @endif>
                                            </a>
                                            
                                            <!-- <a href="{{ my_asset($photo) }}">
                                                <img src="{{static_asset('frontend/images/placeholder.jpg')}}" class="xzoom-gallery lazyload" src="{{static_asset('frontend/images/placeholder.jpg')}}"   data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"  @if($key == 0) xpreview="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" @endif>
                                            </a> -->
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <!-- Product description -->
                        <div class="product-description-wrapper">
                            <!-- Product title -->

                            {{-- @if(!empty($detailedProduct->product->discount))
                                <span class="discount">{{ round($detailedProduct->product->discount,2)}}% off</span>
                            @endif --}}
                            <?php
                             if(isset($detailedProduct['subcategory_id'])){

                                 $subSubCategoryName = \App\SubCategory::where('id',$detailedProduct['subcategory_id'])->first('name');

                             }
                             ?>
                           <div class="product_cat"><span><a href="{{ url('/')}}/search?category={{ @$detailedProduct->category->slug }}">{{@$detailedProduct->category->name}} </a> > <a href="{{ url('/')}}/search?subcategory={{ @$detailedProduct->subcategory->slug }}">{{ @$detailedProduct->subcategory->name }}</a> > <a href="{{ url('/')}}/search?subsubcategory={{ @$detailedProduct->subsubcategory->slug}}">{{ @$detailedProduct->subsubcategory->name}}</a></span></div>
                             <ul class="inline-links inline-links--style-1 float-right status">
                                        @php
                                            $qty = 0;
                                            if(Cookie::has('pincode')){
                                                $qty = mapped_product_stock($shortId->sorting_hub_id,$detailedProduct->id);
                                                }else{
                                            if($detailedProduct->variant_product){
                                                foreach ($detailedProduct->stocks as $key => $stock) {
                                                    $qty += $stock->qty;
                                                }
                                            }
                                            else{
                                                $qty = $detailedProduct->current_stock;
                                            }
                                                }
                                        @endphp
                                        @if ($qty > 0)
                                            <li>
                                                <span class="badge badge-md badge-pill bg-green">{{ translate('In stock')}}</span>
                                            </li>
                                        @else
                                            <li>
                                                <span class="badge badge-md badge-pill bg-red">{{ translate('Out of stock')}}</span>
                                            </li>
                                        @endif
                                    </ul>
                            <h1 class="product-title mb-2" id = "product_name">
                                {{  __($detailedProduct->name) }}
                            </h1>


                            <div class="row align-items-center my-0">
                                <div class="col-6">
                                    <!-- Rating stars -->
                                    <div class="rating">
                                        @php
                                            $total = 0;
                                            $product_rating = $detailedProduct->reviews;
                                            $total += $product_rating->count();
                                            $sum_of_product_rating = $product_rating->map(function($item){
                                                return $item->rating;
                                            });
                                            
                                        @endphp
                                        <span class="star-rating">
                                            {{ calculateRating($total,$sum_of_product_rating->sum()) }}
                                        </span>
                                        @if(count($product_rating))
                                        <span class="rating-count ml-1">({{ $total }} {{ translate('reviews')}})</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6 text-right">
                                   
                                </div>
                            </div>

                               
                           
                             <div class="row no-gutters mt-1">
                                    <div class="col-3">
                                        <div class="product-description-label">{{ translate('Price')}}:</div>
                                    </div>
                                    <div class="col-9">
                                        <div class="product-price">
                                        @php
                                        $cataloFb = "";
                                        @endphp

                                            @if(Session::has('referal_discount'))
                                                
                                                <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($detailedProduct->id,$shortId)) }}</span>
                                                <del class="old-product-price strong-400">{{ home_price($detailedProduct->id,$shortId) }}</del>

                                                <span class="product-price strong-600">({{ round(peer_discounted_percentage($detailedProduct->id,$shortId),2) }}% Off)</span>
                                            @else    
                                                  <span class="product-price strong-600">{{ home_price($detailedProduct->id,$shortId)  }}</span>
                                            @endif
                                            
                                            <!-- <strong>
                                                {{ home_price($detailedProduct->id)  }}
                                            </strong> -->

                                            {{-- <span class="piece">/{{ $detailedProduct->unit }}</span> --}}
                                        </div>
                                    </div>
                                </div>

                                {{-- facebook catalogue start--}}
                                <div itemscope itemtype="http://schema.org/Product">
                                @if ($detailedProduct->brand != null)
                                <meta itemprop="brand" content={{$detailedProduct->brand->name}}>
                                @endif
                                <meta itemprop="name" content={{$detailedProduct->name}}>
                                <meta itemprop="description" content={{$detailedProduct->meta_description}}>
                                <meta itemprop="productID" content={{$detailedProduct->id}}>
                                <meta itemprop="url" content={{route('product', $detailedProduct->slug)}}>
                                <meta itemprop="googleProductCategory" content={{$detailedProduct->category->name}}>
                                
                                @if ($detailedProduct->photos != '[]')
                                <meta itemprop="image" content={{Storage::disk('s3')->url(json_decode($detailedProduct->photos)[0])}}>
                                @endif

                                <div itemprop="value" itemscope itemtype="http://schema.org/PropertyValue">
                                    <span itemprop="propertyID" content={{$detailedProduct->category_id}}></span>
                                    <meta itemprop="value" content="fb_tshirts"></meta>
                                </div>
                                <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                                    <link itemprop="availability" href="http://schema.org/InStock">
                                    <link itemprop="itemCondition" href="http://schema.org/NewCondition">
                                    <meta itemprop="price" content={{ single_price(peer_discounted_newbase_price($detailedProduct->id,$shortId)) }}>
                                    <meta itemprop="priceCurrency" content="INR">
                                </div>
                                </div>
                                {{-- facebook catalogue end --}}

                        

                            @if (\App\Addon::where('unique_identifier', 'club_point')->first() != null && \App\Addon::where('unique_identifier', 'club_point')->first()->activated && $detailedProduct->earn_point > 0)
                                <div class="row no-gutters mt-4  ">
                                    <div class="col-3">
                                        <div class="product-description-label">{{  translate('Club Point') }}:</div>
                                    </div>
                                    <div class="col-9">
                                        <div class="d-inline-block club-point bg-soft-base-1 border-light-base-1 border">
                                            <span class="strong-700">{{ $detailedProduct->earn_point }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                           
                            
                            <?php
                           $cartQty = 0; 
                           if(session()->has('cart'))
                           {
                            $cartQty=0;
                            $cart = session()->get('cart')->where('id',$detailedProduct->id)->first();
                            if(!is_null($cart)){
                                $cartQty = $cart['quantity'];
                            }
                           
                           }

                           if(!empty($shortId)){
                                $maxquantity_bysh = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('product_id',$detailedProduct->id)->where('published',1)->first('max_purchaseprice');
                                $maxquantity = $maxquantity_bysh['max_purchaseprice'];
                            }else{
                                $maxquantity = 0;
                            }
                            if(!empty($detailedProduct->max_purchase_qty)){
                                $adminmaxquantity = $detailedProduct->max_purchase_qty;
                            }else{
                                $adminmaxquantity = 0;
                            }  
                           ?>
                            <input type="hidden" name="limit_qty" id="limit_qty" value="@if($maxquantity!=0){{ $maxquantity}} @else {{ $adminmaxquantity }} @endif" />
                            <input name="cart_qty" id="cart_qty" type="hidden" value="@if(!empty($cartQty)){{ $cartQty }} @else 0 @endif" />
                           
                            <form id="option-choice-form">
                                @csrf
                                <input type="hidden" name="id" value="{{ $detailedProduct->id }}">

                                @if ($detailedProduct->choice_options != null)
                                    @foreach (json_decode($detailedProduct->choice_options) as $key => $choice)

                                    <div class="row no-gutters mt-3 pb-1">
                                        <div class="col-3">
                                            <div class="product-description-label mt-2 ">{{ \App\Attribute::find($choice->attribute_id)->name }}:</div>
                                        </div>
                                        <div class="col-9">
                                            <ul class="list-inline checkbox-alphanumeric checkbox-alphanumeric--style-1 mb-2">
                                                @foreach ($choice->values as $key => $value)
                                                    <li>
                                                        <input type="radio" id="{{ $choice->attribute_id }}-{{ $value }}" name="attribute_id_{{ $choice->attribute_id }}" value="{{ $value }}" @if($key == 0) checked @endif>
                                                        <label for="{{ $choice->attribute_id }}-{{ $value }}">{{ $value }}</label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>

                                    @endforeach
                                @endif

                                @if (count(json_decode($detailedProduct->colors)) > 0)
                                    <div class="row no-gutters">
                                        <div class="col-3">
                                            <div class="product-description-label mt-2">{{ translate('Color')}}:</div>
                                        </div>
                                        <div class="col-9">
                                            <ul class="list-inline checkbox-color mb-1">
                                                @foreach (json_decode($detailedProduct->colors) as $key => $color)
                                                    <li>
                                                        <input type="radio" id="{{ $detailedProduct->id }}-color-{{ $key }}" name="color" value="{{ $color }}" @if($key == 0) checked @endif>
                                                        <label style="background: {{ $color }};" for="{{ $detailedProduct->id }}-color-{{ $key }}" data-toggle="tooltip"></label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>

                                    
                                @endif
                               
                               
                                <!-- Quantity + Add to cart -->
                                <div class="row no-gutters pb-2 pt-2">
                                    <div class="col-3">
                                        <div class="product-description-label mt-2">{{ translate('Quantity')}}:</div>
                                    </div>
                                    <div class="col-9">
                                        <div class="product-quantity d-flex align-items-center">
                                            <div class="input-group input-group--style-2 pr-3" style="width: 120px;">
                                         
                                            <input type="hidden" class="form-control h-auto input-number text-center" name="limit_qty" value="@if(!empty($detailedProduct->max_purchase_qty)){{ $detailedProduct->max_purchase_qty}} @else 0 @endif">
                                                <span class="input-group-btn minus-btn">
                                                    <button class="btn btn-number" type="button" data-type="minus" data-field="quantity" disabled="disabled">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                </span>
                                                <input style="font-weight: 600" type="text" name="quantity" class="form-control h-auto input-number text-center" placeholder="1" value="@if(isset( $_COOKIE[$detailedProduct->id])){{ $_COOKIE[$detailedProduct->id] }} @else 1 @endif" min="1" max="@if(!empty(Cookie::has('pincode'))){{ mapped_product_stock($shortId->sorting_hub_id,$detailedProduct->id)}} @else 0 @endif">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-number" type="button" data-type="plus" data-field="quantity">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </span>
                                            </div>
                                            
                                            @php
                                            $stock_available = 0;
                                            if(!empty(Cookie::has('pincode')))
                                            {
                                                $stock_available = mapped_product_stock($shortId->sorting_hub_id,$detailedProduct->id);
                                            }                               
                                            

                                            if(!empty($shortId)){
                                                $maxquantity_bysh = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('product_id',$detailedProduct->id)->where('published',1)->first('max_purchaseprice');
                                                $maxquantity = $maxquantity_bysh['max_purchaseprice'];
                                            }else{
                                                $maxquantity = 0;
                                            }
                                            if(!empty($detailedProduct->max_purchase_qty)){
                                                $adminmaxquantity = $detailedProduct->max_purchase_qty;
                                            }else{
                                                $adminmaxquantity = 0;
                                            }  
                                            @endphp
                                            
                                            @if($stock_available < 10 && $stock_available > 0)
                                            <div class="avialable-amount" style="color:red;"><span id="available-quantity"> {{ $stock_available }}</span> Left</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-3">
                                    <div class="product-description-label mt-2">Max Purchase Limit</div>    
                                    </div>
                                    <div class="col-9">
                                    <div class="product-description-label mt-2">  
                                    <span class="btn btn-info btn-sm" style="cursor:text;">
                                    @if($maxquantity!=0){{ $maxquantity}} @else {{ $adminmaxquantity }} @endif</div>
                        </span></div>   

                                </div>
                                

                                <div class="row no-gutters pb-3 d-none" id="chosen_price_div">
                                    <div class="col-3">
                                        <div class="product-description-label">{{ translate('Total Price')}}:</div>
                                    </div>
                                    <div class="col-9">
                                        <div class="product-price">
                                            <strong id="chosen_price">

                                            </strong>
                                        </div>
                                    </div>
                                </div>

                            </form>

                            <div class="d-table width-100 mt-3">
                                <div class="d-table-cell">
                                    <!-- Buy Now button -->
                                   
                                    @if(Auth::check())
                                        @if(check_in_wishlist(Auth::user()->id,$detailedProduct->id)!=null)
                                        <button type="button" class="wishlist float-right" style="border:solid 1px red;" onclick="removeFromWishlist({{ $detailedProduct->id }},{{ Auth::user()->id }})">
                                     
                                      <i class="fa fa-heart" style="color:red;"></i>
                                    </button>       
                                      @else
                                      <button type="button" class="wishlist float-right" onclick="addToWishList({{ $detailedProduct->id }})">
                                     
                                      <i class="fa fa-heart-o"></i>
                                    </button>
                                      @endif 
                                    @else
                                    <button type="button" class="wishlist float-right" onclick="addToWishList({{ $detailedProduct->id }})">
                                     
                                      <i class="fa fa-heart-o"></i>
                                    </button>       
                                    @endif
                                    </button>   
                                    @if ($qty > 0)
                                      <button type="button" class="btn btn-cart btn-styled btn-alt-base-1 c-white btn-icon-left strong-700 hov-bounce hov-shaddow ml-2 add-to-cart" onclick="addToCart(),totalCartItem()">
                                            <i class="fa fa-cart-plus" style="font-size: 14px"></i>
                                            <span class="d-none d-md-inline-block"> {{ translate('Add to cart')}}</span>
                                        </button>
                                        <button type="button" class="btn btn-cart btn-styled btn-base-1 btn-icon-left strong-700 hov-bounce hov-shaddow buy-now" onclick="buyNow()">
                                            <i class="la la-shopping-cart" style="font-size: 16px"></i> {{ translate('Buy Now')}}
                                        </button>
                                      
                                    @else
                                        <button type="button" class="btn btn-styled btn-base-3 btn-icon-left strong-700" disabled>
                                            <i class="la la-cart-arrow-down"></i> {{ translate('Out of Stock')}}
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <hr class="mt-4">
                              <div class="row align-items-center mt-4">
                                <div class="sold-by col-auto">
                                    <small class="mr-2">{{ translate('Sold by')}}: </small><br>
                                    @if ($detailedProduct->added_by == 'seller' && \App\BusinessSetting::where('type', 'vendor_system_activation')->first()->value == 1)
                                        <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}">{{ $detailedProduct->user->shop->name }}</a>
                                    @else
                                        {{  translate('Inhouse product') }}
                                    @endif
                                </div>
                                @if (\App\BusinessSetting::where('type', 'conversation_system')->first()->value == 1)
                                    <div class="col-auto">
                                        <button class="btn btn-secondary  btn-sm" onclick="show_chat_modal()">{{ translate('Bulk Order Enquiry')}}</button>
                                    </div>
                                @endif

                                @if ($detailedProduct->brand != null)
                                    <div class="col-auto">
                                        <img src="{{Storage::disk('s3')->url($detailedProduct->brand->logo)}}" alt="{{ $detailedProduct->brand->name }}" height="30">
                                        <!-- <img src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" alt="{{ $detailedProduct->brand->name }}" height="30"> -->
                                    </div>
                                @endif
                            </div>

                            


                           <!--  <div class="d-table width-100 mt-3">
                                <div class="d-table-cell">
                                    
                                    <button type="button" class="btn btn-link btn-icon-left strong-700" onclick="addToCompare({{ $detailedProduct->id }})">
                                        {{ translate('Add to compare')}}
                                    </button>
                                    @if(Auth::check() && \App\Addon::where('unique_identifier', 'affiliate_system')->first() != null && \App\Addon::where('unique_identifier', 'affiliate_system')->first()->activated && (\App\AffiliateOption::where('type', 'product_sharing')->first()->status || \App\AffiliateOption::where('type', 'category_wise_affiliate')->first()->status) && Auth::user()->affiliate_user != null && Auth::user()->affiliate_user->status)
                                        @php
                                            if(Auth::check()){
                                                if(Auth::user()->referral_code == null){
                                                    Auth::user()->referral_code = substr(Auth::user()->id.Str::random(10), 0, 10);
                                                    Auth::user()->save();
                                                }
                                                $referral_code = Auth::user()->referral_code;
                                                $referral_code_url = URL::to('/product').'/'.$detailedProduct->slug."?product_referral_code=$referral_code";
                                            }
                                        @endphp
                                        <div class="form-group">
                                            <textarea id="referral_code_url" class="form-control" readonly type="text" style="display:none">{{$referral_code_url}}</textarea>
                                        </div>
                                        <button type=button id="ref-cpurl-btn" class="btn btn-sm btn-secondary" data-attrcpy="{{ translate('Copied')}}" onclick="CopyToClipboard('referral_code_url')">{{ translate('Copy the Promote Link')}}</button>
                                    @endif
                                </div>
                            </div> -->

                            

                            @php
                                $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
                                $refund_sticker = \App\BusinessSetting::where('type', 'refund_sticker')->first();
                            @endphp
                            @if ($refund_request_addon != null && $refund_request_addon->activated == 1 && $detailedProduct->refundable)
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="product-description-label">{{ translate('Refund')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        <a href="{{ route('returnpolicy') }}" target="_blank"> @if ($refund_sticker != null && $refund_sticker->value != null) 
                                            <img src="{{ my_asset($refund_sticker->value) }}" height="36">
                                             <!-- <img src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" height="36"> -->
                                            @else
                                           <img src="{{ static_asset('frontend/images/refund-sticker.jpg') }}" height="36">
                                            <!-- <img src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" height="36"> -->
                                             @endif</a>
                                        <a href="{{ route('returnpolicy') }}" class="ml-2" target="_blank">View Policy</a>
                                    </div>
                                </div>
                            @endif
                            @if ($detailedProduct->added_by == 'seller')
                                <div class="row no-gutters mt-3">
                                    <div class="col-2">
                                        <div class="product-description-label">{{ translate('Seller Guarantees')}}:</div>
                                    </div>
                                    <div class="col-10">
                                        @if ($detailedProduct->user->seller->verification_status == 1)
                                            {{ translate('Verified seller')}}
                                        @else
                                            {{ translate('Non verified seller')}}
                                        @endif
                                    </div>
                                </div>
                            @endif
                            <div class="row no-gutters mt-4">
                                <div class="col-2">
                                    <div class="product-description-label mt-2">{{ translate('Share')}}:</div>
                                </div>
                                <div class="col-10">
                                    <div id="share"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="gry-bg">
        <div class="container">
            <div class="row">
              <!--   <div class="col-xl-3 d-none d-xl-block">
                    <div class="seller-info-box mb-3">
                        <div class="sold-by position-relative">
                            @if ($detailedProduct->added_by == 'seller' && \App\BusinessSetting::where('type', 'vendor_system_activation')->first()->value == 1 && $detailedProduct->user->seller->verification_status == 1)
                                <div class="position-absolute medal-badge">
                                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" viewBox="0 0 287.5 442.2">
                                        <polygon style="fill:#F8B517;" points="223.4,442.2 143.8,376.7 64.1,442.2 64.1,215.3 223.4,215.3 "/>
                                        <circle style="fill:#FBD303;" cx="143.8" cy="143.8" r="143.8"/>
                                        <circle style="fill:#F8B517;" cx="143.8" cy="143.8" r="93.6"/>
                                        <polygon style="fill:#FCFCFD;" points="143.8,55.9 163.4,116.6 227.5,116.6 175.6,154.3 195.6,215.3 143.8,177.7 91.9,215.3 111.9,154.3
                                        60,116.6 124.1,116.6 "/>
                                    </svg>
                                </div>
                            @endif
                            <div class="title">{{ translate('Sold By')}}</div>
                            @if($detailedProduct->added_by == 'seller' && \App\BusinessSetting::where('type', 'vendor_system_activation')->first()->value == 1)
                                <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="name d-block">{{ $detailedProduct->user->shop->name }}
                                @if ($detailedProduct->user->seller->verification_status == 1)
                                    <span class="ml-2"><i class="fa fa-check-circle" style="color:green"></i></span>
                                @else
                                    <span class="ml-2"><i class="fa fa-times-circle" style="color:red"></i></span>
                                @endif
                                </a>
                                <div class="location">{{ $detailedProduct->user->shop->address }}</div>
                            @else
                                {{ env('APP_NAME') }}
                            @endif
                            @php
                                $total = 0;
                                $rating = 0;
                                
                            @endphp

                            <div class="rating text-center d-block">
                                <span class="star-rating star-rating-sm d-block">
                                    @if ($total > 0)
                                        {{ renderStarRating($rating/$total) }}
                                    @else
                                        {{ renderStarRating(0) }}
                                    @endif
                                </span>
                                <span class="rating-count d-block ml-0">({{ $total }} {{ translate('customer reviews')}})</span>
                            </div>
                        </div>
                        <div class="row no-gutters align-items-center">
                            @if($detailedProduct->added_by == 'seller')
                                <div class="col">
                                    <a href="{{ route('shop.visit', $detailedProduct->user->shop->slug) }}" class="d-block store-btn">{{ translate('Visit Store')}}</a>
                                </div>
                                <div class="col">
                                    <ul class="social-media social-media--style-1-v4 text-center">
                                        <li>
                                            <a href="{{ $detailedProduct->user->shop->facebook }}" class="facebook" target="_blank" data-toggle="tooltip" data-original-title="Facebook">
                                                <i class="fa fa-facebook"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ $detailedProduct->user->shop->google }}" class="google" target="_blank" data-toggle="tooltip" data-original-title="Google">
                                                <i class="fa fa-google"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ $detailedProduct->user->shop->twitter }}" class="twitter" target="_blank" data-toggle="tooltip" data-original-title="Twitter">
                                                <i class="fa fa-twitter"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ $detailedProduct->user->shop->youtube }}" class="youtube" target="_blank" data-toggle="tooltip" data-original-title="Youtube">
                                                <i class="fa fa-youtube"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="seller-top-products-box bg-white sidebar-box mb-3">
                        <div class="box-title">
                            {{ translate('Top Selling Products From This Seller')}}
                        </div>
                        <div class="box-content">
                            @foreach (filter_products(\App\Product::where('user_id', $detailedProduct->user_id)->orderBy('num_of_sale', 'desc'))->limit(6)->get() as $key => $top_product)
                            <div class="mb-3 product-box-3">
                                <div class="clearfix">
                                    <div class="product-image float-left">
                                        <a href="{{ route('product', $top_product->slug) }}">
                                            <img class="img-fit lazyload" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{Storage::disk('s3')->url($top_product->thumbnail_img)}}" alt="{{  translate($top_product->name) }}">
                                        </a>
                                    </div>
                                    <div class="product-details float-left">
                                        <h4 class="title text-truncate">
                                            <a href="{{ route('product', $top_product->slug) }}" class="d-block">{{ $top_product->name }}</a>
                                        </h4>
                                        <div class="star-rating star-rating-sm mt-1">
                                            {{ renderStarRating($top_product->rating) }}
                                        </div>
                                        <div class="price-box">
                                            <span class="product-price strong-600">{{ home_discounted_base_price($top_product->id) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div> -->
                <div class="col-xl-12">
                    <div class="product-desc-tab bg-white">
                        <div class="tabs tabs--style-2">
                            <ul class="nav nav-tabs   sticky-top bg-white">
                                <li class="nav-item">
                                    <a href="#tab_default_1" data-toggle="tab" class="nav-link text-uppercase strong-600 active show">{{ translate('Description')}}</a>
                                </li>
                                @if($detailedProduct->video_link != null)
                                    <li class="nav-item">
                                        <a href="#tab_default_2" data-toggle="tab" class="nav-link text-uppercase strong-600">{{ translate('Video')}}</a>
                                    </li>
                                @endif
                                @if($detailedProduct->pdf != null)
                                    <li class="nav-item">
                                        <a href="#tab_default_3" data-toggle="tab" class="nav-link text-uppercase strong-600">{{ translate('Downloads')}}</a>
                                    </li>
                                @endif
                                <li class="nav-item">
                                    <a href="#tab_default_4" data-toggle="tab" class="nav-link text-uppercase strong-600">{{ translate('Reviews')}}</a>
                                </li>
                            </ul>

                            <div class="tab-content pt-0">
                                <div class="tab-pane active show" id="tab_default_1">
                                    <div class="py-2 px-4">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mw-100 overflow--hidden aiz-product-description">
                                                    <?php echo $detailedProduct->description; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab_default_2">
                                    <div class="fluid-paragraph py-2">
                                        <!-- 16:9 aspect ratio -->
                                        <div class="embed-responsive embed-responsive-16by9 mb-5">
                                            @if ($detailedProduct->video_provider == 'youtube' && $detailedProduct->video_link != null)
                                                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{{ explode('=', $detailedProduct->video_link)[1] }}"></iframe>
                                            @elseif ($detailedProduct->video_provider == 'dailymotion' && $detailedProduct->video_link != null)
                                                <iframe class="embed-responsive-item" src="https://www.dailymotion.com/embed/video/{{ explode('video/', $detailedProduct->video_link)[1] }}"></iframe>
                                            @elseif ($detailedProduct->video_provider == 'vimeo' && $detailedProduct->video_link != null)
                                                <iframe src="https://player.vimeo.com/video/{{ explode('vimeo.com/', $detailedProduct->video_link)[1] }}" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab_default_3">
                                    <div class="py-2 px-4">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <a href="{{ my_asset($detailedProduct->pdf) }}">{{  translate('Download') }}</a>
                                            </div>
                                        </div>
                                        <span class="space-md-md"></span>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab_default_4">
                                    <div class="fluid-paragraph py-4">
                                        @foreach ($detailedProduct->reviews as $key => $review)
                                            <div class="block block-comment">
                                                <div class="block-image">
                                                    <img
                                                      src="{{ static_asset('frontend/images/placeholder.jpg') }}"
                                                        @if($review->user->avatar_original !=null)
                                                            data-src="{{ my_asset($review->user->avatar_original) }}"
                                                        @else
                                                            data-src="{{ static_asset('frontend/images/user.png') }}"
                                                        @endif
                                                        class="rounded-circle lazyload"
                                                        >
                                                     <!-- <img
                                                        src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"
                                                        @if($review->user->avatar_original !=null)
                                                            data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"
                                                        @else
                                                            data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}"
                                                        @endif
                                                        class="rounded-circle lazyload"
                                                        >    -->
                                                </div>
                                                <div class="block-body">
                                                    <div class="block-body-inner">
                                                        <div class="row no-gutters">
                                                            <div class="col">
                                                                <h3 class="heading heading-6">
                                                                    <p>{{ ucfirst($review->user->name) }}</p>
                                                                </h3>
                                                                <span class="comment-date">
                                                                    {{ date('d-m-Y', strtotime($review->created_at)) }}
                                                                </span>
                                                            </div>
                                                            <div class="col">
                                                                <div class="rating text-right clearfix d-block">
                                                                    <span class="star-rating star-rating-sm float-right">
                                                                        @for ($i=0; $i < $review->rating; $i++)
                                                                            <i class="fa fa-star active"></i>
                                                                        @endfor
                                                                        @for ($i=0; $i < 5-$review->rating; $i++)
                                                                            <i class="fa fa-star"></i>
                                                                        @endfor
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <p class="comment-text">
                                                            {{ $review->comment }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        @if(count($detailedProduct->reviews) <= 0)
                                            <div class="text-center">
                                                {{  translate('There have been no reviews for this product yet.') }}
                                            </div>
                                        @endif

                                        @if(Auth::check())
                                            @php
                                                $commentable = false;
                                            @endphp
                                            @foreach ($detailedProduct->orderDetails as $key => $orderDetail)
                                                @if($orderDetail->order != null && $orderDetail->order->user_id == Auth::user()->id && $orderDetail->delivery_status == 'delivered' && \App\Review::where('user_id', Auth::user()->id)->where('product_id', $detailedProduct->id)->first() == null)
                                                    @php
                                                        $commentable = true;
                                                    @endphp
                                                @endif
                                            @endforeach
                                            @if ($commentable)
                                                <div class="leave-review">
                                                    <div class="section-title section-title--style-1">
                                                        <h3 class="section-title-inner heading-6 strong-600 text-uppercase">
                                                            {{ translate('Write a review')}}
                                                        </h3>
                                                    </div>
                                                    <form class="form-default" role="form" action="{{ route('reviews.store') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="" class="text-uppercase c-gray-light">{{ translate('Your name')}}</label>
                                                                    <input type="text" name="name" value="{{ Auth::user()->name }}" class="form-control" disabled required>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="" class="text-uppercase c-gray-light">{{ translate('Email')}}</label>
                                                                    <input type="text" name="email" value="{{ Auth::user()->email }}" class="form-control" required disabled>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <div class="c-rating mt-1 mb-1 clearfix d-inline-block">
                                                                    <input type="radio" id="star5" name="rating" value="5" required/>
                                                                    <label class="star" for="star5" title="Awesome" aria-hidden="true"></label>
                                                                    <input type="radio" id="star4" name="rating" value="4" required/>
                                                                    <label class="star" for="star4" title="Great" aria-hidden="true"></label>
                                                                    <input type="radio" id="star3" name="rating" value="3" required/>
                                                                    <label class="star" for="star3" title="Very good" aria-hidden="true"></label>
                                                                    <input type="radio" id="star2" name="rating" value="2" required/>
                                                                    <label class="star" for="star2" title="Good" aria-hidden="true"></label>
                                                                    <input type="radio" id="star1" name="rating" value="1" required/>
                                                                    <label class="star" for="star1" title="Bad" aria-hidden="true"></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-3">
                                                            <div class="col-sm-12">
                                                                <textarea class="form-control" rows="4" name="comment" placeholder="{{ translate('Your review')}}" required></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="text-right">
                                                            <button type="submit" class="btn btn-styled btn-base-1 btn-circle mt-4">
                                                                {{ translate('Send review')}}
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="my-4 bg-white p-3">
                        <div class="section-title-1">
                            <h3 class="heading-5 strong-700 mb-0">
                                <span class="mr-4">{{ translate('Related products')}}</span>
                            </h3>
                        </div>
                        <div class="caorusel-box arrow-round gutters-5">
                            <div class="slick-carousel" data-slick-items="3" data-slick-xl-items="3" data-slick-lg-items="3"  data-slick-md-items="2" data-slick-sm-items="1" data-slick-xs-items="1"  data-slick-rows="2">
                                @php
                                
                                if(!empty($productIds)){
                                    $relatedProduct = \App\Product::where('subcategory_id', $detailedProduct->subcategory_id)->where('id', '!=', $detailedProduct->id)->whereIn('id',$productIds)->limit(10)->get();
                                }else{
                                    $relatedProduct = \App\Product::where('subcategory_id', $detailedProduct->subcategory_id)->where('id', '!=', $detailedProduct->id)->limit(10)->get();

                                }
                               
                                @endphp
                                @foreach (filter_products($relatedProduct) as $key => $related_product)
                                <div class="caorusel-card my-1">
                                    <div class="row no-gutters product-box-2 align-items-center">
                                        <div class="col-5">
                                            <div class="position-relative overflow-hidden h-100">
                                                <a href="{{ route('product', $related_product->slug) }}" class="d-block product-image h-100 text-center">
                                      <img class="img-fit lazyload" src="{{static_asset('frontend/images/placeholder.jpg')}}" data-src="{{Storage::disk('s3')->url($related_product->thumbnail_img)}}" alt="{{  __($related_product->name) }}">
                                                    <!-- <img class="img-fit lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" data-src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" alt="{{  __($related_product->name) }}"> -->
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-7 border-left">
                                            <div class="p-3">
                                                <h2 class="product-title mb-0 p-0 text-truncate">
                                                    <a href="{{ route('product', $related_product->slug) }}">{{  __($related_product->name) }}</a>
                                                </h2>
                                                @php
                                                $related_product_rating = $related_product->reviews;
                                                $no_of_users = count($related_product_rating);
                                                $sum_of_rating = $related_product_rating->map(function($item){
                                                   return $item->rating;
                                                });
                                                
                                            @endphp
                                                <div class="star-rating star-rating-sm mb-2">
                                                    {{ calculateRating($no_of_users,$sum_of_rating->sum()) }}
                                                </div>
                                                <div class="clearfix">
                                                    <div class="price-box float-left">
                                                        @if(home_base_price($related_product->id) != home_discounted_base_price($related_product->id))
                                                           <!--  <del class="old-product-price strong-400">{{ home_base_price($related_product->id) }}</del> -->
                                                        @endif

                                                         @if(Session::has('referal_discount'))
                                                        
                                                            <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($related_product->id,$shortId)) }}</span>
                                                            <del class="old-product-price strong-400">{{ home_price($related_product->id,$shortId) }}</del>
                                                            <span class="product-price strong-600">({{ round(peer_discounted_percentage($related_product->id,$shortId),2) }}% Off)</span>
                                                        @else    
                                                              <span class="product-price strong-600">{{ home_price($related_product->id,$shortId)  }}</span>
                                                        @endif
                                                        <!-- <span class="product-price strong-600">{{ home_discounted_base_price($related_product->id) }}</span> -->
                                                    </div>
                                                    @if (\App\Addon::where('unique_identifier', 'club_point')->first() != null && \App\Addon::where('unique_identifier', 'club_point')->first()->activated)
                                                        <div class="float-right club-point bg-soft-base-1 border-light-base-1 border">
                                                            {{  translate('Club Point') }}:
                                                            <span class="strong-700 float-right">{{ $related_product->earn_point }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" id="chat_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title strong-600 heading-5">{{ translate('Any query about this product')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="" action="{{ route('conversations.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $detailedProduct->id }}">
                    <div class="modal-body gry-bg px-3 pt-3">
                        <div class="form-group">
                            <input type="text" class="form-control mb-3" name="title" value="{{ $detailedProduct->name }}" placeholder="{{ translate('Product Name') }}" required>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" rows="8" name="message" required placeholder="{{ translate('Your Question') }}">{{ route('product', $detailedProduct->slug) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-dismiss="modal">{{ translate('Cancel')}}</button>
                        <button type="submit" class="btn btn-base-1 btn-styled">{{ translate('Send')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="login_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-zoom" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLabel">{{ translate('Login')}}</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-3">
                        <form class="form-default" role="form" action="{{ route('cart.login.submit') }}" method="POST">
                            @csrf
                            @if (\App\Addon::where('unique_identifier', 'otp_system')->first() != null && \App\Addon::where('unique_identifier', 'otp_system')->first()->activated)
                                <span>{{  translate('Use country code before number') }}</span>
                            @endif
                            <div class="form-group">
                                @if (\App\Addon::where('unique_identifier', 'otp_system')->first() != null && \App\Addon::where('unique_identifier', 'otp_system')->first()->activated)
                                    <input type="text" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{ translate('Email Or Phone')}}" name="email" id="email">
                                @else
                                    <input type="email" class="form-control h-auto form-control-lg {{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email">
                                @endif
                            </div>

                            <div class="form-group">
                                <input type="password" name="password" class="form-control form-control-lg h-auto" placeholder="{{ translate('Password')}}">
                            </div>

                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <a href="#" class="link link-xs link--style-3">{{ translate('Forgot password?')}}</a>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" class="btn btn-styled btn-base-1 px-4">{{ translate('Sign in')}}</button>
                                </div>
                            </div>
                        </form>

                        <div class="text-center pt-3">
                            <p class="text-md">
                                {{ translate('Need an account?')}} <a href="{{ route('user.registration') }}" class="strong-600">{{ translate('Register Now')}}</a>
                            </p>
                        </div>
                        @if(\App\BusinessSetting::where('type', 'google_login')->first()->value == 1 || \App\BusinessSetting::where('type', 'facebook_login')->first()->value == 1 || \App\BusinessSetting::where('type', 'twitter_login')->first()->value == 1)
                            <div class="or or--1 my-3 text-center">
                                <span>{{ translate('or')}}</span>
                            </div>
                            @if(\App\BusinessSetting::where('type', 'google_login')->first()->value == 1)
                                <a href="{{ route('social.login', ['provider' => 'google']) }}" class="btn btn-styled btn-block btn-google btn-icon--2 btn-icon-left px-4 mb-3">
                                    <i class="icon fa fa-google"></i> {{ translate('Login with Google')}}
                                </a>
                            @endif
                            @if (\App\BusinessSetting::where('type', 'facebook_login')->first()->value == 1)
                                <a href="{{ route('social.login', ['provider' => 'facebook']) }}" class="btn btn-styled btn-block btn-facebook btn-icon--2 btn-icon-left px-4 mb-3">
                                    <i class="icon fa fa-facebook"></i> {{ translate('Login with Facebook')}}
                                </a>
                            @endif
                            @if (\App\BusinessSetting::where('type', 'twitter_login')->first()->value == 1)
                            <a href="{{ route('social.login', ['provider' => 'twitter']) }}" class="btn btn-styled btn-block btn-twitter btn-icon--2 btn-icon-left px-4 mb-3">
                                <i class="icon fa fa-twitter"></i> {{ translate('Login with Twitter')}}
                            </a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

    <script type="text/javascript">
    
        $(document).ready(function() {

    		$('#share').jsSocials({
    			showLabel: false,
                showCount: false,
                text:false,
                logo:"https://rozaana.s3.ap-south-1.amazonaws.com/uploads/products/photos/aUTJsUm443btWEb1wjrm7zpoDrvVAuhEy3hvr3BJ.webp",
                shares: ["email", "twitter", "facebook", "linkedin", "pinterest", "stumbleupon", "whatsapp"]
    		});
            getVariantPrice();
    	});

        function CopyToClipboard(containerid) {
            if (document.selection) {
                var range = document.body.createTextRange();
                range.moveToElementText(document.getElementById(containerid));
                range.select().createTextRange();
                document.execCommand("Copy");

            } else if (window.getSelection) {
                var range = document.createRange();
                document.getElementById(containerid).style.display = "block";
                range.selectNode(document.getElementById(containerid));
                window.getSelection().addRange(range);
                document.execCommand("Copy");
                document.getElementById(containerid).style.display = "none";

            }
            showFrontendAlert('success', 'Copied');
        }

        function show_chat_modal(){
            @if (Auth::check())
                $('#chat_modal').modal('show');
            @else
                $('#login_modal').modal('show');
            @endif
        }

        function removeFromWishlist(id,user_id){
            $.post('{{ route('wishlists.remove') }}',{_token:'{{ csrf_token() }}', id:id,user_id:user_id}, function(data){
                //$('#wishlist').html(data);
                //$('#wishlist_'+id).hide();
                showFrontendAlert('success', 'Item has been removed from wishlist');
                window.location.reload();
            })
        }

        var product_id = document.getElementById('pid').value;

        $("#myJSONID").text(function() {
            return JSON.stringify({
                "@context": "http://schema.org/",
                "@type": "Recipe",
                "productID":product_id
            });
        });

    </script>



@endsection