<section class="mb-4" >
    <div class="container">
        <div class="px-2 py-4 p-md-4   ">
         <!--    <div class="section-title-1 clearfix  ">
                <h3 class="heading-3 strong-700 mb-0   ">
                    <span class="mr-4">Featured Products</span>
                </h3>
            </div> -->
            <div class="caorusel-box arrow-round gutters-5 mt-5 mb-5">
                <div class="slick-carousel" data-slick-items="6" data-slick-xl-items="5" data-slick-lg-items="4"  data-slick-md-items="3" data-slick-sm-items="2" data-slick-xs-items="2">
                    @foreach (filter_products(\App\Product::where('published', 1)->where('featured', '1'))->limit(12)->get() as $key => $product)
                    <div class="caorusel-card">
                        <div class="product-card-2 card card-product shop-cards shop-tech">
                            <div class="card-body p-0">

                                <div class="card-image">
                                    <a href="{{ route('product', $product->slug) }}" class="d-block">
                                        <img class="img-fit lazyload mx-auto" src="{{ static_asset('frontend/images/placeholder.jpg') }}" data-src="{{ my_asset($product->thumbnail_img) }}" alt="{{ __($product->name) }}">
                                    </a>
                                </div>

                                <div class="p-md-2 p-2 prod_info">
                                    <h2 class="product-title p-0">
                                        <a href="{{ route('product', $product->slug) }}" class="text-truncate">{{ __($product->name) }}</a>
                                        <span>Category Name</span>
                                    </h2>
                                    <div class="price-box">
                                        @if(home_base_price($product->id) != home_discounted_base_price($product->id))
                                            <del class="old-product-price strong-400">{{ home_base_price($product->id) }}</del>
                                        @endif
                                        <span class="product-price strong-600">{{ home_discounted_base_price($product->id) }}</span>
                                          <a href="" class="float-right"><i class="fa fa-heart-o"></i></a>
                                    </div>
                                  <!--   <div class="star-rating star-rating-sm mt-1">
                                        {{ renderStarRating($product->rating) }}
                                    </div> -->
                                    

                                   <!--  @if (\App\Addon::where('unique_identifier', 'club_point')->first() != null && \App\Addon::where('unique_identifier', 'club_point')->first()->activated)
                                        <div class="club-point mt-2 bg-soft-base-1 border-light-base-1 border">
                                            Club Point:
                                            <span class="strong-700 float-right">{{ $product->earn_point }}</span>
                                        </div>
                                    @endif -->
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

<section class="why-choose-us pt-4 pb-5">
     <div class="container">
        <div class="  ">
            <div class="col-md-12">
                <div class="box">
                    <div class="row">
                        <div class="col-md-3">
                             <img src="{{ static_asset('frontend/images/home/why-choose-us/Group 633.png') }}" alt="{{ env('APP_NAME') }}">
                             <div class="cont"><p>Free Shipping <span>On order above 500</span></p></div>
                         </div>
                         <div class="col-md-3">
                             <img src="{{ static_asset('frontend/images/home/why-choose-us/Group 634.png') }}" alt="{{ env('APP_NAME') }}">
                             <div class="cont"><p>Money Return <span>30 Days money retur</span></p></div>
                         </div>
                         <div class="col-md-3">
                             <img src="{{ static_asset('frontend/images/home/why-choose-us/Group 625.png') }}" alt="{{ env('APP_NAME') }}">
                             <div class="cont"><p>Support 24x7 <span>Helpline : 9875463210 </span></p></div>
                         </div>
                         <div class="col-md-3">
                             <img src="{{ static_asset('frontend/images/home/why-choose-us/Group 635.png') }}" alt="{{ env('APP_NAME') }}">
                             <div class="cont last"><p>Safe Payment <span>Protect Online Payment</span></p></div>
                         </div>
                     </div>
                </div>
            </div>
        </div>
    </div>
</section>
