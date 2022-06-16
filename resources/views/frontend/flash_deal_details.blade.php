@extends('frontend.layouts.app')
 @php
 $categories ="";
 $shortId = "";
 if(!empty(Cookie::has('pincode'))){ 
        $pincode = Cookie::get('pincode');

        $distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->where('status',1)->pluck('id')->all();
        $shortId = \App\MappingProduct::whereIn('distributor_id',$distributorId)->first('sorting_hub_id');
        //print_r($shortId);
        if(!empty($distributorId)){
            $productIds = \App\MappingProduct::whereIn('distributor_id',$distributorId)->where('published',1)->where('flash_deal',0)->pluck('product_id')->all();
            $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
            $categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->whereIn('id',$categoryIds)->get();

        }


    }else{
        $categoryIds = array();
        $productIds = array();
        $categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->get();
    }  
@endphp
@section('content')
    @if($flash_deal->status == 1 && strtotime(date('d-m-Y')) <= $flash_deal->end_date)
    <div style="background-color:{{ $flash_deal->background_color }}">
        <section class="text-center">
            <img src="{{ my_asset($flash_deal->banner) }}" alt="{{ $flash_deal->title }}" class="img-fit w-100">
        </section>
        <section class="pb-4">
            <div class="container">
                <div class="text-center my-4 text-{{ $flash_deal->text_color }}">
                    <h1 class="h3">{{ $flash_deal->title }}</h1>
                    <div class="countdown countdown-sm countdown--style-1" data-countdown-date="{{ date('m/d/Y', $flash_deal->end_date) }}" data-countdown-label="show"></div>
                </div>
                <div class="gutters-5 row">
                    @foreach ($flash_deal->flash_deal_products as $key => $flash_deal_product)
                        @php
                            $product = \App\Product::find($flash_deal_product->product_id);
                        @endphp
                        @if ($product->published != 0)
                            <div class="col-xl-2 col-lg-3 col-md-4 col-6">
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
                                <div class="product-card-2 card card-product shop-cards shop-tech mb-2">
                                    <div class="card-body p-0">

                                        <div class="card-image">
                                            <a href="{{ route('product', $product->slug) }}" class="d-block text-center" >
                                                <img class="img-fit lazyload" src="{{ static_asset('frontend/images/placeholder.jpg') }}" data-src="{{ my_asset($product->thumbnail_img) }}" alt="{{  __($product->name) }}">
                                            </a>
                                        </div>

                                        <div class="p-3 prod_info">
                                             @if(Session::has('referal_discount'))
                                            @php
                                                $referral_price = peer_discounted_newbase_price($product->id,$shortId);
                                                $main_price = main_price_percent($product->id,$shortId);

                                                $difference = ($main_price - $referral_price)/$main_price;
                                                $percent_price = $difference*100;
                                            @endphp
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
                                                <!-- <span class="quant">{{ $product->unit }}</span> -->
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

                                                    <input type="hidden" id="stock_qty_{{$product->id}}" name="stock_qty_{{$product->id}}" value="@if(!empty(Cookie::has('pincode'))){{ mapped_product_stock($shortId->sorting_hub_id,$product->id)}} @else 0 @endif">
                                                <input type="hidden" id="limit_qty_{{$product->id}}" name="limit_qty_{{$product->id}}" value="@if(!empty($product->max_purchase_qty)){{ $product->max_purchase_qty}} @else 0 @endif">
                                                <div class="spinner-border text-success" id= "cart_loader{{ $product->id }}" style="display:none;" role="status">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                                   <div class="quantity buttons_added new" id="button-group{{ $product->id }}">
                                                    
                                                      <input type="button" value="Add" class="quant_add_btn plus" id="btn_add{{ $product->id }}" onclick="addToCartF('{{$product->id}}')" @if(get_product_cart_qty($product->id)>0) style="display:none" @endif>
                                                    <input type="button" value="-" class="minus" onclick="add_qty('{{ $product->id }}'),updateCartF('{{ $product->id }}')">
                                                    <input type="number" step="1" min="0" max="@if(!empty($product->max_purchase_qty))
                                                    {{ $product->max_purchase_qty}} @endif" onkeypress="add_qty(this.id),addToCartF('{{$product->id}}')" id="pamount_{{ $product->id}}" name="quantity{{$product->id}}" value="{{ get_product_cart_qty($product->id) }}" title="Qty" class="input-text qty text" size="4" pattern="" inputmode="">
                                                    <input type="button" value="+" class="plus" id="plus-button{{ $product->id }}" onclick="add_qty('{{ $product->id }}'),addToCartF('{{$product->id}}')">
                                                    <div class="clearfix"></div>
                                                    
                                                </div>
                                            </div>
                                            <!-- <h2 class="product-title p-0 mt-2">
                                                <a href="{{ route('product', $product->slug) }}" class="text-truncate">{{  __($product->name) }}</a>
                                            </h2> -->
                                        </div>
                                    </div>
                                </div>
                            </form>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    </div>
    @else
        <div style="background-color:{{ $flash_deal->background_color }}">
            <section class="text-center pt-3">
                <div class="container ">
                    <img src="{{ my_asset($flash_deal->banner) }}" alt="{{ $flash_deal->title }}" class="img-fit">
                </div>
            </section>
            <section class="pb-4">
                <div class="container">
                    <div class="text-center text-{{ $flash_deal->text_color }}">
                        <h1 class="h3 my-4">{{ $flash_deal->title }}</h1>
                        <p class="h4">{{  translate('This offer has been expired.') }}</p>
                    </div>
                </div>
            </section>
        </div>
    @endif
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

         $('.quant_add_btn, .quantity.buttons_added.new  .plus').click(function(){
            $(this).parent().find('.quant_add_btn').hide();
         });
         $('.quantity.buttons_added.new  .minus').click(function(){
               var currentVal = parseInt($(this).parent().find('input[type=number]').val());
            if( currentVal==1){
                parseInt($(this).parent().find('input[type=number]').val('0'));
                // $(this).parent().find('input[type=number]').val("0");
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
                    $(".quant_add_btn").show();
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
