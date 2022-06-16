@extends('frontend_new.layouts.app')
@section('content')
<section class="product-details-area gry-bg">
              <div class="container">
                <div class="bg-white">
                    <!-- Product gallery and Description -->
                    <div class="row no-gutters cols-xs-space cols-sm-space cols-md-space">
                        <div class="col-lg-5">
                            <div class="product-gal sticky-top  ">
                                     <div class="product-gal-img" style="width: 100%;">
                                     @if(is_array($product->photos) && count($product->photos) > 0)
                                     <img src="{{static_asset('frontend/images/placeholder.jpg')}}" class="xzoom img-fluid lazyload" src="{{static_asset('frontend/images/placeholder.jpg')}}" data-src="{{Storage::disk('s3')->url($product->photos[0])}}" xoriginal="{{Storage::disk('s3')->url($product->photos[0])}}" />
                                
                                    @endif
                                    </div>
                                    <div class="product-gal-thumb">
                                        <div class="xzoom-thumbs">
                                        @foreach ($product->photos as $key => $photo)
                                            <a href="{{ my_asset($photo) }}">
                                                <img src="{{static_asset('frontend/images/placeholder.jpg')}}" class="xzoom-gallery lazyload" src="{{static_asset('frontend/images/placeholder.jpg')}}"   data-src="{{Storage::disk('s3')->url($photo)}}"  @if($key == 0) xpreview="{{Storage::disk('s3')->url($photo)}}" @endif>
                                            </a>
                                        @endforeach
                                        </div>
                                    </div>
                             </div>
                        </div>

                        <div class="col-lg-7">
                            <!-- Product description -->
                            <div class="product-description-wrapper">
                                <!-- Product title -->
                                <div class="product_cat"><span><a href="#">Grocery &amp; Staple </a> &gt; <a href="#">Edible Oils &amp; Ghee</a> &gt; <a href="">Soyabean Oil</a></span></div>
                                 <ul class="inline-links inline-links--style-1 float-right status">
                                    <li>
                                        <span class="badge badge-md badge-pill bg-green">{{ translate('In stock')}}</span>
                                    </li>
                               <!-- @if ($product->quantity > 0) -->
                                    <!-- <li>
                                        <span class="badge badge-md badge-pill bg-green">{{ translate('In stock')}}</span>
                                    </li> -->
                                <!-- @else -->
                                  <!--   <li>
                                        <span class="badge badge-md badge-pill bg-red">{{ translate('Out of stock')}}</span>
                                    </li> -->
                               <!-- @endif  -->
                                  </ul>
                                  <h1 class="product-title mb-2" id="product_name">
                                     {{ translate($product->name) }}
                                  </h1>  
                                <div class="row align-items-center my-0">
                                    <div class="col-6">
                                        <!-- Rating stars -->
                                        <div class="rating">
                                            <span class="star-rating">
                                                <i class="fa fa-star active"></i><i class="fa fa-star active"></i><i class="fa fa-star active"></i><i class="fa fa-star active"></i><i class="fa fa-star half"></i>
                                            </span>
                                              <span class="rating-count ml-1">({{ $product->reviewCount }} Reviews)</span>
                                       </div>
                                    </div>
                                    <div class="col-6 text-right">
                                       
                                    </div>
                                </div>    
                                @php 
                                $price = $product->stock_price;
                                $del_price = $product->stock_price;

                                if(Cookie::has('peer')){
                                    $price = $product->base_price;
                                }
                                @endphp                       
                                 <div class="row no-gutters mt-1">
                                    <div class="col-12">
                                        <div class="product-price">
                                        <span class="product-price strong-600">{{ single_price($price) }}</span>
                                        @if(Cookie::has('peer'))
                                        <del class="old-product-price strong-400">{{ single_price($del_price) }}</</del>
                                        @endif
                                        </div>
                                    </div>
                                </div>



                                @foreach($product->choice_options as $key => $choices)
                                        @php $cid = $product->id.$key.$product->id;@endphp  

                                <form id="product_form{{ $cid }}">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">      
                                    <input type="hidden" name="id" value="{{ $product->id }}">  
                                    <input type="hidden" name="variant" value="{{ $product->variant }}">   
                                    <input type="hidden" id="max_purchase_qty{{$cid}}" name="max_purchase_qty" value="{{ $product->max_purchase_qty }}">     
                                <!-- </form> -->

                                <div class="row no-gutters mt-3 pb-1">
                                    <div class="col-3 col-md-2">
                                        <div class="product-description-label mt-2 ">{{ $choices->title }}:</div>
                                    </div>
                                    <div class="col-9 col-md-9">
                                        <ul class="list-inline checkbox-alphanumeric checkbox-alphanumeric--style-1 mb-2">
                                            @foreach($choices->options as $k=> $option)
                                             <li>
                                                <input type="radio" id="7-5 L" name="attribute_id_7" value="5 L" checked="">
                                                <label for="7-5 L">{{ $option }}</label>
                                            </li>
                                            @endforeach
                                         </ul>
                                    </div>
                                </div>
                                @endforeach



                                    <!-- Quantity + Add to cart -->
                                    <div class="row no-gutters pb-2 pt-2">
                                         <div class="col-3 col-md-2">
                                            <div class="product-description-label mt-2">Quantity:</div>
                                        </div>
                                         <div class="col-9 col-md-4">
                                            <div class="product-quantity d-flex align-items-center">
                                                <div class="input-group input-group--style-2 pr-3" style="width: 120px;">
                                                <input type="hidden" class="form-control h-auto input-number text-center" name="limit_qty" value="3 " max="927">
                                                    <span class="input-group-btn minus-btn">
                                                        <button class="btn btn-number" type="button" data-type="minus" data-field="quantity" disabled="disabled">
                                                            <i class="fa fa-minus"></i>
                                                        </button>
                                                    </span>
                                                    <input style="font-weight: 600" type="text" name="quantity" class="form-control h-auto input-number text-center" id="pqty{{$cid}}" placeholder="1" value=" 1 " min="1" max="927">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-number" type="button" data-type="plus" data-field="quantity">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </span>
                                                </div>
                                          </div>
                                        </div>
                                        <div class="col-6 pt-4 pt-md-0 col-md-3">
                                             <div class="product-description-label mt-2">Max Purchase Limit</div>    
                                        </div>
                                        <div class="col-6 pt-4 pt-md-0  col-md-2">
                                             <div class="product-description-label mt-2">  
                                             <span class="badge bg-info p-2 text-large  text-white"  >  {{ $product->max_purchase_qty }} </span></div>
                                         </div> 

                                         

                                    </div>
                                    

                                    <div class="row no-gutters pt-4 pb-3" id="chosen_price_div">
                                        <div class="col-3 col-md-2">
                                            <div class="product-description-label">Total Price:</div>
                                        </div>
                                        <div class="col-9 col-md-9">
                                            <div class="product-price">
                                                <strong id="chosen_price">{{ single_price($price) }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-table width-100 mt-3">
                                        <div class="d-table-cell">
                                            <!-- Buy Now button -->
                                         <!-- <button type="button" class="wishlist float-right" onclick="addtoWishlist({{ $product->id }})">
                                         
                                          <i class="fa fa-heart-o"></i>
                                        </button>        -->
                                          <button type="button" class="btn btn-cart btn-styled btn-alt-base-1 c-white btn-icon-left strong-700 hov-bounce hov-shaddow ml-2 add-to-cart" onclick="addToCart('{{ $cid }}')" >
                                              <i class="fa fa-cart-plus"  ></i>
                                             <span class="d-none d-md-inline-block"> Add to cart</span>
                                            </button>
                                            <button type="button" onclick="buyNow('{{ $cid }}')" class="btn btn-cart btn-styled btn-base-1 btn-icon-left strong-700 hov-bounce hov-shaddow buy-now"  >
                                                <i class="fa fa-shopping-cart" ></i> Buy Now 
                                            </button>
                                            <!-- <button type="button" onclick="subscribeModal()" class="btn btn-cart btn-styled btn-base-1 btn-icon-left strong-700 hov-bounce hov-shaddow buy-now m-2 m-md-0"  >
                                                <i class="fa fa-share" ></i> Subscribe
                                            </button> -->
                                       </div>
                                    </div>
                                    </form>
                                <hr class="mt-4">
                                  <div class="row align-items-center mt-4">
                                      <div class="sold-by col-auto">
                                        <small class="mr-2">Sold by: </small><br>
                                                Inhouse product
                                        </div>
                                         <!-- <div class="col-auto">
                                            <button class="btn btn-secondary  btn-sm" onclick="show_chat_modal()">Bulk Order Enquiry</button>
                                        </div> -->
                                         <div class="col-auto">
                                            <img src="https://rozaana.s3.ap-south-1.amazonaws.com/uploads/brands/a9qmKoYi40dw9cG9RCdIadHFUzqkC3yMEr88T3nO.jpg" alt="Fortune" height="30">
                                        </div>
                                  </div>
                                 <div class="row no-gutters mt-4">
                                    <div class="col-2">
                                        <div class="product-description-label mt-2">Share:</div>
                                    </div>
                                    <div class="col-10">
                                        <div id="share" class="jssocials">
                                            <div class="jssocials-shares">
                                                <div class="jssocials-share jssocials-share-email"><a target="_self" href="mailto:?&amp;" class="jssocials-share-link"><i class="fa fa-at jssocials-share-logo"></i></a></div>
                                                <div class="jssocials-share jssocials-share-twitter"><a target="_blank" href="https://twitter.com/share?url=" class="jssocials-share-link"><i class="fa fa-twitter jssocials-share-logo"></i></a></div>
                                                <div class="jssocials-share jssocials-share-facebook"><a target="_blank" href="https://facebook.com/sharer/sharer.php?u=" class="jssocials-share-link"><i class="fa fa-facebook jssocials-share-logo"></i></a></div>
                                                <div  class="jssocials-share jssocials-share-linkedin"><a target="_blank" href="https://www.linkedin.com/shareArticle?mini=true&amp;url=" class="jssocials-share-link"><i class="fa fa-linkedin jssocials-share-logo"></i></a></div>
                                                <div   class="jssocials-share jssocials-share-pinterest"><a target="_blank" href="https://pinterest.com/pin/create/bookmarklet/?&amp;url=" class="jssocials-share-link"><i class="fa fa-pinterest jssocials-share-logo"></i></a></div>
                                               <div  class="jssocials-share jssocials-share-stumbleupon"><a target="_blank" href="http://www.stumbleupon.com/submit?url=" class="jssocials-share-link"><i class="fa fa-stumbleupon jssocials-share-logo"></i></a></div>
                                              <div  class="jssocials-share jssocials-share-whatsapp"><a target="_self" href="whatsapp://send?text= " class="jssocials-share-link"><i class="fa fa-whatsapp jssocials-share-logo"></i></a></div>
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
          <section class="gry-bg  pb-5">
                <div class="container">
                   <div class="row">
                       <div class="col-xl-12">
                        <div class="product-desc-tab bg-white">
                            <div class="tabs tabs--style-2">
                                <ul class="nav nav-tabs   sticky-top bg-white">
                                    <li class="nav-item">
                                        <a href="#tab_default_1" data-toggle="tab" class="nav-link text-uppercase strong-600 active show">Description</a>
                                    </li>
                                     <li class="nav-item">
                                        <a href="#tab_default_4" data-toggle="tab" class="nav-link text-uppercase strong-600">Reviews</a>
                                    </li>
                                </ul>
                                 <div class="tab-content pt-0">
                                    <div class="tab-pane active show" id="tab_default_1">
                                        <div class="py-2 px-4">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mw-100 pt-4 overflow--hidden aiz-product-description">
                                                        <?php echo $product->description ?>                  
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="tab_default_4">
                                         <div class="fluid-paragraph py-4">
                                            @foreach($review as $revie)
                                            <div class="block block-comment">
                                                <div class="block-image">
                                                    <img src="{{ static_asset('frontend/images/user.png') }}"   class="rounded-circle">
                                                </div>
                                                 <div class="block-body">
                                                    <div class="block-body-inner prod_review">
                                                        <div class="row no-gutters">
                                                            <div class="col">
                                                                @foreach($revie->user as $uname)
                                                                <h3 class="heading heading-6">
                                                                    {{$uname}}
                                                                </h3>
                                                                @endforeach
                                                                <span class="comment-date">
                                                                     {{$revie->time}}
                                                                </span>
                                                            </div>
                                                             <div class="col">
                                                                <div class="rating text-right clearfix d-block">
                                                                    <span class="star-rating star-rating-sm float-right">
                                                                        @php  $cont=$revie->rating; @endphp
                                                                        @for($i=0;$i<$cont;$i++)
                                                                         <i class="fa fa-star active"></i> 
                                                                         @endfor
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <p class="comment-text">
                                                                     {{$revie->comment}}
                                                         </p>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                       
                                            <!-- leave comment -->
                                            <div class="leave-review">
                                                <div class="section-title section-title--style-1">
                                                    <h3 class="section-title-inner heading-6 strong-600 text-uppercase">
                                                        Write a review
                                                    </h3>
                                                </div>
                                                <form class="form-default" role="form" action="{{route('phoneapi.add_review')}}" method="POST">
                                                    <div class="row">

                                                        @if(session()->has('access_token'))
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                              <input type="hidden" name="_token" value="{{csrf_token()}}" />
                                                              <input type="hidden" name="product_id" value="{{ $product->id }}" />
                                                              <input type="hidden" name="name" value="{{ $user->name }}" />

                                                                <label for="" class="text-uppercase c-gray-light">Your name</label>
                                                                <input type="text" name="name" value="{{$user->name}}" class="form-control" disabled="" required="">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="" class="text-uppercase c-gray-light">Email</label>
                                                                <input type="text" name="email" value="{{$user->email}}" class="form-control" required="" disabled="">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <div class="c-rating mt-1 mb-1 clearfix d-inline-block">
                                                                <input type="radio" id="star5" name="rating" value="5" required="">
                                                                <label class="star" for="star5" title="Awesome" aria-hidden="true"></label>
                                                                <input type="radio" id="star4" name="rating" value="4" required="">
                                                                <label class="star" for="star4" title="Great" aria-hidden="true"></label>
                                                                <input type="radio" id="star3" name="rating" value="3" required="">
                                                                <label class="star" for="star3" title="Very good" aria-hidden="true"></label>
                                                                <input type="radio" id="star2" name="rating" value="2" required="">
                                                                <label class="star" for="star2" title="Good" aria-hidden="true"></label>
                                                                <input type="radio" id="star1" name="rating" value="1" required="">
                                                                <label class="star" for="star1" title="Bad" aria-hidden="true"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-3">
                                                        <div class="col-sm-12">
                                                            <textarea class="form-control height-100" rows="4" name="comment" placeholder="Your review" required=""></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="text-right">
                                                        <button type="submit" class="btn btn-styled btn-base-1 btn-circle mt-4">
                                                            Send review
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                     </div>
                   </div>
               </div>

                <div class="best_seller home_section">
                <div class="container">
                    <div class="row gutters-10">
                        <div class="col-md-12  "> 
                            <div class="sec_title  ">
                                 <h4 class="black">Related Products</h4>
                            </div> 
                        </div>
                         <div class="col-md-12 p-0">
                                <div class="caorusel-box arrow-round gutters-5">
                                    <div class="slick-carousel product_carausel" data-slick-items="5" data-slick-xl-items="5" data-slick-lg-items="5" data-slick-md-items="4" data-slick-sm-items="2" data-slick-xs-items="2">
                                    @foreach($related as $kk => $relatedproduct)    
                                    <div class="caorusel-card">
                                             <div class="product-card-2   shop-cards shop-tech">
                                                <div class="card-body p-0">
                                                    <div class="card-image">
                                                        <a href="{{ route('product.details',['id'=>encrypt($relatedproduct->product_id)]) }}" class="d-block" tabindex="0">
                                                          <img class="img-fit mx-auto" src="{{ Storage::disk('s3')->url($relatedproduct->thumbnail_image) }}" alt="Onion ( Pyaaj )">
                                                        </a>
                                                    </div>
                                                    <div class="p-md-2 p-2 prod_info mb-4">
                                                        <h2 class="product-title p-0"> 
                                                            <span>Vegetables</span>
                                                            <a href="{{ route('product.details',['id'=>encrypt($relatedproduct->product_id)]) }}" class="text-truncate" tabindex="0">{{ translate($relatedproduct->name) }}</a>
                                                            <span class="quant mt-1">
                                                            500 Gm
                                                                <!-- <select class="form-control" tabindex="0">
                                                                    <option> 500 Gm  </option>
                                                                </select> -->
                                                             </span>
                                                        </h2>
                                                    </div>
                                                    @php 
                                                    $price = $product->stock_price;
                                                    $del_price = $product->stock_price;

                                                    if(Cookie::has('peer')){
                                                        $price = $product->base_price;
                                                    }
                                                    @endphp 
                                                     <div class="price-box p-2 pt-3">
                                                     <span class="product-price strong-600">{{ single_price($price) }}</span>
                                                        @if(Cookie::has('peer'))
                                                        <del class="old-product-price strong-400">{{ single_price($del_price) }}</</del>
                                                        @endif
                                                         <!-- <div class="quantity buttons_added new" id="button-group">
                                                            <div class="cart_loader" ><div class="spinner-border text-success" role="status"></div></div>
                                                               <input type="button" value="Add" class="quant_add_btn plus " id="btn_add"  tabindex="0">
                                                                <input type="button" value="-" class="minus"   tabindex="0">
                                                                <input type="number" step="1" min="0" max="10"   id="pamount" name="" value="0" title="Qty" class="input-text qty text" size="4" readonly="" tabindex="0">
                                                                <input type="button" value="+" class="plus" id="plus-button"  tabindex="0">
                                                                <div class="clearfix"></div>
                                                         </div> -->
                                                         
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

           @section('script')
    <script type="text/javascript">
        function addtoWishlist(id){
           
             $.post('{{ route('phoneapi.addwishlists') }}',{_token:'{{ csrf_token() }}', id:id}, function(data){
                $('#wishlist').html(data);
                // $('#wishlist'+id).hide();
                // window.location.reload();
                showFrontendAlert('success', 'Item add to wishlist');
            })

    
        }


    </script>
@endsection
@endsection

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <!-- Custom style -->
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<script src="{{ static_asset('frontend/new/assets/js/intlTelInput.min.js')}}"></script>
@endsection

