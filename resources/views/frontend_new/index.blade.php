@extends('frontend_new.layouts.app')
@section('content')

        <!-- Header -->
        <!-- locaiton popup -->
 
      <!-- slider -->
        <section class="home-banner-area ">
            <div class="">
                <div class="no-gutters position-relative">
                    <div class="col-lg-12">
                        <div class="home-slide  d-none d-md-block">
                            <div class="home-slide">
                                <div class="slick-carousel" data-slick-arrows="true" data-slick-dots="true" data-slick-autoplay="true">
                                    @foreach($sliders as $key => $slider)
                                    <div class="home-slide-item">
                                        <a href="products-list.html" target="_blank">
                                             <img class="d-block w-100 h-100 lazyload"  src="{{ Storage::disk('s3')->url($slider->photo) }}" data-src="{{ Storage::disk('s3')->url($slider->photo) }}" alt="Rozana">
                                        </a>
                                    </div>
                                   @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="home-slide d-block d-md-none">
                            <div class="home-slide">
                                <div class="slick-carousel" data-slick-arrows="true" data-slick-dots="true" data-slick-autoplay="true">
                                    <div class="home-slide-item">
                                        <a href="products-list.html" target="_blank">
                                           <img class="d-block w-100 h-100 lazyload" src="https://rozaana.s3.ap-south-1.amazonaws.com/uploads/sliders/LHqf50KFudN9bc6Zcvj0UQL5eKkbc812RpS3kafC.webp" data-src="https://rozaana.s3.ap-south-1.amazonaws.com/uploads/sliders/LHqf50KFudN9bc6Zcvj0UQL5eKkbc812RpS3kafC.webp" alt="">
                                         </a>
                                    </div>


                                    <div class="home-slide-item">
                                        <a href="products-list.html" target="_blank">
                                            <img class="d-block w-100 h-100 lazyload" src="https://rozaana.s3.ap-south-1.amazonaws.com/uploads/sliders/VhrkJ2Dy7eOOUecz37lqUsmsQRMB9XGa4QwMlY8F.webp" data-src="https://rozaana.s3.ap-south-1.amazonaws.com/uploads/sliders/VhrkJ2Dy7eOOUecz37lqUsmsQRMB9XGa4QwMlY8F.webp" alt="">
                                          </a>
                                    </div>


                                    <div class="home-slide-item">
                                        <a href="products-list.html" target="_blank">
                                             <img class="d-block w-100 h-100 lazyload" src="https://rozaana.s3.ap-south-1.amazonaws.com/uploads/sliders/JHa2ycYSm0g98QxD7e7PTsa712R2TZKpJofvemtJ.webp" data-src="https://rozaana.s3.ap-south-1.amazonaws.com/uploads/sliders/JHa2ycYSm0g98QxD7e7PTsa712R2TZKpJofvemtJ.webp" alt="">
                                          </a>
                                    </div>

  
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end slider -->

        <!-- main content start -->
        <div class="bg-home">
            <!-- new ticker -->
            <!-- <marquee class="news" direction="left">
               Dear Customer, please be informed that Cash on delivery is not valid for orders above Rs 2500/-
             </marquee> -->
             <!-- end new ticker -->

             <!-- category carausel -->
            <div class="categoriesss  pt-3 ">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 mt-md-2">
                            <div class="trending-category">
                                <div class="caorusel-box arrow-round gutters-5">
                                    <div class="slick-carousel home-cat" data-slick-items="6" data-slick-xl-items="6" data-slick-lg-items="6" data-slick-md-items="6" data-slick-sm-items="4" data-slick-xs-items="3">
                                    @foreach($categories as $key => $category)    
                                    <div class="caorusel-card">
                                            <div class="trend-category-single">
                                                <a href="{{ route('category.products',['id'=>encrypt($category->id),'type'=>'category']) }}" class="d-block">
                                                   <img src="{{Storage::disk('s3')->url($category->banner)}}" alt="Pre-Cut">
                                                   <div class="name">
                                                   {{ translate($category->name) }}
                                                 </div>
                                                </a>
                                            </div>
                                        </div>
                                       @endforeach
                                       
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- best seller products -->
             {{-- <div class="best_seller home_section">
                <div class="container-fluid">
                    <div class="row gutters-10">
                        <div class="col-md-12  "> 
                            <div class="sec_title  ">
                                 <h4 class="black">Bestseller</h4>
                            </div> 
                        </div>
                         <div class="col-md-12 p-0">
                                <div class="caorusel-box arrow-round gutters-5">
                                    <div class="slick-carousel product_carausel" data-slick-items="5" data-slick-xl-items="5" data-slick-lg-items="5" data-slick-md-items="4" data-slick-sm-items="2" data-slick-xs-items="2">
                                    @foreach($best_sellers as $key => $best_seller)  
                                    @php 
                                        $cid = $best_seller->category_id.$key.$best_seller->product_id;
                                        
                                    @endphp  
                                    <div class="caorusel-card">
                                             <div class="product-card-2   shop-cards shop-tech">
                                                <div class="card-body p-0">
                                                    <div class="card-image">
                                                        <a href="{{ route('product.details',['id'=>encrypt($best_seller->product_id)]) }}" class="d-block" tabindex="0">
                                                          <img class="img-fit mx-auto" src="{{ Storage::disk('s3')->url($best_seller->thumbnail_image) }}" alt="Onion ( Pyaaj )">
                                                        </a>
                                                    </div>
                                                    <div class="p-md-2 p-2 prod_info mb-4">
                                                        <h2 class="product-title p-0"> 
                                                            <span>{{ translate('Category name') }}</span>
                                                            <a href="{{ route('product.details',['id'=>encrypt($best_seller->product_id)]) }}" class="text-truncate" tabindex="0">{{ translate($best_seller->name) }}</a>
                                                            <span class="quant mt-1">
                                                                {{ $best_seller->variant }}
                                                               
                                                             </span>
                                                        </h2>
                                                    </div>

                                                    <form id="product_form{{ $cid }}">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">      
                                                        <input type="hidden" name="id" value="{{ $best_seller->product_id }}">  
                                                        <input type="hidden" name="variant" value="{{ $best_seller->variant }}">   
                                                        <input type="hidden" id="max_purchase_qty{{$cid}}" name="max_purchase_qty{{$cid}}" value="{{ $best_seller->max_purchase_qty }}">     
                                                    </form>

                                                     <div class="price-box p-2 pt-3">
                                                     @php 
                                                         $price = $best_seller->stock_price;
                                                         $del_price = $best_seller->stock_price;

                                                            if(Cookie::has('peer')){
                                                                $price = $best_seller->base_price;
                                                            }
                                                         @endphp
                                                        <span class="product-price strong-600">{{ single_price($price) }}</span>
                                                        @if(Cookie::has('peer'))
                                                        <del class="old-product-price strong-400">{{ single_price($del_price) }}</</del>
                                                        @endif
                                                       
                                                        <div class="quantity buttons_added new" id="button-group{{$cid}}">
                                                            <div class="cart_loader" ><div class="spinner-border text-success" role="status"></div></div>
                                                               <input type="button" value="Add" class="quant_add_btn plus @if($best_seller->cart_qty>0)display-none @endif" onclick="plus(this),addToCart('{{ $cid }}')" id="btn_add{{$cid}}"  tabindex="0">
                                                                <input type="button" value="-" class="minus" onclick="minus(this),updateCart('{{ $cid }}')" tabindex="0">
                                                                <input type="number" step="1" min="0" max="{{ $best_seller->max_purchase_qty}}" id="pqty{{$cid}}" dcid="{{ $best_seller->cart_id }}" name="pqty{{$cid}}" value="{{ $best_seller->cart_qty }}" title="Qty" class="input-text qty text" size="4" readonly="" tabindex="0">
                                                                <input type="button" value="+" class="plus" id="plus-button{{ $cid }}" onclick="plus(this),updateCart('{{ $cid }}')"  tabindex="0">
                                                                <div class="clearfix"></div>
                                                        </div>

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
            </div> --}}

             <!-- Fruits  -->
             @foreach($allcategories as $ckey => $categ)
             <div class="home_section">
                <div class="container-fluid">
                    <div class="row gutters-10">
                        <div class="col-md-12  "> 
                            <div class="sec_title  ">
                                <a href="{{ route('category.products',['id'=>encrypt($categ->id),'type'=>'category']) }}" class="btn btn-p btn-base-1 float-right mt-1">Explore All</a>
                                 <h4 class="black">{{ translate($categ->name) }}</h4>
                            </div> 
                        </div>
                         <div class="col-md-12 p-0">
                                <div class="caorusel-box arrow-round gutters-5">
                                    <div class="slick-carousel product_carausel" data-slick-items="5" data-slick-xl-items="5" data-slick-lg-items="5" data-slick-md-items="4" data-slick-sm-items="2" data-slick-xs-items="2">
                                       @foreach($categ->products as $pk => $product)
                                       @php $cid = $categ->id.$ckey.$pk.$product->id;@endphp  
                                        <div class="caorusel-card">
                                             <div class="product-card-2   shop-cards shop-tech">
                                                <div class="card-body p-0">
                                                    <div class="card-image">
                                                        <a href="{{ route('product.details',['id'=>encrypt($product->id)]) }}" class="d-block" tabindex="0">
                                                          <img class="img-fit mx-auto" src="{{ Storage::disk()->url($product->thumbnail_img) }}" alt="">
                                                        </a>
                                                    </div>
                                                    <div class="p-md-2 p-2 prod_info mb-4">
                                                        <h2 class="product-title p-0"> 
                                                            <span>Category name</span>
                                                            <a href="{{ route('product.details',['id'=>encrypt($product->id)]) }}" class="text-truncate" tabindex="0">{{ translate($product->name) }}</a>
                                                            <span class="quant mt-1">
                                                                {{ $product->variant }}
                                                                <!-- <select class="form-control" tabindex="0">
                                                                    <option> 500 Gm  </option>
                                                                </select> -->
                                                             </span>
                                                        </h2>
                                                    </div>
                                                    <form id="product_form{{ $cid }}">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">      
                                                        <input type="hidden" name="id" value="{{ $product->id }}">  
                                                        <input type="hidden" name="variant" value="{{ $product->variant }}">   
                                                        <input type="hidden" id="max_purchase_qty{{$cid}}" name="max_purchase_qty" value="{{ $product->max_purchase_qty }}">     
                                                    </form>
                                                     <div class="price-box p-2 pt-3">
                                                         @php 
                                                         $price = $product->stock_price;
                                                         $del_price = $product->stock_price;

                                                            if(Cookie::has('peer')){
                                                                $price = $product->base_price;
                                                            }
                                                         @endphp
                                                        <span class="product-price strong-600">{{ single_price($price) }}</span>
                                                        @if(Cookie::has('peer'))
                                                        <del class="old-product-price strong-400">{{ single_price($del_price) }}</</del>
                                                        @endif
                                                         <div class="quantity buttons_added new" id="button-group{{$cid}}">
                                                            <div class="cart_loader" ><div class="spinner-border text-success" role="status"></div></div>
                                                               <input type="button" value="Add" class="quant_add_btn plus @if($product->cart_qty>0)display-none @endif" onclick="plus(this),addToCart('{{ $cid }}')" id="btn_add{{$cid}}"  tabindex="0">
                                                                <input type="button" value="-" class="minus" onclick="minus(this),updateCart('{{ $cid }}')" tabindex="0">
                                                                <input type="number" step="1" min="0" max="{{ $product->max_purchase_qty}}" id="pqty{{$cid}}" dcid="{{ $product->cart_id }}" name="pqty{{$cid}}" value="{{ $product->cart_qty }}" title="Qty" class="input-text qty text" size="4" readonly="" tabindex="0">
                                                                <input type="button" value="+" class="plus" id="plus-button{{ $cid }}" onclick="plus(this),updateCart('{{ $cid }}')"  tabindex="0">
                                                                <div class="clearfix"></div>
                                                         </div>
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
            </div>
            @endforeach

             <!-- Vegetables  -->
            

             <!-- ad banner section -->
            <div class="new_banner_sec home_section">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="img_cont">
                                <a href="#">
                                    <div class="cont">
                                        <h2>Peak Season<br> Fruits</h2>
                                        <p>Fulfill your daily dose of vitamins </p>
                                        <span class="btn btn-dark "> Shop Now </span>
                                    </div>
                                    <img src="{{ static_asset('frontend/new/assets/images/home/Mask%20Group%2027.jpg')}}"> </a>
                            </div>

                            <div class="img_cont">
                                <a href="#">
                                    <div class="cont">
                                        <h2>Look here for your <br>Bare necessities!</h2>
                                    </div>
                                    <img class="" src="{{ static_asset('frontend/new/assets/images/home/Mask%20Group%2028.jpg')}}"> </a>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="img_cont last">
                                <a href="#">
                                    <div class="cont">
                                        <p> Lorem ipsum dolor sit amet, consectetur adipisicing elit. Tempora similique excepturi obcaecati. </p>
                                        <span>  <img src="{{ static_asset('frontend/new/assets/images/home/play_store.jpg')}}">  </span>
                                    </div>
                                    <img class="bg" src="{{ static_asset('frontend/new/assets/images/home/Group%2010683.jpg')}}"> </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <!-- about section -->
            <section class="pb-4 home-text home_section ">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12  ">
                            <div class="sec_title mb-3 about_txt ">
                                <h4>ROZANA</h4>
                                 </div>
                                <div class="cont">
                                <p>Rozana.in is a P2P rural commerce startup that leverages Tech and Data Science to cater to unique local demands of 1 billion Indians outside the scope of online commerce. This silent tech revolution is not only empowering the end consumer but also working towards incubating over 10 million tech micro-entrepreneurs all over the country.</p>

                                <p>We enable micro-entrepreneurs to give wider, innovative and competitive offerings to end users. Our entrepreneurs use the platform to onboard customers, share latest deals and help them place online orders, making last mile delivery efficient.</p>

                                <p>Aspirations in rural India are on the rise and we are racing against time to ensure the effective delivery of essential services to these areas. Rozana.in aims to empower rural communities of the country and connect them to online commerce through a network of micro-entrepreneurs, and envisions to become the leading P2P rural commerce platform in India.</p>
                                </div>
                                 <!-- <a href="about-us.html" class="btn btn-base-1 mt-4 readmore">Read more</a> -->
                            </div>
                           
                        </div>
                    </div>
                </div>
            </section>

           

            <!-- FOOTER -->
            

            <div class="aiz-mobile-bottom-nav d-md-none fixed-bottom bg-white shadow-lg border-top">

                <div class="d-flex justify-content-around align-items-center">

                    <a href="index.html" class="text-reset flex-grow-1 text-center py-3 border-right bg-soft-primary">
                         <i class="fa fa-home la-2x"></i>
                   </a>

                    <a href="categories.html" class="text-reset flex-grow-1 text-center py-3 border-right ">
                        <span class="d-inline-block position-relative px-2">
                            <i class="fa fa-list-ul la-2x"></i>
                        </span>
                    </a>

                    <a href="cart.html" class="text-reset flex-grow-1 text-center py-3 border-right ">

                        <span class="d-inline-block position-relative px-2">

                            <i class="fa fa-shopping-cart la-2x"></i>
                                <span class="badge badge-circle badge-success position-absolute absolute-top-right" id="cart_items_sidenav">0</span>

                            
                        </span>

                     </a>


                    <a href="user-dashboard.html" class="text-reset flex-grow-1 text-center py-2">
                        <span class="avatar avatar-xs d-block mx-auto">
                            <img width="30px"  src="{{ static_asset('frontend/new/assets/images/user.png')}}">
                        </span>

                     </a>


                </div>

            </div>
        
            @if(Auth::check())
            <div class="modal fade" id="wallet_modal_new" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabels" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal modal-lg" id="modal-size" role="document">
            <div class="modal-content position-relative">
                <div class="modal-header">
                    <h5 class="modal-title strong-600 heading-5">{{ translate('Fill Address Form')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="" action="{{ route('phoneapi.updateaddressbycallcenter') }}" method="post">
                    @csrf
                    <div class="modal-body gry-bg px-3 pt-3">
                        

					<!-- <div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Name')}} <span class="error">*</span></label>
						<div class="col-lg-7">
							<input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="{{ translate('Name') }}">
							
							@if($errors->has('name'))
                            <div class="error  mr-top">{{ $errors->first('name') }}</div>
                        	@endif
						</div>
                        
					</div>
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Phone')}} <span class="error">*</span></label>
						<div class="col-lg-7">
							<input type="number" class="form-control"  name="phone" value="{{ old('phone') }}" placeholder="{{ translate('Phone') }}" required>
							
							@if($errors->has('phone'))
                            <div class="error  mr-top">{{ $errors->first('phone') }}</div>
                        	@endif
						</div>
                        
					</div>
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Email')}}</label>
						<div class="col-lg-7">
							<input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="{{ translate('Email') }}">
							
							@if($errors->has('email'))
                            <div class="error  mr-top">{{ $errors->first('email') }}</div>
                        	@endif
						</div>
                        
					</div> -->
					<div class="form-group row" id="subsubcategory">
						<label class="col-lg-2 control-label">{{translate('Address')}} <span class="error">*</span></label>
						<div class="col-lg-7">
                        <input type="text" class="form-control" name="address" value="{{ old('address') }}" placeholder="{{ translate('Address') }}" required>
						
						@if($errors->has('address'))
                            <div class="error  mr-top">{{ $errors->first('address') }}</div>
                        @endif
						</div>
                        
					</div>
					@php 
					
					$clusters = \App\Cluster::where('status',1)->select('state_id')->get();
					$state_ids = [];

					foreach($clusters as $key => $cluster){
						foreach(json_decode($cluster->state_id) as $kk => $state){
							$state_ids[] =$state;
						}
						
					}

					@endphp
                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}" />
					<div class="form-group row">
						<label class="col-lg-2 control-label">{{translate('State')}} <span class="error">*</span></label>
						<div class="col-lg-7">
							<input type="hidden" name="state" id="state" value="" />
							<select class="demo-select2 state_id" name="state_id" data-placeholder="Select State" id="state_id" onchange="loadList(this)" required>
								
								@foreach(\App\State::where('status',1)->where('country_id',99)->whereIn('id',array_unique($state_ids))->get() as $key => $state)
									<option value="{{ $state->id }}" @if(old('state')==$state->id) selected @endif>{{ $state->name }}</option>
								@endforeach
							</select>
							@if($errors->has('state'))
                            <div class="error  mr-top">{{ $errors->first('state') }}</div>
                        	@endif
						</div>
                        
					</div>

					<div class="form-group row">
						<label class="col-lg-2 control-label">{{translate('City/District')}} <span class="error">*</span></label>
						<div class="col-lg-7">
						<input type="hidden" name="city" id="city" value="" />
						<select class="form-control demo-select2 city_id" name="city_id" data-placeholder="Select City" id="city_id" onchange="loadList(this)" required>
								
							</select>
							@if($errors->has('city'))
                            <div class="error  mr-top">{{ $errors->first('city') }}</div>
                        	@endif
						</div>
                       
					</div>

					<div class="form-group row">
						<label class="col-lg-2 control-label">{{translate('Block/Taaluka')}} 
							<span class="error">*</span>
						</label>
						<div class="col-lg-7">
						<input type="hidden" name="block" id="block" value="" />
						<select class="form-control demo-select2" name="block_id" id="block_id" onchange="loadList(this)" required>
								<option value="">Select Block</option>
							</select>
							@if($errors->has('block_id'))
                            <div class="error  mr-top">{{ $errors->first('block_id') }}</div>
                        	@endif
						</div>
                       
					</div>

					<div class="form-group row">
						<label class="col-lg-2 control-label">{{translate('Gram Panchayat')}} 
							<!-- <span class="error">*</span> -->
						</label>
						<div class="col-lg-7">
						<input type="text" class="form-control" name="village" placeholder="Gram Panchayat" />
							@if($errors->has('village'))
                            <div class="error  mr-top">{{ $errors->first('village') }}</div>
                        	@endif
						</div>
                       
					</div>
                    
					<div class="form-group row">
						<label class="col-lg-2 control-label">{{translate('Pincode')}} <span class="error">*</span></label>
						<div class="col-lg-7">
							<select class="form-control pindata demo-select2" name="pincode" id="pincode_id" required>
								<option value="">Select Pincode</option>
							</select>
							
							@if($errors->has('pincode'))
                            <div class="error mr-top">{{ $errors->first('pincode') }}</div>
                       		 @endif
						</div>
                        
					</div>
                    
					<div class="form-group row">
						<label class="col-lg-2 control-label">{{translate('Zone')}}</label>
						<div class="col-lg-7">
							<input type="text" class="form-control zonedata" id="zone" name="zone" placeholder="{{ translate('Zone') }}" readonly>
                        </div>
                    </div>
					<!-- <div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Refferal Code')}}</label>
						<div class="col-lg-7">
							<input type="text" class="form-control blank_ref" id="referral_code" name="referral_code" placeholder="{{ translate('Refferal Code') }}">
							
						</div>
						
					</div>
					<div class="form-group">
                                           
					<label class="col-lg-2 control-label">{{  translate('PAN No.') }}</label>
					
					<div class="col-lg-7">
					<input type="text" class="form-control mb-3" placeholder="{{ translate('PAN No.')}}" name="pannumber" id="panNumber">
					<br />
					<p style="font-size: 12px;color:green;margin-top: -10px;">First five characters are letters (A-Z), next 4 numerics (0-9), last character letter (A-Z)</p>
					</div>
					@if($errors->has('pannumber'))
                        <div class="error">{{ $errors->first('pannumber') }}</div>
                    @endif
					</div>

				</div> -->

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-base-1">{{ translate('Confirm')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
      </div>
@endif

@endsection

