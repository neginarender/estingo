@extends('frontend.layouts.app')

@if(isset($subsubcategory_id))
    @php
        $meta_title = \App\SubSubCategory::find($subsubcategory_id)->meta_title;
        $meta_description = \App\SubSubCategory::find($subsubcategory_id)->meta_description;
    @endphp
@elseif (isset($subcategory_id))
    @php
        $meta_title = \App\SubCategory::find($subcategory_id)->meta_title;
        $meta_description = \App\SubCategory::find($subcategory_id)->meta_description;
    @endphp
@elseif (isset($category_id))
    @php
        $meta_title = \App\Category::find($category_id)->meta_title;
        $meta_description = \App\Category::find($category_id)->meta_description;
    @endphp
@elseif (isset($brand_id))
    @php
        $meta_title = \App\Brand::find($brand_id)->meta_title;
        $meta_description = \App\Brand::find($brand_id)->meta_description;
    @endphp
@else
    @php
        $meta_title = env('APP_NAME');
        $meta_description = \App\SeoSetting::first()->description;
    @endphp
@endif
@php
$shortId = "";
$productIds = [];
if(!empty(Cookie::get('pincode'))){ 
                                    $pincode = Cookie::get('pincode');
                                    $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
                                    if(!empty($shortId)){
                                        $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->where('flash_deal',0)->pluck('product_id')->all();
                                        $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
                                        $subcategoryIds = \App\Product::where('published',1)->whereIn('id',$productIds)->distinct()->pluck('subcategory_id')->all();
                                        $subsubcategoryIds = \App\Product::where('published',1)->whereIn('id',$productIds)->distinct()->pluck('subsubcategory_id')->all();
                                    }else{
                                        $categoryIds = array();
                                        $subcategoryIds = array();
                                        $subsubcategoryIds = array();
                                    }
                                    


                                }else{
                                    $categoryIds = \App\Product::where('published', '1')->distinct()->pluck('category_id')->all();
                                    $productIds = array();
                                    $subcategoryIds = \App\Product::where('published',1)->distinct()->pluck('subcategory_id')->all();
                                    $subsubcategoryIds = \App\Product::where('published',1)->distinct()->pluck('subsubcategory_id')->all();
                                   
                                }

@endphp
@section('meta_title'){{ $meta_title }}@stop
@section('meta_description'){{ $meta_description }}@stop

@section('meta')
    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $meta_title }}">
    <meta itemprop="description" content="{{ $meta_description }}">

    <!-- Twitter Card data -->
    <meta name="twitter:title" content="{{ $meta_title }}">
    <meta name="twitter:description" content="{{ $meta_description }}">

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $meta_title }}" />
    <meta property="og:description" content="{{ $meta_description }}" />

@endsection



@section('content')
<style type="text/css">   .spinner-border{position: absolute; left: 45%; bottom: 20px;}   </style>

