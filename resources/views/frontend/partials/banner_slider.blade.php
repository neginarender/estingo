<section class="pt-1 bg-white   banners" >
     <div class="container">
          <div class="caorusel-box arrow-round gutters-5 mt-2  ">
                <div class="slick-carousel" data-slick-items="1" data-slick-xl-items="1" data-slick-lg-items="1"  data-slick-md-items="1" data-slick-sm-items="1" data-slick-xs-items="1">

                    @foreach (\App\BannerSlider::where('published', 1)->get() as $key => $banner)
                    <div class="caorusel-card" style="height: auto">
                        <div class="product-card-2   shop-cards shop-tech">
                            <div class="card-body p-0">
                                <div class="card-image">
                                    <a href="{{$banner->link}}" class="d-block">
                                        <img style="border-radius: 10px;" class="img-fit lazyload mx-auto" src="{{Storage::disk('s3')->url($banner->photo)}}" alt="{{ env('APP_NAME') }}">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
     </div>
</section>