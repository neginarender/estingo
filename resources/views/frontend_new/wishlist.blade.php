
@extends('frontend_new.layouts.app')
@section('content')


        <!-- main content start -->
        <section class="gry-bg py-4 profile">
          <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                                 @include('frontend_new.inc.customer_side_nav')

                <div class="col-lg-9">
                    <!-- Page title -->
                    <div class="page-title">
                        <div class="row align-items-center">
                            <div class="col-md-6 col-12">
                                <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                    Wishlist
                                </h2>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="float-md-right">
                                    <ul class="breadcrumb">
                                        <li><a href="index.html">Home</a></li>
                                        <li ><a href="user-dashboard.html">Dashboard</a></li>
                                         <li class="active"><a >Wishlist</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--  content -->
                      <div class="row shop-default-wrapper shop-cards-wrapper shop-tech-wrapper mt-2" id="wishlist">
                        @foreach ($wishlists as $key => $wishlist)
                        @php $cid = $wishlist->id.$wishlist->product->product_id;@endphp
                                @if ($wishlist->product != null)
                                 <div class="col-xl-4 col-6 p-2 p-md-3"  >
                                    <div class="card card-product mb-3 product-card-2">
                                        <div class="card-body p-3">
                                            <div class="card-image text-center">
                                                <a href="{{ route('product.details',['id'=>encrypt($wishlist->product->product_id)]) }}" class="d-block"  >
                                                    <img src="{{ Storage::disk('s3')->url($wishlist->product->thumbnail_image) }}" class="h-auto">
                                                </a>
                                            </div>

                                            <h2 class="heading heading-6 strong-600 mt-2 text-truncate-2">
                                                <a href="#">{{ translate($wishlist->product->name) }}</a>
                                            </h2>
                                            <div class="star-rating star-rating-sm mb-1">
                                                {{ renderStarRating($wishlist->product->rating) }}
                                            </div>
                                            <div class="mt-0">
                                                <div class="price-box">
                                                      <span class="product-price strong-600">₹{{$wishlist->product->base_price}}</span>
                                                 </div>
                                            </div>
                                        </div>
                                        <div class="card-footer p-2 p-md-3">
                                            <div class="product-buttons">
                                                <div class="row align-items-center">
                                                    <div class="col-3 col-md-4">
                                                        <a href="#" class="link link--style-3" data-toggle="tooltip" data-placement="top" title="" onclick="removeFromWishlist({{ $wishlist->id }})" data-original-title="Remove from wishlist">
                                                            <i class="fa fa-close"></i>
                                                        </a>
                                                    </div>
                                                 <!--    <div class="col-9 col-md-8">
                                                        <a href="#" class="btn btn-block btn-base-1 btn-circle btn-icon-left" value="Add"  onclick="plus(this),addToCart('{{ $wishlist->id }}')" id="btn_add{{$wishlist->id}}">
                                                            <i class="fa fa-shopping-cart mr-2"></i>{{ translate('Add to cart')}}
                                                        </a>
                                                    </div> -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                                @endif
                        @endforeach
                       </div>
                        
                    </div>
                </div>
           </section>
@section('script')
    <script type="text/javascript">
        function removeFromWishlist(id){
            // alert(id);
            $.post('{{ route('phoneapi.wishlistsremove') }}',{_token:'{{ csrf_token() }}', id:id}, function(data){
                $('#wishlist').html(data);
                // $('#wishlist'+id).hide();
                 window.location.reload();
                showFrontendAlert('success', 'Item has been removed from wishlist');

            })
        }



    </script>
@endsection


@endsection