@php
$getBrandIds = "";
@endphp

    <div class="breadcrumb-area">
        <div class="container">
            <div class="row">
                <div class="col">
                    <ul class="breadcrumb">
                        <li><a href="{{ route('home') }}">{{ translate('Home')}}</a></li>
                        <li><a href="{{ route('products') }}">{{ translate('All Categories')}}</a></li>
                        <?php $link = route('products');?>
                        @if(isset($category_id))
                            @php
                            $link = route('products.category', \App\Category::find($category_id)->slug);
                            $getBrandIds = \App\Product::where(['category_id'=>$category_id,'published'=>1])->groupBy('brand_id')->get('brand_id')->toArray();
                            @endphp
                            <li class="active"><a href="{{ route('products.category', \App\Category::find($category_id)->slug) }}">{{ \App\Category::find($category_id)->name }}</a></li>
                        @endif
                        @if(isset($subcategory_id))
                            @php
                            $link = route('products.subcategory', \App\SubCategory::find($subcategory_id)->slug);
                            $getBrandIds = \App\Product::where(['subcategory_id'=>$subcategory_id,'published'=>1])->groupBy('brand_id')->get('brand_id')->toArray();
                            @endphp
                            <li ><a href="{{ route('products.category', \App\SubCategory::find($subcategory_id)->category->slug) }}">{{ \App\SubCategory::find($subcategory_id)->category->name }}</a></li>
                            <li class="active"><a href="{{ route('products.subcategory', \App\SubCategory::find($subcategory_id)->slug) }}">{{ \App\SubCategory::find($subcategory_id)->name }}</a></li>
                        @endif
                        @if(isset($subsubcategory_id))
                            @php
                            $link = route('products.subsubcategory', \App\SubSubCategory::find($subsubcategory_id)->slug);
                            $getBrandIds = \App\Product::where(['subsubcategory_id'=>$subsubcategory_id,'published'=>1])->groupBy('brand_id')->get('brand_id')->toArray();
                            @endphp
                            <li ><a href="{{ route('products.category', \App\SubSubCategory::find($subsubcategory_id)->subcategory->category->slug) }}">{{ \App\SubSubCategory::find($subsubcategory_id)->subcategory->category->name }}</a></li>
                            <li ><a href="{{ route('products.subcategory', \App\SubsubCategory::find($subsubcategory_id)->subcategory->slug) }}">{{ \App\SubsubCategory::find($subsubcategory_id)->subcategory->name }}</a></li>
                            <li class="active"><a href="{{ route('products.subsubcategory', \App\SubSubCategory::find($subsubcategory_id)->slug) }}">{{ \App\SubSubCategory::find($subsubcategory_id)->name }}</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>


    <section class="gry-bg py-4">
        <div class="container sm-px-0">
            <form class="" id="search-form" action="{{ route('search') }}" method="GET">
                <div class="row">
                <div class="col-xl-3 side-filter d-xl-block">
                    <div class="filter-overlay filter-close"></div>
                    <div class="filter-wrapper c-scrollbar">
                        <div class="filter-title d-flex d-xl-none justify-content-between pb-3 align-items-center">
                            <h3 class="h6">Filters</h3>
                            <button type="button" class="close filter-close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="bg-white sidebar-box mb-3">
                            <div class="box-title text-center">
                                {{ translate('Categories')}}
                            </div>
                            <div class="box-content">
                                <div class="category-filter">
                                    <ul>
                                        @if(!isset($category_id) && !isset($category_id) && !isset($subcategory_id) && !isset($subsubcategory_id))

                                            @php
                                                if(!empty(Session::get('pincode'))){ 
                                                    $pincode = Session::get('pincode');

                                                    $distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->pluck('id')->all();
                                                    if(!empty($shortId)){
                                                        $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->pluck('product_id')->all();
                                                        $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
                                                        $categories = \App\Category::whereIn('id',$categoryIds)->where('status',1)->get();

                                                    }else{
                                                        $categories = array();
                                                    }


                                                }else{
                                                    $categoryIds = array();
                                                    $productIds = array();
                                                     $categories = \App\Category::where('featured', 1)->where('status',1)->get();
                                                }  
                                            @endphp
                                            @foreach($categories as $category)
                                                <li class=""><a href="{{ route('products.category', $category->slug) }}">{{  __($category->name) }}</a></li>
                                            @endforeach
                                        @endif
                                        @if(isset($category_id))
                                            <li class="active"><a href="{{ route('products') }}">{{ translate('All Categories')}}</a></li>
                                            <li class="active"><a href="{{ route('products.category', \App\Category::find($category_id)->slug) }}">{{  translate(\App\Category::find($category_id)->name) }}</a></li>
                                            @foreach (\App\Category::find($category_id)->subcategories as $key2 => $subcategory)
                                                @if(in_array($subcategory->id,$subcategoryIds))
                                                <li class="child"><a href="{{ route('products.subcategory', $subcategory->slug) }}">{{  __($subcategory->name) }}</a></li>
                                                @endif 
                                            @endforeach
                                        @endif

                                        @if(isset($subcategory_id))
                                            <li class="active"><a href="{{ route('products') }}">{{ translate('All Categories')}}</a></li>
                                            <li class="active"><a href="{{ route('products.category', \App\SubCategory::find($subcategory_id)->category->slug) }}">{{  translate(\App\SubCategory::find($subcategory_id)->category->name) }}</a></li>
                                            <li class="active"><a href="{{ route('products.subcategory', \App\SubCategory::find($subcategory_id)->slug) }}">{{  translate(\App\SubCategory::find($subcategory_id)->name) }}</a></li>
                                            @foreach (\App\SubCategory::find($subcategory_id)->subsubcategories as $key3 => $subsubcategory)
                                                @if(in_array($subsubcategory->id,$subsubcategoryIds))
                                                <li class="child"><a href="{{ route('products.subsubcategory', $subsubcategory->slug) }}">{{  __($subsubcategory->name) }}</a></li>
                                                @endif    
                                            @endforeach
                                        @endif

                                        @if(isset($subsubcategory_id))
                                            <li class="active"><a href="{{ route('products') }}">{{ translate('All Categories')}}</a></li>
                                            <li class="active"><a href="{{ route('products.category', \App\SubsubCategory::find($subsubcategory_id)->subcategory->category->slug) }}">{{  translate(\App\SubSubCategory::find($subsubcategory_id)->subcategory->category->name) }}</a></li>
                                            <li class="active"><a href="{{ route('products.subcategory', \App\SubsubCategory::find($subsubcategory_id)->subcategory->slug) }}">{{  translate(\App\SubsubCategory::find($subsubcategory_id)->subcategory->name) }}</a></li>

                                            <?php 
                                            $test = \App\SubsubCategory::find($subsubcategory_id)->id;
                                            ?> 
                                            @foreach (\App\SubCategory::find(\App\SubsubCategory::find($subsubcategory_id)->sub_category_id)->subsubcategories as $key3 => $subsubcategory)
                                            
                                                 @if($test == $subsubcategory->id)
                                                    @if(in_array($subsubcategory->id,$subsubcategoryIds))
                                                <li class="test"><a href="{{ route('products.subsubcategory', $subsubcategory->slug) }}">{{  __($subsubcategory->name) }}</a></li>
                                                    @endif
                                                @else
                                                    @if(in_array($subsubcategory->id,$subsubcategoryIds))
                                                <li class="child"><a href="{{ route('products.subsubcategory', $subsubcategory->slug) }}">{{  __($subsubcategory->name) }}</a></li>
                                                    @endif
                                                @endif
                                            @endforeach
                                            <!-- <li class="current"><a href="{{ route('products.subsubcategory', \App\SubsubCategory::find($subsubcategory_id)->slug) }}">{{  translate(\App\SubsubCategory::find($subsubcategory_id)->name) }}</a></li> -->
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white sidebar-box mb-3">
                            <div class="box-title text-center">
                                {{ translate('Price range')}}
                            </div>
                            <div class="box-content">
                                <div class="range-slider-wrapper mt-3">
                                    <!-- Range slider container -->
                                    @php
                                    $getProductsOnCat;
                                      if(!empty($products[0])){
                                          $getProductsOnCat = \App\Product::where('category_id',$products[0]->category_id)->get();
                                      }else{
                                          $getProductsOnCat = array();
                                      }

                                    @endphp

                                    <div
                                        id="input-slider-range"
                                        data-range-value-min="@if(count($getProductsOnCat) < 1) 0 @else {{ @$getProductsOnCat->min('unit_price') }} @endif"
                                        
                                        data-range-value-max="@if(count($getProductsOnCat) < 1) 0 @else {{ @$getProductsOnCat->max('unit_price') }} @endif"></div>

                                    <!-- Range slider values -->
                                    <div class="row">
                                        <div class="col-6">
                                            <span class="range-slider-value value-low"
                                                @if (isset($min_price))
                                                    data-range-value-low="{{ $min_price }}"
                                                @elseif(!empty($getProductsOnCat) && $getProductsOnCat->min('unit_price') > 0)
                                                    data-range-value-low="{{ $getProductsOnCat->min('unit_price') }}"
                                                @else
                                                    data-range-value-low="0"
                                                @endif
                                                id="input-slider-range-value-low">
                                        </div>

                                        <div class="col-6 text-right">
                                            <span class="range-slider-value value-high"
                                                @if (isset($max_price))
                                                    data-range-value-high="{{ $max_price }}"
                                                @elseif(!empty($getProductsOnCat) && $getProductsOnCat->max('unit_price') > 0)
                                                    data-range-value-high="{{ $getProductsOnCat->max('unit_price') }}"
                                                @else
                                                    data-range-value-high="0"
                                                @endif
                                                id="input-slider-range-value-high">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(!empty($all_colors))

                        <div class="bg-white sidebar-box mb-3">
                            <div class="box-title text-center">
                                {{ translate('Filter by color')}}
                            </div>
                            <div class="box-content">
                                <!-- Filter by color -->
                                <ul class="list-inline checkbox-color checkbox-color-circle mb-0">
                                    @foreach ($all_colors as $key => $color)
                                        <li>
                                            <input type="radio" id="color-{{ $key }}" name="color" value="{{ $color }}" @if(isset($selected_color) && $selected_color == $color) checked @endif onchange="filter()">
                                            <label style="background: {{ $color }};" for="color-{{ $key }}" data-toggle="tooltip" data-original-title="{{ \App\Color::where('code', $color)->first()->name }}"></label>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        @endif

                        @foreach ($attributes as $key => $attribute)
                            @if (\App\Attribute::find($attribute['id']) != null && \App\Attribute::find($attribute['id'])->name != "Number")
                                <div class="bg-white sidebar-box scroll-f mb-3">
                                    <div class="box-title text-center">
                                        Filter by {{ \App\Attribute::find($attribute['id'])->name }}
                                    </div>
                                    <div class="box-content">
                                        <!-- Filter by others -->
                                        <div class="filter-checkbox">
                                            @if(array_key_exists('values', $attribute))
                                                @foreach ($attribute['values'] as $key => $value)
                                                    @php
                                                        $flag = false;
                                                        if(isset($selected_attributes)){
                                                            foreach ($selected_attributes as $key => $selected_attribute) {
                                                                if($selected_attribute['id'] == $attribute['id']){
                                                                    if(in_array($value, $selected_attribute['values'])){
                                                                        $flag = true;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="checkbox">
                                                        <input type="checkbox" id="attribute_{{ $attribute['id'] }}_value_{{ $value }}" name="attribute_{{ $attribute['id'] }}[]" value="{{ $value }}" @if ($flag) checked @endif onchange="filter()">
                                                        <label for="attribute_{{ $attribute['id'] }}_value_{{ $value }}">{{ $value }}</label>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        <a href="{{ $link }}" class="btn btn-styled btn-block btn-base-4">Clear all filters</a> 
                        {{-- <button type="submit" class="btn btn-styled btn-block btn-base-4">Apply filter</button> --}}
                    </div>
                </div>
                <div class="col-xl-9">
                    <!-- <div class="bg-white"> -->
                        @isset($category_id)
                            <input type="hidden" name="category" value="{{ \App\Category::find($category_id)->slug }}">
                        @endisset
                        @isset($subcategory_id)
                            <input type="hidden" name="subcategory" value="{{ \App\SubCategory::find($subcategory_id)->slug }}">
                        @endisset
                        @isset($subsubcategory_id)
                            <input type="hidden" name="subsubcategory" value="{{ \App\SubSubCategory::find($subsubcategory_id)->slug }}">
                        @endisset

                        <div class="sort-by-bar row no-gutters bg-white mb-3 px-3 pt-2">
                            <div class="col-xl-4 d-flex d-xl-block justify-content-between align-items-end ">
                                <div class="sort-by-box flex-grow-1">
                                    <div class="form-group">
                                        <label>{{ translate('Search')}}</label>
                                        <div class="search-widget">
                                            <input class="form-control input-lg" type="text" name="q" placeholder="{{ translate('Search products')}}" @isset($query) value="{{ $query }}" @endisset>
                                            <button type="submit" class="btn-inner">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-xl-none ml-3 form-group">
                                    <button type="button" class="btn p-1 btn-sm" id="side-filter">
                                        <i class="la la-filter la-2x"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-xl-7 offset-xl-1">
                                <div class="row no-gutters">
                                    <div class="col-6">
                                        <div class="sort-by-box px-1">
                                            <div class="form-group">
                                                <label>{{ translate('Sort by')}}</label>
                                                <select class="form-control sortSelect" data-minimum-results-for-search="Infinity" name="sort_by" onchange="filter()">
                                                    <option value="1" @isset($sort_by) @if ($sort_by == '1') selected @endif @endisset>{{ translate('Newest')}}</option>
                                                    {{-- <option value="2" @isset($sort_by) @if ($sort_by == '2') selected @endif @endisset>{{ translate('Oldest')}}</option> --}}
                                                    <option value="3" @isset($sort_by) @if ($sort_by == '3') selected @endif @endisset>{{ translate('Price low to high')}}</option>
                                                    <option value="4" @isset($sort_by) @if ($sort_by == '4') selected @endif @endisset>{{ translate('Price high to low')}}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                        <?php
                                            $get_brand_id = array();
                                            foreach($products as $k=>$v){
                                                array_push($get_brand_id,$v['brand_id']);
                                            }
                                            if(!empty($getBrandIds)){
                                                $product_brand = \App\Brand::whereIn('id',$getBrandIds)->get();
                                            }elseif(!empty($get_brand_id)){
                                                $product_brand = \App\Brand::whereIn('id',$get_brand_id)->get();
                                                
                                            }else{
                                                $product_brand = array();
                                            }

                                        ?>
                                    <div class="col-6">
                                        <div class="sort-by-box px-1">
                                            <div class="form-group">
                                                <label>{{ translate('Brands')}}</label>
                                                <select class="form-control sortSelect" data-placeholder="{{ translate('All Brands')}}" name="brand" onchange="filter()">
                                                    <option value="">{{ translate('All Brands')}}</option>
                                                    @foreach ($product_brand as $brand)
                                                        <option value="{{ $brand->slug }}" @isset($brand_id) @if ($brand_id == $brand->id) selected @endif @endisset>{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- <div class="col-4">
                                        <div class="sort-by-box px-1">
                                            <div class="form-group">
                                                <label>{{ translate('Sellers')}}</label>
                                                <select class="form-control sortSelect" data-placeholder="{{ translate('All Sellers')}}" name="seller_id" onchange="filter()">
                                                    <option value="">{{ translate('All Sellers')}}</option>
                                                    @foreach (\App\Seller::all() as $key => $seller)
                                                        @if ($seller->user != null && $seller->user->shop != null)
                                                            <option value="{{ $seller->id }}" @isset($seller_id) @if ($seller_id == $seller->id) selected @endif @endisset>{{ $seller->user->shop->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="min_price" value="">
                        <input type="hidden" name="max_price" value="">
                        <!-- <hr class=""> -->
                        <div class="products-box-bar p-3 bg-white product_listing">
                            <div class="row sm-no-gutters gutters-5">
                                @if(count($products)==0)
                        <h6 style="color:grey;">Did You Mean <a href="{{ route('suggestion.search', $dym) }}">{{ $dym }}</a>?</h6>
                        @endif
                                @foreach ($products as $key => $product)
                                    <div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-6">
                                    	@if(Session::has('referal_discount'))
                                            
                                                <div class="discount" >
                                            		<span>{{ round(peer_discounted_percentage($product->id,$shortId),2) }}% Off</span>
                                            		<img  src="{{ static_asset('frontend/images/discount.png') }}" >
                                            	</div>
                                        @endif
                                        <div class="product-box-2 bg-white alt-box my-md-2">
                                        		
                                            <div class="position-relative overflow-hidden">
                                                <a href="{{ route('product', $product->slug) }}" class="d-block product-image   text-center" tabindex="0">
                                                    <img class="img-fit lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" data-src="{{ my_asset($product->thumbnail_img) }}" alt="{{  __($product->name) }}">
                                                    <!-- <img class="img-fit lazyload" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" alt="{{  __($product->name) }}"> -->
                                                </a>
                                            </div><!-- @php print_r(Session::get('referal_discount')); @endphp -->
                                           
                                            <div class="p-md-3 p-2">
                                                <div class="price-box">
                                                    @if(home_base_price($product->id) != home_discounted_base_price($product->id))
                                                       <!--  <del class="old-product-price strong-400">{{ home_base_price($product->id) }}</del> -->
                                                    @endif

                                            @if(Session::has('referal_discount'))
                                                @if(!empty($shortId))
                                                        @if(getShortId($shortId,$product->id)->selling_price !=0)
                                                            <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($product->id,$shortId)) }}</span> 
                                                            <del class="old-product-price strong-400">{{ format_price(@getShortId($shortId,$product->id)->selling_price) }}</del>
                                                        @else
                                                        
                                                            <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($product->id,$shortId)) }}</span>
                                                            <del class="old-product-price strong-400">


                                                                {{ format_price(@$product->stocks[0]->price) }}</del>
                                                        @endif

                                                @else
                                                            <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($product->id)) }}</span>
                                                            <del class="old-product-price strong-400">{{ format_price(@$product->stocks[0]->price) }}</del>
                                                @endif

                                            @else    
                                                @if(!empty($shortId))
                                                      @if(getShortId($shortId,$product->id)->selling_price !=0)
                                                        <span class="product-price strong-600">{{ format_price(@getShortId($shortId,$product->id)->selling_price) }}</span>
                                                      @else
                                                      <span class="product-price strong-600">{{ format_price(@$product->stocks[0]->price) }}</span>
                                                      @endif

                                                @else
                                                    @if($product->stocks[0]->price == 0)
                                                    <span class="product-price strong-600">{{ format_price(@$product->purchase_price) }}</span>
                                                    @else
                                                    <span class="product-price strong-600">{{ format_price(@$product->stocks[0]->price) }}</span>
                                                    @endif
                                                  
                                                  @endif
                                                @endif


                                                   <!--  <span class="product-price strong-600">
                                                        {{ home_price($product->id)  }}
                                                         {{ home_discounted_base_price($product->id) }} 
                                                    </span> -->
                                                </div>
                                                <!-- <div class="star-rating star-rating-sm mt-1">
                                                    {{ renderStarRating($product->rating) }}
                                                </div> -->
                                                <?php
                                                    if(!empty($shortId)){
                                                        $maxquantity_bysh = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('product_id',$product->id)->where('published',1)->first('max_purchaseprice');
                                                        $maxquantity = $maxquantity_bysh['max_purchaseprice'];
                                                    }else{
                                                        $maxquantity = 0;
                                                    }
                                                    if(!empty($product->max_purchase_qty)){
                                                        $adminmaxquantity = $product->max_purchase_qty;
                                                    }else{
                                                        $adminmaxquantity = 0;
                                                    } 
                                                ?>
                                                <span class="quant">
                                                @if ($product->choice_options != null)
                                                    @foreach (json_decode($product->choice_options) as $key => $choice)
                                                        @foreach ($choice->values as $key => $value)
                                                            {{ strtolower($value) }}
                                                        @endforeach   
                                                    @endforeach
                                                @endif
                                                </span>
                                                <h2 class="product-title p-0">
                                                    <a href="{{ route('product', $product->slug) }}" class=" text-truncate">{{  __($product->name) }}</a>
                                                </h2>
                                                <input type="hidden" id="stock_qty_{{$product->id}}" name="stock_qty_{{$product->id}}" value="@if(!empty(Cookie::has('pincode'))){{ mapped_product_stock($shortId->sorting_hub_id,$product->id)}} @else 0 @endif">
                                                <input type="hidden" id="limit_qty_{{$product->id}}" name="limit_qty_{{$product->id}}" value="@if($maxquantity!=0){{ $maxquantity}} @else {{ $adminmaxquantity }} @endif">
                                                
                                                <div id= "cart_loader{{ $product->id }}"></div>

                                                   <div class="quantity buttons_added new" id="button-group{{ $product->id }}">
                                                    
                                                    <input type="button" value="Add" class="quant_add_btn plus" id="btn_add{{$product->id}}" onclick="addToCartF('{{$product->id}}')" @if(get_product_cart_qty($product->id)>0) style="display:none" @endif>
                                                    <input type="button" value="-" class="minus" onclick="add_qty('{{ $product->id }}'),updateCartF('{{ $product->id }}')">
                                                    <input type="number" step="1" min="0" max="@if(!empty($product->max_purchase_qty))
                                                    {{ $product->max_purchase_qty}} @endif" onkeypress="add_qty(this.id),addToCartF('{{$product->id}}')" id="pamount_{{ $product->id}}" name="quantity{{$product->id}}" value="{{ get_product_cart_qty($product->id) }}" class="input-text qty text" size="4" pattern="" inputmode="" readonly>
                                                    <input type="button" value="+" class="plus" id="plus-button{{ $product->id }}" onclick="add_qty('{{ $product->id }}'),addToCartF('{{$product->id}}')">
                                                    <div class="clearfix"></div>
                                                    
                                                </div> 
                                                @if (\App\Addon::where('unique_identifier', 'club_point')->first() != null && \App\Addon::where('unique_identifier', 'club_point')->first()->activated)
                                                    <div class="club-point mt-2 bg-soft-base-1 border-light-base-1 border">
                                                        {{  translate('Club Point') }}:
                                                        <span class="strong-700 float-right">{{ $product->earn_point }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                
                                @endforeach
                            </div>
                        </div>
                        <div class="products-pagination bg-white p-3">
                            <nav aria-label="Center aligned pagination">
                                <ul class="pagination justify-content-center">
                                    {{ $products->links() }}
                                </ul>
                            </nav>
                        </div>

                    <!-- </div> -->
                </div>
            </div>
            </form>
            @foreach ($products as $key => $product)
            <form id="option-choice-form_{{$product->id}}">
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
                         </form>
                         @endforeach
        </div>
    </section>

@endsection

@section('script')
    <script type="text/javascript">
        function filter(){
            $('#search-form').submit();
        }
        function rangefilter(arg){
            $('input[name=min_price]').val(arg[0]);
            $('input[name=max_price]').val(arg[1]);
            filter();
        }
    </script>

 <script type="text/javascript">

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

         $('.quant_add_btn, .quantity.buttons_added.new .plus').click(function(){
            $(this).parent().find('.quant_add_btn').hide();
         });
         $('.quantity.buttons_added.new  .minus').click(function(){
               var currentVal = parseInt($(this).parent().find('input[type=number]').val());
            if( currentVal==1){
                parseInt($(this).parent().find('input[type=number]').val('0'));
                //$(this).parent().find('input[type=number]').val("0");
                $(this).parent().find('.quant_add_btn').show();

            }
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
        if(stock_qty<=0){
                   
                    showFrontendAlert('danger','Product out of stock');
                    $("#btn_add"+id).next().attr('disabled','disabled');
                    $("#plus-button"+id).attr('disabled','disabled');
                    $("#"+id).val(1);
                    //$("#pamount_"+id).val(1);
                    return false;
                }
            var limit_qty = $("#limit_qty_"+id).val();
            var pr_qty = $("#pamount_"+id).val();
            var pr_qty = parseInt(pr_qty)+1;
            
            if(limit_qty < pr_qty){
                showFrontendAlert('danger','Maximum Limit has been reached');
                return false;

            }
        if(checkAddToCartValidity()) {
            var data = $('#option-choice-form_'+id).serializeArray();
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
            var data = $('#option-choice-form_'+id).serializeArray();
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
