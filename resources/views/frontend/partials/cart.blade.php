@php
$shortId = "";
if(!empty(Cookie::get('pincode'))){ 
            $pincode = Cookie::get('pincode');
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        }
@endphp
                                    <a href="" class="nav-box-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                           <img src="{{ static_asset('frontend/images/homepage/header/cart.png') }}" alt="{{ env('APP_NAME') }}">
                                        <span class="nav-box-text  d-xl-inline-block">
                                        @if(Session::has('cart'))
                                            <span class="nav-box-number">{{ count(Session::get('cart'))}}</span>
                                        @else
                                            <span class="nav-box-number">0 items</span>
                                        @endif
                                        </span>
                                       <span class="nav-box-text  d-xl-inline-block"> My Cart </span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-right px-0 ">
                                        <li>
                                            <div class="dropdown-cart px-0">
                                                @if(Session::has('cart'))
                                                    @if(count($cart = Session::get('cart')) > 0)
                                                        <div class="dc-header">
                                                            <h3 class="heading heading-6 strong-700">{{translate('Cart Items')}}</h3>
                                                        </div>
                                                        <div class="dropdown-cart-items c-scrollbar">
                                                            @php
                                                                $total = 0;
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
                                                                                <img src="{{ static_asset('frontend/images/placeholder.jpg') }}" data-src="{{ my_asset($product->thumbnail_img) }}" class="img-fluid lazyload" alt="{{ __($product->name) }}">
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
                                                                            @endif                                                                         </div>
                                                                        <div class="dc-actions">
                                                                            <button onclick="removeFromCart({{ $key }},{{$product->id}})">
                                                                                <i class="la la-close"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="dc-item py-3">
                                                            <span class="subtotal-text">{{translate('Subtotal')}}</span>
                                                            <span class="subtotal-amount">{{ single_price($total) }}</span>
                                                        </div>
                                                        <div class="py-2 text-center dc-btn">
                                                            <ul class="inline-links inline-links--style-3">
                                                                <li class="px-1">
                                                                    <a href="{{ route('cart') }}" class="link link--style-1 text-capitalize btn btn-base-1 px-3 py-1">
                                                                        <i class="la la-shopping-cart"></i> {{translate('View cart')}}
                                                                    </a>
                                                                </li>
                                                                @if (Auth::check())
                                                                <li class="px-1">
                                                                    <a href="{{ route('checkout.shipping_info') }}" class="link link--style-1 text-capitalize btn btn-base-1 px-3 py-1 light-text">
                                                                        <i class="la la-mail-forward"></i> {{translate('Checkout')}}
                                                                    </a>
                                                                </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    @else
                                                        <div class="dc-header">
                                                            <h3 class="heading heading-6 strong-700">{{translate('Your Cart is empty')}}</h3>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="dc-header">
                                                        <h3 class="heading heading-6 strong-700">{{translate('Your Cart is empty')}}</h3>
                                                    </div>
                                                @endif
                                            </div>
                                        </li>
                                    </ul>
                              