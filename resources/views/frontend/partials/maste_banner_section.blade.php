@if(count(\App\MasterBanner::where('published', 1)->get()) > 0)
  <section class="  pt-4 bg-white sale-section pb-4 offers" >
      <div class="container">
          <div class="row">
            @foreach (\App\MasterBanner::where('published', 1)->get() as $key => $banner)
              <div class="col-md-12 @if($key != 0) {{'mt-4'}} @endif">
              <a href="{{ $banner->link }}" target="_blank" class="banner-container">
                  <img class="lazyload mx-auto" src="{{Storage::disk('s3')->url($banner->photo)}}" alt="{{ env('APP_NAME') }}">
              </a>    
              </div>
            @endforeach
          </div>
      </div>
  </section>
@endif