@php
    $brands = array();
                                if(!empty(Cookie::get('pincode'))){ 
                                    $pincode = Cookie::get('pincode');
                                    $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
       
                                if(!empty($shortId)){
                                        $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->pluck('product_id')->all();
                                        $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
                                        $subcategoryIds = \App\Product::where('published',1)->whereIn('id',$productIds)->distinct()->pluck('subcategory_id')->all();
                                        $subsubcategoryIds = \App\Product::where('published',1)->whereIn('id',$productIds)->distinct()->pluck('subsubcategory_id')->all();
                                    }else{
                                        $categoryIds = array();
                                        $subcategoryIds = array();
                                        $subsubcategoryIds = array();
                                    }
                                    


                                }else{
                                    $categoryIds = array();
                                    $productIds = array();
                                    $subcategoryIds = \App\Product::where('published',1)->distinct()->pluck('subcategory_id')->all();
                                    $subsubcategoryIds = \App\Product::where('published',1)->distinct()->pluck('subsubcategory_id')->all();
                                   
                                }

                               
@endphp
<div class="sub-cat-main row no-gutters">
    <div class="col-12">
        <div class="sub-cat-content">
            <div class="sub-cat-list">
                <div class="card-columns">
                    @foreach ($category->subcategories as $subcategory)
                        <div class="card">
                            <ul class="sub-cat-items">
                            @if(in_array($subcategory->id,$subcategoryIds))
                                <li class="sub-cat-name"><a href="{{ route('products.subcategory', $subcategory->slug) }}">{{ __($subcategory->name) }}</a></li>
                                @foreach ($subcategory->subsubcategories as $subsubcategory)
                                    @if(in_array($subsubcategory->id,$subsubcategoryIds))
                                    <li><a href="{{ route('products.subsubcategory', $subsubcategory->slug) }}">{{ __($subsubcategory->name) }}</a></li>
                                    @endif
                                @endforeach
                            @endif
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
