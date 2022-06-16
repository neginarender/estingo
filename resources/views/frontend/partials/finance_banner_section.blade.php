  <section class="pt-1 bg-white sale-section pb-4 offers" >
    <div class="container">
        <div class="row">
           @foreach (\App\FinanceBanner::where('published', 1)->get() as $key => $banner)
            <div class="col-md-3"> 
                <img class=" lazyload mx-auto" src="{{Storage::disk('s3')->url($banner->photo)}}" alt="{{ env('APP_NAME') }}">
             </div>
            @endforeach
        </div>
      </div>
  </section>