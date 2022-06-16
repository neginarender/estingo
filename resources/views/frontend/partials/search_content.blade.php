@php
$shortId = "";
if(!empty(Cookie::get('pincode'))){
$pincode = Cookie::get('pincode');

$distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->where('status',1)->pluck('id')->all();
$shortId = \App\MappingProduct::whereIn('distributor_id',$distributorId)->first('sorting_hub_id');

}
@endphp
<div class="keyword">
    @if (sizeof($keywords) > 0)
        <div class="title">{{translate('Popular Suggestions')}}</div>
        <ul>
            @foreach ($keywords as $key => $keyword)
                <li><a href="{{ route('suggestion.search', $keyword) }}">{{ $keyword }}</a></li>
            @endforeach
        </ul>
    @endif

           @if(sizeof($keywords)==0 && count($products)==0 && $dym)
     
            <p style="color:grey;">Did You Mean <a href="{{ route('suggestion.search', $dym) }}">{{ $dym }}</a>?</p>
        
            @endif
</div>
<!-- <div class="category">
    @if (count($subsubcategories) > 0)
        <div class="title">{{translate('Category Suggestions')}}</div>
        <ul>
            @foreach ($subsubcategories as $key => $subsubcategory)
                <li><a href="{{ route('products.subsubcategory', $subsubcategory->slug) }}">{{ __($subsubcategory->name) }}</a></li>
            @endforeach
        </ul>
    @endif
</div> -->
<div class="product">
    @if (count($products) > 0)
        <div class="title">{{translate('Products')}}</div>
        <ul>
            @foreach ($products as $key => $product)
                <li>
                    <a href="{{ route('product', $product->slug) }}">
                        <div class="d-flex search-product align-items-center">
                            <div class="image" style="background-image:url('{{ my_asset($product->thumbnail_img) }}');">
                            </div>
                            <div class="w-100 overflow--hidden">
                                <div class="product-name text-truncate">
                                    {{ __($product->name) }}
                                </div>
                                <div class="clearfix">
                                    <div class="price-box float-left">
                                        
                                    @if(Session::has('referal_discount'))

                                    <!-- <span class="product-price strong-600">{{ single_price(peer_discounted_base_price($product->id,$shortId)) }}</span> -->
                                    
                                    <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($product->id,$shortId)) }}</span>

                                    <del class="old-product-price strong-400">{{ home_price($product->id,$shortId) }}</del>
                                    @else
                                    <span class="product-price strong-600">{{ home_price($product->id,$shortId) }}</span>
                                    @endif
                                                                            
                                        
                                        <!-- @if(home_base_price($product->id) != home_discounted_base_price($product->id))
                                            <del class="old-product-price strong-400">{{ home_base_price($product->id) }}</del>
                                        @endif
                                         <span class="product-price strong-600">{{ home_discounted_base_price($product->id) }}</span> 
                                        <span class="product-price strong-600">{{ format_price($product->stocks()->first()->price) }}</span> -->
                                    </div>
                                    {{-- <div class="stock-box float-right">
                                        @php
                                            $qty = 0;
                                            foreach (json_decode($product->variations) as $key => $variation) {
                                                $qty += $variation->qty;
                                            }
                                        @endphp
                                        @if(count(json_decode($product->variations, true)) >= 1)
                                            @if ($qty > 0)
                                                <span class="badge badge-pill bg-green">{{translate('In stock')}}</span>
                                            @else
                                                <span class="badge badge badge-pill bg-red">{{translate('Out of stock')}}</span>
                                            @endif
                                        @endif
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@if(\App\BusinessSetting::where('type', 'vendor_system_activation')->first()->value == 1)
    <div class="product">
        @if (count($shops) > 0)
            <div class="title">{{translate('Shops')}}</div>
            <ul>
                @foreach ($shops as $key => $shop)
                    <li>
                        <a href="{{ route('shop.visit', $shop->slug) }}">
                            <div class="d-flex search-product align-items-center">
                                <div class="image" style="background-image:url('{{ my_asset($shop->logo) }}');">
                                </div>
                                <div class="w-100 overflow--hidden ">
                                    <div class="product-name text-truncate heading-6 strong-600">
                                        {{ $shop->name }}

                                        <div class="stock-box d-inline-block">
                                            @if($shop->user->seller->verification_status == 1)
                                                <span class="ml-2"><i class="fa fa-check-circle" style="color:green"></i></span>
                                            @else
                                                <span class="ml-2"><i class="fa fa-times-circle" style="color:red"></i></span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="price-box alpha-6">
                                        {{ $shop->address }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
