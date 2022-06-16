@extends('frontend.layouts.app')

@php
$shortId = "";
if(!empty(Cookie::get('pincode'))){ 
                                    $pincode = Cookie::get('pincode');
                                    $distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->pluck('id')->all();
                                    $shortId = \App\MappingProduct::whereIn('distributor_id',$distributorId)->first('sorting_hub_id');
        
    }

@endphp
@section('content')
<style type="text/css">
    @media (max-width: 800px){
        .tooltip{display: none;}
    }
</style>
    <section class="gry-bg py-4 profile">
        <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-lg-3 d-none d-lg-block">
                @if(Auth::user()->user_type == 'seller')
                        @include('frontend.inc.seller_side_nav')
                    @elseif(Auth::user()->user_type == 'customer' ||Auth::user()->user_type == 'partner' || (Auth::user()->user_type=="staff" && Auth::user()->peer_partner==1))
                        @include('frontend.inc.customer_side_nav')
                    @endif
                </div>

                <div class="col-lg-9">
                    <div class="main-content">
                        <!-- Page title -->
                        <div class="page-title">
                            <div class="row align-items-center">
                                <div class="col-md-6 col-12">
                                    <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                        {{ translate('Wishlist')}}
                                    </h2>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="float-md-right">
                                        <ul class="breadcrumb">
                                            <li><a href="{{ route('home') }}">{{ translate('Home')}}</a></li>
                                            <li><a href="{{ route('dashboard') }}">{{ translate('Dashboard')}}</a></li>
                                            <li class="active"><a href="{{ route('wishlists.index') }}">{{ translate('Wishlist')}}</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Wishlist items -->

                        <div class="row shop-default-wrapper shop-cards-wrapper shop-tech-wrapper mt-4">
                            @foreach ($wishlists as $key => $wishlist)
                                @if ($wishlist->product != null)
                                    <div class="col-xl-4 col-6" id="wishlist_{{ $wishlist->id }}">
                                        <div class="card card-product mb-3 product-card-2">
                                            <div class="card-body p-3">
                                                <div class="card-image">
                                                    <a href="{{ route('product', $wishlist->product->slug) }}" class="d-block" style="background-image:url('{{ my_asset($wishlist->product->thumbnail_img) }}');">
                                                    </a>
                                                </div>

                                                <h2 class="heading heading-6 strong-600 mt-2 text-truncate-2">
                                                    <a href="{{ route('product', $wishlist->product->slug) }}">{{ $wishlist->product->name }}</a>
                                                </h2>
                                                <div class="star-rating star-rating-sm mb-1">
                                                    {{ renderStarRating($wishlist->product->rating) }}
                                                </div>
                                                <div class="mt-2">
                                                    <div class="price-box">

                                            @if(Session::has('referal_discount'))
                                                @if(!empty($shortId))
                                                        @if(getShortId($shortId,$wishlist->product_id)->selling_price !=0)
                                                            <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($wishlist->product_id,$shortId)) }}</span> 
                                                            <del class="old-product-price strong-400">{{ format_price(@getShortId($shortId,$wishlist->product_id)->selling_price) }}</del>
                                                        @else
                                                            <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($wishlist->product_id,$shortId)) }}</span>
                                                            <del class="old-product-price strong-400">{{ format_price(@$wishlist->product->stocks[0]->price) }}</del>
                                                        @endif

                                                @else
                                                            <span class="product-price strong-600">{{ single_price(peer_discounted_newbase_price($wishlist->product_id)) }}</span>
                                                            <del class="old-product-price strong-400">{{ format_price(@$wishlist->product->stocks[0]->price) }}</del>
                                                @endif

                                            @else    
                                                  
                                                @if(!empty($shortId))
                                                      @if(getShortId($shortId,$wishlist->product_id)->selling_price !=0)
                                                        <span class="product-price strong-600">{{ format_price(@getShortId($shortId,$wishlist->product_id)->selling_price) }}</span>
                                                      @else
                                                      <span class="product-price strong-600">{{ format_price(@$wishlist->product->stocks[0]->price) }}</span>
                                                      @endif

                                                @else
                                                    @if(format_price(@$wishlist->product->stocks[0]->price) == 0)
                                                    <span class="product-price strong-600">{{ format_price($wishlist->product->unit_price) }}</span>
                                                    @else
                                                    <span class="product-price strong-600">{{ format_price(@$wishlist->product->stocks[0]->price) }}</span>
                                                    @endif
                                                  
                                                  @endif
                                            @endif



                                                   <!--  @if(Session::has('referal_discount'))
                                                        <span class="product-price strong-600">{{ peer_discounted_base_price($wishlist->product->id) }}</span>
                                                        <del class="old-product-price strong-400">{{ format_price(@$wishlist->product->stocks[0]->price) }}</del>
                                                    @else    
                                                          <span class="product-price strong-600">{{ format_price(@$wishlist->product->stocks[0]->price) }}</span>
                                                    @endif -->
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer p-3">
                                                <div class="product-buttons">
                                                    <div class="row align-items-center">
                                                        <div class="col-2">
                                                            <a href="#" class="link link--style-3" data-toggle="tooltip" data-placement="top" title="Remove from wishlist" onclick="removeFromWishlist({{ $wishlist->id }})">
                                                                <i class="la la-close"></i>
                                                            </a>
                                                        </div>
                                                        <div class="col-10">
                                                            <!-- <button type="button" class="btn btn-block btn-base-1 btn-circle btn-icon-left" onclick="showAddToCartModal({{ $wishlist->product->id }})">
                                                                <i class="la la-shopping-cart mr-2"></i>{{ translate('Add to cart')}}
                                                            </button> -->
                                                            <a href="{{ route('product', $wishlist->product->slug) }}" class="btn btn-block btn-base-1 btn-circle btn-icon-left">
                                                                <i class="la la-shopping-cart mr-2"></i>{{ translate('Add to cart')}}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="pagination-wrapper py-4">
                            <ul class="pagination justify-content-end">
                                {{ $wishlists->links() }}
                            </ul>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="addToCart" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="c-preloader">
                    <i class="fa fa-spin fa-spinner"></i>
                </div>
                <button type="button" class="close absolute-close-btn" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div id="addToCart-modal-body">

                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        function removeFromWishlist(id){
            $.post('{{ route('wishlists.remove') }}',{_token:'{{ csrf_token() }}', id:id}, function(data){
                $('#wishlist').html(data);
                $('#wishlist_'+id).hide();
                showFrontendAlert('success', 'Item has been removed from wishlist');
            })
        }
    </script>
@endsection
