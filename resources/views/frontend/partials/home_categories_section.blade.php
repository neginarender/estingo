@php 
$sortinghubid = "";
$self = 0;
if(Auth::check()){
    if(Auth::user()->user_type=='partner' && Auth::user()->peer_partner==1){
        $self = 1;
    }
}
if(Cookie::has('sid')){
    $sortinghubid = decrypt(Cookie::get('sid'));
}

@endphp
@if(!empty($categories))
@foreach ($categories as $key => $category)
    @if ($category != null)
        <section class="pt-0 bg-white pb-0" >
            <div class="container">
                <div class="row">
                    <div class="col-md-12  "> 
                        <div class="sec_title  ">
                             <a href="{{ route('products.category', $category->slug) }}" class="btn btn-success btn-sm float-right mt-1">Explore All</a>
                             <h4>{{ $category->name }}</h4>
                        </div> 
                    </div>
                </div>
                <div class="caorusel-box arrow-round gutters-5 mt-2 mb-5">
                    <div class="slick-carousel" data-slick-items="6" data-slick-xl-items="6" data-slick-lg-items="6"  data-slick-md-items="3" data-slick-sm-items="2" data-slick-xs-items="2">
                    
                        @foreach ($category->products as $key => $product)
                         <form class="height-auto" id="option-choice-form_{{$product->product_id}}">
                                <input type="hidden" id="option_form{{ $product->product_id }}" value="" />
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">      
                                <input type="hidden" name="id" value="{{ $product->product_id }}">
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
                                            <a href="@if(property_exists($product, 'slug')) {{ route('product', $product->slug) }} @else # @endif" class="d-block">
                                                <img class="img-fit lazyload mx-auto" src="{{Storage::disk('s3')->url('frontend/images/placeholder.jpg')}}" data-src="{{Storage::disk('s3')->url($product->thumbnail_image)}}" alt="{{ __($product->name) }}">
                                            </a>
                                        </div>
                                        <div class="p-md-2 p-2 prod_info">
                                            @if(Session::has('referal_discount'))
                                            
                                                <div class="discount homep" >
                                                    <span>{{ (int)$product->discount_percentage }}% Off</span>
                                                    <img  src="{{ static_asset('frontend/images/discount.png') }}" >
                                                </div>
                                             @endif    
                                            <h2 class="product-title p-0">
                                                <span>{{ ucwords($category->name) }}</span>
                                                <a href="@if(property_exists($product, 'slug')) {{ route('product', $product->slug) }} @else # @endif" class=" text-truncate">{{ __($product->name) }}</a>
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
                                             @php 
                                              
                                                $cprice = calculatePrice($product->product_id,$sortinghubid,$self);

                                                //$price = $product->stock_price;
                                                $price = $cprice['selling_price'];
                                                //$del_price = $product['stock_price'];
                                                $del_price = $cprice['MRP'];
                                                if(!Session::has('referal_discount')){
                                                    $price = $del_price;
                                                }
                                            @endphp
                                            <span class="product-price strong-600">{{ single_price($price) }}</span>
                                            @if(Session::has('referal_discount'))
                                            <del class="old-product-price strong-400">{{ single_price($del_price) }}</</del>
                                            @endif

                                                <input type="hidden" id="stock_qty_{{$product->product_id}}" name="stock_qty_{{$product->product_id}}" value="{{ $product->quantity }}">
                                                <input type="hidden" id="limit_qty_{{$product->product_id}}" name="limit_qty_{{$product->product_id}}" value="{{ $product->max_purchase_qty }}">
                                                <div id="cart_loader{{$product->product_id}}"></div>
                                                   <div class="quantity buttons_added new" id="button-group{{ $product->product_id }}">
                                                    
                                                   @php
                                                    $class = "";
                                                    if(get_product_cart_qty($product->product_id)>0)
                                                    {
                                                        $class = "display-none";
                                                    }
                                                    @endphp
                                                    <input type="button" value="Add" class="quant_add_btn plus {{$class}}" id="btn_add{{ $product->product_id }}" onclick="addToCartF('{{$product->product_id}}')">
                                                    <input type="button" value="-" class="minus" onclick="add_qty('{{ $product->product_id }}'),updateCartF('{{ $product->product_id }}')">
                                                    <input type="number" step="1" min="0" max="@if(!empty($product->max_purchase_qty))
                                                    {{ $product->max_purchase_qty}} @endif" onkeypress="add_qty(this.id),addToCartF('{{$product->product_id}}')" id="pamount_{{ $product->product_id}}" name="quantity{{$product->product_id}}" value="{{ get_product_cart_qty($product->product_id) }}" class="input-text qty text" size="4" readonly>
                                                    <input type="button" value="+" class="plus" id="plus-button{{ $product->product_id }}" onclick="add_qty('{{ $product->product_id }}'),addToCartF('{{$product->product_id}}')" >
                                                    <div class="clearfix"></div>
                                                    
                                                </div>                                       
                                            </div>                            
                                        </div>
                                    </div>
                                </div>
                            </div> 
                        </form>
                        @endforeach                
                    </div>
                </div>
            </div>
        </section>
    @endif
@endforeach
@else
<span></span>
@endif

 <script type="text/javascript">
         $('.quant_add_btn, .quantity.buttons_added.new  .plus').click(function(){
            $(this).parent().find('.quant_add_btn').hide();
         });
         $('.quantity.buttons_added.new  .minus').click(function(){
               var currentVal = parseInt($(this).parent().find('input[type=number]').val());
            if( currentVal==1){
                parseInt($(this).parent().find('input[type=number]').val('0'));
                $(this).parent().find('.quant_add_btn').show();
                $(this).parent().find('.quant_add_btn').removeClass('display-none');

            }
         });
    </script>