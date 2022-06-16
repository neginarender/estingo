@if(\App\BusinessSetting::where('type', 'classified_product')->first()->value == 0)
    @php
    $customer_products = "";
    $shortId = [];
    $service_available = 1;
    
    if(Cookie::has('sid')){
        $shortId['sorting_hub_id'] = decrypt(Cookie::get('sid'));
    }
    else{
            if(Cookie::has('pincode')){
                $pincode = Cookie::get('pincode');
                $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')
                ->selectRaw('user_id as sorting_hub_id')
                ->first('sorting_hub_id');
            }
            
        }
    if(!empty($shortId)){ 
          
         if(!empty($shortId)){
            $bestSellingIds = \App\MappingProduct::whereIn('product_id',bestSellingProduct($shortId['sorting_hub_id']))->where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->where('flash_deal',0)->pluck('product_id');
            if($shortId['sorting_hub_id']==165){
                $bestSellingIds = [4234,4233,11320,11322,4218,4220];
            }
            $customer_products = \App\Product::with('category')->where('published', '1')->whereIn('id',$bestSellingIds)->get();

        }
        else{
            $service_available = 0;
        }

    }else{
        $customer_products = \App\Product::with('category')->where('published', '1')->take(6)->orderBy('created_at','desc')->get();

    } 
    
         
    @endphp
    
        <section class="pt-4 bg-white pb-4" >
            <div class="container">
        @if($service_available)
            @if(!empty($customer_products))
                <div class="row">
                    <div class="col-md-12  "> 
                        <div class="sec_title  ">
                             <!-- <a href="" class="btn btn-success btn-sm float-right mt-1">All Bestseller</a> -->
                             <h4 class="black">Bestseller</h4>
                        </div> 
                    </div>
                </div>           
                <div class="caorusel-box arrow-round gutters-5 mt-2 mb-5">
                    <div class="slick-carousel" data-slick-items="6" data-slick-xl-items="6" data-slick-lg-items="6"  data-slick-md-items="3" data-slick-sm-items="2" data-slick-xs-items="2"> 
                        @foreach ($customer_products as $key => $customer_product) 
                        <form class="height-auto" id="option-choice-form_{{$customer_product->id}}">
                                <input type="hidden" id="option_form{{ $customer_product->id }}" value="" />
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">      
                                <input type="hidden" name="id" value="{{ $customer_product->id }}">
                                @php 
                                $attr = json_decode($customer_product->choice_options);
                                
                                @endphp
                                @foreach($attr as $key => $choice)
                                    @foreach ($choice->values as $key => $value)
                             <input type="hidden" name="attribute_id_{{$choice->attribute_id}}" value="{{ $value }}">
                                    @endforeach
                             @endforeach
                            <div class="caorusel-card">
                                <div class="product-card-2   shop-cards shop-tech">
                                    <div class="card-body p-0">
                                        <div class="card-image">
                                            <a href="{{ route('product', $customer_product->slug) }}" class="d-block">
                                            <img class="img-fit lazyload mx-auto" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" data-src="{{Storage::disk('s3')->url($customer_product->thumbnail_img)}}" alt="{{ __($customer_product->name) }}">
                                              <!-- <img class="img-fit lazyload mx-auto" src="{{ asset('public/uploads/products/thumbnail/500x500.png') }}" alt="{{ __($customer_product->name) }}"> -->
                                            </a>
                                        </div>
                                        <div class="p-md-2 p-2 prod_info">
                                            @if(Session::has('referal_discount'))
                                            
                                                <div class="discount homep" >
                                                    <span>{{ peer_discounted_percentage($customer_product->id,$shortId) }}% Off</span>
                                                    <img  src="{{ static_asset('frontend/images/discount.png') }}" >
                                                </div>
                                             @endif 
                                            <h2 class="product-title p-0"> 
                                                <span>{{ ucwords(@$customer_product->category->name) }}</span>
                                                <a href="{{ route('product', $customer_product->slug) }}" class="text-truncate">{{ ucfirst($customer_product->name) }}</a>
                                                <span class="quant">
                                                @if ($customer_product->choice_options != null)
                                                    @foreach (json_decode($customer_product->choice_options) as $key => $choice)
                                                        @foreach ($choice->values as $key => $value)
                                                            {{ $value }}
                                                        @endforeach   
                                                    @endforeach
                                                @endif
                                                </span>
                                                <!-- <span class="quant">{{ $customer_product->unit }}</span> -->
                                            </h2>
                                            <?php
                                            if(!empty($shortId)){
                                                $maxquantity_bysh = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('product_id',$customer_product->id)->where('published',1)->first('max_purchaseprice');
                                                $maxquantity = $maxquantity_bysh['max_purchaseprice'];
                                            }else{
                                                $maxquantity = 0;
                                            }
                                            if(!empty($customer_product->max_purchase_qty)){
                                                $adminmaxquantity = $customer_product->max_purchase_qty;
                                            }else{
                                                $adminmaxquantity = 0;
                                            }  
                                           ?>

                                            <div class="price-box">
                                                    @if(Session::has('referal_discount'))
                                                    @if(!empty($shortId))  
                                                            <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($customer_product->id,$shortId)) }}</span>
                                                            @php
                                                            $mappedProductPrice = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$customer_product->id])->first(); 
                                                            @endphp
                                                            @if($mappedProductPrice['selling_price'] !=0)
                                                            <span class="old-product-price strong-400">{{ format_price(@$mappedProductPrice['selling_price']) }}</span>
                                                            @else
                                                            <span class="old-product-price strong-400">{{ format_price(@$customer_product->stocks[0]->price) }}</span>
                                                            @endif
                                                        @else
                                                            <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($customer_product->id,$shortId)) }}</span>
                                                            <del class="old-product-price strong-400">{{ format_price(@$customer_product->stocks[0]->price) }}</del>
                                                        @endif
                                                    @else    
                                                        @if(!empty($shortId))
                                                            @php
                                                            $mappedProductPrice = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$customer_product->id])->first(); 
                                                            @endphp
                                                                @if($mappedProductPrice['selling_price'] !=0)
                                                                <span class="product-price strong-600">{{ format_price(@$mappedProductPrice['selling_price']) }}</span>
                                                                @else
                                                                <span class="product-price strong-600">{{ format_price(@$customer_product->stocks[0]->price) }}</span>
                                                                @endif
                                                            
                                                            @else
                                                            <span class="product-price strong-600">{{ format_price(@$customer_product->stocks[0]->price) }}</span>
                                                            @endif 
                                                    @endif

                                                <input type="hidden" id="stock_qty_{{$customer_product->id}}" name="stock_qty_{{$customer_product->id}}" value="@if(!empty(Cookie::has('pincode'))){{ mapped_product_stock($shortId['sorting_hub_id'],$customer_product->id)}} @else 0 @endif">
                                                <input type="hidden" id="limit_qty_{{$customer_product->id}}" name="limit_qty_{{$customer_product->id}}" value="@if($maxquantity!=0){{ $maxquantity}} @else {{ $adminmaxquantity }} @endif">
                                                <div id="cart_loader{{$customer_product->id}}"></div>
                                                <div class="quantity buttons_added new" id="button-group{{ $customer_product->id }}">
                                                    
                                                    @php
                                                    $class = "";
                                                    if(get_product_cart_qty($customer_product->id)>0)
                                                    {
                                                        $class = "display-none";
                                                    }
                                                    @endphp
                                                    <input type="button" value="Add" class="quant_add_btn plus {{$class}}" id="btn_add{{ $customer_product->id }}" onclick="addToCartF('{{$customer_product->id}}')">
                                                    <input type="button" value="-" class="minus" onclick="add_qty('{{ $customer_product->id }}'),updateCartF('{{$customer_product->id}}')">
                                                    <input type="number" step="1" min="0" max="@if(!empty($customer_product->max_purchase_qty))
                                                    {{$customer_product->max_purchase_qty}} @endif" onkeypress="add_qty(this.id),addToCartF('{{$customer_product->id}}')" id="pamount_{{ $customer_product->id}}" name="quantity{{$customer_product->id}}" value="{{ get_product_cart_qty($customer_product->id) }}" title="Qty" class="input-text qty text" size="4" readonly>
                                                    <input type="button" value="+" class="plus" id="plus-button{{ $customer_product->id }}" onclick="add_qty('{{ $customer_product->id }}'),addToCartF('{{$customer_product->id}}')">
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
                @endif
        @else
                    @php
                    $service_banner = \App\ServiceBanner::first();
                    @endphp
                    <div class="caorusel-box arrow-round gutters-5 mt-2 mb-5">
                    <span><img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/placeholder.jpg') }}"   data-src="{{Storage::disk('s3')->url($service_banner->photo)}}" }}"></span>
                    </div>
        @endif
            </div>
        </section>
    @endif

    <script type="text/javascript">
         $('.quant_add_btn, .quantity.buttons_added.new  .plus').click(function(){
            $(this).parent().find('.quant_add_btn').hide();
         });
         $('.quantity.buttons_added.new  .minus').click(function(){
               var currentVal = parseInt($(this).parent().find('input[type=number]').val());
            if( currentVal==1){
                parseInt($(this).parent().find('input[type=number]').val('0'));
                // $(this).parent().find('input[type=number]').val("0");
                $(this).parent().find('.quant_add_btn').show();
                $(this).parent().find('.quant_add_btn').removeClass('display-none');

            }
         });
    </script>