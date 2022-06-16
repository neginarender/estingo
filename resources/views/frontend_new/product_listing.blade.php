@extends('frontend_new.layouts.app')
@section('content')
<section class="  py-0 py-md-4 gry-bg">
            <div class="container-fluid sm-px-0">
                  <div class="row">
                        <!-- filter sidebar  -->
                    <!-- <div class="col-xl-3 side-filter d-xl-block">
                        <div class="filter-overlay filter-close"></div>
                        <div class="filter-wrapper c-scrollbar">
                          <div class="filter-title d-flex d-xl-none justify-content-between pb-3 align-items-center">
                                <h3 class="h6">Filters</h3>
                                <button type="button" class="close filter-close">
                                  <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="bg-white sidebar-box scroll-f mb-3">
                                <div class="box-title  ">
                                    <span>Categories</span>
                                </div>
                                <div class="box-content pt-0">
                                    <div class="category-filter">
                                        <ul>
                                            <li ><a href="#" class="bold"><i class="fa fa-angle-left"></i> All Categories</a></li>
                                            <li ><a href="#" class="bold">Grocery &amp; Staple</a></li>
                                            <li ><a href="">Pulses</a></li>
                                            <li ><a href="">Atta & Other Flours</a></li>
                                             <li ><a href="">Edible Oils & Ghee</a></li>
                                            <li ><a href="">Rice</a></li>
                                             <li ><a href="">Pulses</a></li>
                                            <li ><a href="">Atta & Other Flours</a></li>
                                             <li ><a href="">Edible Oils & Ghee</a></li>
                                            <li ><a href="">Rice</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white sidebar-box mb-3">
                                <div class="box-title ">
                                   <span>  Price range  </span>
                                </div>
                                <div class="box-content">
                                    <div class="range-slider-wrapper mt-3">
                                         <div  id="slider-range" data-range-value-min="4" data-range-value-max="9600"></div>
                                         <div class="row mt-3">
                                            <div class="col-6">
                                                <span class="range-slider-value value-low" data-range-value-low="4" id="input-slider-range-value-low">{{$pricerange->min_price}}</span>
                                            </div>

                                            <div class="col-6 text-right">
                                                <span class="range-slider-value value-high" data-range-value-high="4000" id="input-slider-range-value-high">{{$pricerange->max_price}}</span>
                                            </div>
                                         </div>
                                    </div>
                                </div>
                            </div>

                                  
                          @foreach($filterbyvolume as $filter)
                                 
                            <div class="bg-white sidebar-box scroll-f mb-3">
                                <div class="box-title t ">
                                   <span>Filter by {{$filter->name}} </span>
                                </div>
                                <div class="box-content">
                                    
                                    <div class="filter-checkbox">


                                   @foreach($filter->values as $row)
                                        <div class="checkbox">
                                            <input type="checkbox" id="attribute_1"  name="" value="1 l"  >
                                            <label for="attribute_1"  > <span class="ml-2">{{$row}}</span> </label>
                                        </div>
                                    @endforeach
                                         
                      
                                    </div>
                                </div>
                            </div>
                           @endforeach
                        
                             <div class="bg-white sidebar-box scroll-f mb-3">
                                <div class="box-title t ">
                                   <span> Filter by Tablets </span>
                                </div>
                                <div class="box-content">
                                    
                                    <div class="filter-checkbox">
                                        <div class="checkbox">
                                            <input type="checkbox" id="attribute_21"  name="" value="1 l"  >
                                            <label for="attribute_21"  > <span class="ml-2">l00 n</span> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="" class="btn btn-styled btn-block btn-base-4">Clear all filters</a>
                        </div>
                    </div> -->
                    <!-- products list -->
                    <div class="col-xl-12">
                        <div class="sort-by-bar row no-gutters   px-3 pt-2">
                            <div class="col-xl-6   ">
                                  <div class="d-block w-100">  
                                    <ul class="breadcrumb  ">
                                       <li><a href="index.html">Home</a></li>
                                       <li><a href="categories.html">All Categories</a></li>
                                         <li class="active"><a href="#">Grocery &amp; Staple</a></li>
                                   </ul>
                               </div>
                               <div class="d-flex d-md-none w-100"> 
                                <div class="sort-by-box flex-grow-1">
                                    <div class="form-group">
                                        <div class="search-widget">
                                            <input class="form-control input-lg" type="text" name="q" placeholder="Search products" value="">
                                            <button type="submit" class="btn-inner">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-xl-none ml-3 form-group">
                                    <button type="button" class="btn p-1 btn-sm" id="side-filter">
                                        <i class="fa fa-filter fa-2x"></i>
                                    </button>
                                </div>
                              </div>
                           </div>
                              <!-- <div class="col-xl-5 offset-xl-1">
                                <div class="row no-gutters">
                                   <div class="col-6">
                                        <div class="sort-by-box px-1">
                                            <div class="form-group">
                                                <label>Sort by</label>
                                                 <select class="form-control sortSelect" data-minimum-results-for-search="Infinity" name="sort_by" onchange=" ">
                                                     <option value="1" selected="">Newest</option>
                                                     <option value="3">Price low to high</option>
                                                     <option value="4">Price high to low</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div> -->
                                     <!-- <div class="col-6">
                                        <div class="sort-by-box px-1">
                                            <div class="form-group">
                                                <label>Brands</label>
                                                <select class="form-control sortSelect" data-placeholder="All Brands" name="brand" onchange="">
                                                    <option value="">All Brands</option>
                                                     <option value="Organic-Soul"  >Organic Soul</option>
                                                     <option value="Gourmet-Jar">Gourmet Jar</option>
                                                      <option value="Fresh-Cartons">Fresh Cartons</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                       </div>
                       <div class="products-box-bar p-2 pt-0   product_listing">
                         <div class="row sm-no-gutters gutters-4">
                             @forelse($products as $key => $product)
                             @php $cid = $product->category_id.$key.$product->product_id;@endphp  
                           <div class="col-xxl-2 col-xl-2 col-lg-2 col-md-2 col-6  ">
                              <div class="product-card-2 shop-cards mx-0 bg-white alt-box my-2 ">
                                 <div class="position-relative overflow-hidden">
                                    <a href="{{ route('product.details',['id'=>encrypt($product->product_id)]) }}" class="d-block product-image   text-center" tabindex="0">
                                        <img class="img-fit ls-is-cached lazyloaded" src="{{ Storage::disk('s3')->url($product->thumbnail_image) }}"   alt="Fortune Soya Health Refined Soyabean Oil - 5L">
                                    </a>
                                </div>
                                <div class="  prod_info mb-4">
                                    <h2 class="product-title p-0"> 
                                        <a href="{{ route('product.details',['id'=>encrypt($product->product_id)]) }}" class="text-truncate" tabindex="0">{{ translate($product->name)}}</a>
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
                                    <input type="hidden" name="id" value="{{ $product->product_id }}">  
                                    <input type="hidden" name="variant" value="{{ $product->variant }}">   
                                    <input type="hidden" id="max_purchase_qty{{$cid}}" name="max_purchase_qty" value="{{ $product->max_purchase_qty }}">     
                                </form>
                                @php 
                                $price = $product->stock_price;
                                $del_price = $product->stock_price;

                                if(Cookie::has('peer')){
                                    $price = $product->base_price;
                                }
                                @endphp

                                 <div class="price-box p-0 pt-0">
                                    <span class="product-price strong-600">{{ single_price($price) }}</span>
                                        @if(Cookie::has('peer'))
                                        <del class="old-product-price strong-400">{{ single_price($del_price) }}</</del>
                                        @endif

                                        @if(4>0)

                                        <div class="quantity buttons_added new" id="button-group{{$cid}}">
                                        <div class="cart_loader" ><div class="spinner-border text-success" role="status"></div></div>
                                                <input type="button" value="Add" class="quant_add_btn plus @if($product->cart_qty>0)display-none @endif" onclick="plus(this),addToCart('{{ $cid }}')" id="btn_add{{$cid}}"  tabindex="0">
                                                <input type="button" value="-" class="minus" onclick="minus(this),updateCart('{{ $cid }}')" tabindex="0">
                                                <input type="number" step="1" min="0" max="{{ $product->max_purchase_qty}}" id="pqty{{$cid}}" dcid="{{ $product->cart_id }}" name="pqty{{$cid}}" value="{{ $product->cart_qty }}" title="Qty" class="input-text qty text" size="4" readonly="" tabindex="0">
                                                <input type="button" value="+" class="plus" id="plus-button{{ $cid }}" onclick="plus(this),updateCart('{{ $cid }}')"  tabindex="0">
                                                <div class="clearfix"></div>
                                            </div>
                                                    @else

                                                           <div class="quantity buttons_added new" >
                                                            <div class="cart_loader" ><div class="spinner-border text-success" role="status"></div></div>
                                                               <input type="button" value="Out Of Stock" class="quant_add_btn  plus bg-red"  id="btn_add{{$cid}}"  tabindex="0" disabled="" >

                                                                <input type="button" value="-" class="minus" tabindex="0">
                                                           
                                                               
                                                                <div class="clearfix"></div>
                                                        </div>


                                                        @endif
                                    </div>
                              </div>
                           </div>
                           @empty
                           <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12  ">
                              <div class="product-card-2 shop-cards mx-0 bg-white alt-box my-2 ">
                                 <div class="position-relative overflow-hidden" style="padding: 21px;text-align: center;font-size: 20px;">
                                     <span>No Products Found</span>
                                     <i class="fa-solid fa-meh"></i>
                                 </div>
                            </div>
                            </div>
                          
                          @endforelse
                        </div>
                       </div>
                       <div class="products-pagination  p-3">
                            <nav aria-label="Center aligned pagination">
                                <ul class="pagination justify-content-center">
                                    <nav>
                                      <ul class="pagination">
                                        <li class="page-item disabled" aria-disabled="true" aria-label="« Previous">
                                             <!-- <span class="page-link" aria-hidden="true">‹</span> -->
                                            <a class="page-link" href="{{ route('category.products',['id'=>encrypt($id),'type'=>$type]) }}" rel="prev" aria-label="prev »"><</a>

                                          </li>
                                          <!-- <li class="page-item active" aria-current="page"><span class="page-link">1</span></li> -->
                                            @php $activepage = 1;@endphp
                                          @for($i=$meta->current_page;$i<=$meta->last_page;$i++)
                                          
                                           <li class="page-item @if($activepage==$i) active @endif" aria-current="page"><a class="page-link" href="{{ route('category.products',['id'=>$id,'type'=>$type]) }}?page={{$i}}">{{$i}}</a></li>
                                
                                           @endfor

                                           <li class="page-item disabled">
                                              <a class="page-link" href="{{ route('category.products',['id'=>encrypt($id),'type'=>$type]) }}" rel="next" aria-label="Next »">›</a>
                                          </li>
                                        </ul>
                                    </nav>

                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
             
            </div>
        </section>
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