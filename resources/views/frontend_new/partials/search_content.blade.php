@if(count($tags))
<div class="keyword">
    
        <div class="title">{{translate('Popular Suggestions')}}</div>
        <ul>
            @foreach($tags as $tag)
           <li><a href="{{ route('phoneapi.elasticsearch') }}?q={{ $tag }}">{{ $tag }} </a></li>
           @endforeach
        </ul>
   
</div>
@endif
{{-- @if(count($categories))
<div class="category">
    
        <div class="title">{{translate('Category Suggestions')}}</div>
        <ul>
            @foreach($categories as $key => $category)
                <li><a href="#">{{ translate($category->search) }}</a></li>
           @endforeach
        </ul>
  
</div>
@endif --}}
@if(count($products))
<div class="product">
    
        <div class="title">{{ translate('Products') }}</div>
        <ul>
            @foreach(array_slice($products,0,3) as $key => $product)
                <li>
                    <a href="{{ route('product.details',['id'=>encrypt($product->product_id)]) }}">
                        <div class="d-flex search-product align-items-center">
                            <div class="image" style="background-image:url('{{ my_asset($product->thumbnail_image) }}');">
                            </div>
                            <div class="w-100 overflow--hidden">
                                <div class="product-name text-truncate">
                                    {{ translate($product->name) }}
                                </div>
                                <div class="clearfix">
                                    <div class="price-box float-left">
                                        
                                    @if(Cookie::has('peer'))
                                    <span class="product-price strong-600">{{ single_price($product->base_price) }}</span>
                                    <del class="old-product-price strong-400">{{ single_price($product->stock_price) }}</del>
                                    @else
                                    <span class="product-price strong-600">{{ single_price($product->stock_price) }}</span>
                                    @endif
                                                                            
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                @endforeach
           
        </ul>
</div>
@endif

