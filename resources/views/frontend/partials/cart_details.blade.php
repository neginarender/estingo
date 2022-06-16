@php
$shortId = "";
if(!empty(Cookie::get('pincode'))){ 
            $pincode = Cookie::get('pincode');
            $distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->where('status',1)->pluck('id')->all();
            $shortId = \App\MappingProduct::whereIn('distributor_id',$distributorId)->first('sorting_hub_id');
        }
@endphp
<div class="container">
    <div class="row cols-xs-space cols-sm-space cols-md-space">
        <div class="col-xl-8">
            <!-- <form class="form-default bg-white p-4" data-toggle="validator" role="form"> -->
            <div class="form-default bg-white p-4" style="overflow:scroll;">
                <div class="">
                    <div class="">
                        <table class="table-cart border-bottom">
                            <thead>
                                <tr>
                                    <th class="product-select"><input type="checkbox" id="selectall" onclick="selectAll(this)"> Select all</input></th>
                                    <th class="product-image"></th>
                                    <th class="product-name">{{ translate('Product')}}</th>
                                    <th class="product-price d-lg-table-cell">{{ translate('Price')}}</th>
                                    <th class="product-quanity d-md-table-cell">{{ translate('Quantity')}}</th>
                                    <th class="product-total">{{ translate('Total')}}</th>
                                    <th class="product-remove"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $total = 0;
                                @endphp
                                @foreach (Session::get('cart') as $key => $cartItem)
                                    @php
                                    $product = \App\Product::find($cartItem['id']);
                                    $total = $total + $cartItem['price']*$cartItem['quantity'];
                                    $product_name_with_choice = $product->name;
                                    $cartItem['max_purchase_qty'] = $product->max_purchase_qty;
                                    if ($cartItem['variant'] != null) {
                                        $product_name_with_choice = $product->name.' - '.$cartItem['variant'];
                                    }
                                    @endphp
                                    <tr class="cart-item">
                                        <td class="product-select">
                                                    <input type="checkbox" class="form-control selectedId" onclick="singleCheck()" name="productsid[]" value="{{$key}}" style="width: 16px;">

                                                </td>
                                        <td class="product-image">
                                            <a href="#" class="mr-3">
                                              <img loading="lazy"  src="{{ my_asset($product->thumbnail_img) }}">
                                                <!-- <img loading="lazy"  src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}">  -->
                                            </a>
                                        </td>

                                        <td class="product-name">
                                            <span class="pr-4 d-block">{{ $product_name_with_choice }}</span>
                                        </td>

                                        <td class="product-price d-lg-table-cell">
                                                    @if(Session::has('referal_discount'))
                                                        @if(!empty($shortId))
                                                        <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($cartItem['id'],$shortId)) }}</span>
                                                        <del class="old-product-price strong-400">{{ single_price($cartItem['price']) }}</del>
                                                        @else
                                                        <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($cartItem['id'])) }}</span>
                                                        <del class="old-product-price strong-400">{{ single_price($cartItem['price']) }}</del>
                                                        @endif
                                                   
                                                    @else    
                                                        <span class="pr-3 d-block">
                                                            {{ single_price($cartItem['price']) }}
                                                        </span>
                                                    @endif
                                                    <!-- <span class="pr-3 d-block">{{ single_price($cartItem['price']) }}</span> -->
                                                </td>

                                        <td class="product-quantity d-md-table-cell">
                                            <div class="input-group input-group--style-2 pr-4" style="width: 130px;">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-number" type="button" data-type="minus" data-field="quantity[{{ $key }}]">
                                                        <i class="la la-minus"></i>
                                                    </button>
                                                </span>
                                                <input type="text" name="quantity[{{ $key }}]" class="form-control h-auto input-number" placeholder="1" value="{{ $cartItem['quantity'] }}" min="1" max="{{ $cartItem['max_purchase_qty'] }}" onchange="updateQuantity({{ $key }}, this)">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-number" type="button" data-type="plus" data-field="quantity[{{ $key }}]">
                                                        <i class="la la-plus"></i>
                                                    </button>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="product-total">

                                                    @if(Session::has('referal_discount'))
                                                    @if(!empty($shortId))
                                                    <span>{{ single_price(peer_discounted_newbase_price($cartItem['id'],$shortId)*$cartItem['quantity']) }}</span>
                                                        <!-- <span>{{ single_price(peer_discounted_base_price($cartItem['id'])*$cartItem['quantity']) }}</span> -->
                                                     @else
                                                     <span>{{ single_price(peer_discounted_newbase_price($cartItem['id'])*$cartItem['quantity']) }}</span>
                                                    
                                                     @endif
                                                    @else    
                                                        <span>{{ single_price($cartItem['price']*$cartItem['quantity']) }}</span>
                                                    @endif
                                                    
                                                </td>
                                        <td class="product-remove">
                                            <a href="#" onclick="removeFromCartView(event, {{ $key }})" class="pl-4">
                                                <i class="la la-trash" style="font-size:25px;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row align-items-center pt-4">
                    <div class="col-6">
                        <a href="{{ route('home') }}" class="link link--style-3">
                            <i class="ion-android-arrow-back"></i>
                            {{ translate('Return to shop')}}
                        </a>
                    </div>
                    <div class="col-6 text-right">
                     
                                <button type="button" class="btn btn-styled btn-base-1 remove_btn" style="display:none;" onclick="removeMultipleProduct()">{{ translate('Remove') }}</button>
                        @if(Auth::check())
                            <a href="{{ route('checkout.shipping_info') }}" class="btn btn-styled btn-base-1">{{ translate('Continue to Shipping')}}</a>
                        @else
                            <button class="btn btn-styled btn-base-1" onclick="showCheckoutModal()">{{ translate('Continue to Shipping')}}</button>
                        @endif
                    </div>
                </div>
            </div>
            <!-- </form> -->
        </div>

        <div class="col-xl-4 ml-lg-auto">
            @include('frontend.partials.cart_summary')
        </div>
    </div>
</div>

@section('script')
<script type="text/javascript">
    //cartQuantityInitialize();
    
</script>

@endsection
