
<div class="sub-cat-main row no-gutters">
    <div class="col-12">
        <div class="sub-cat-content">
            <div class="sub-cat-list">
                <div class="card-columns">
                   
                    @foreach ($subcategories as $subcategory)
               
                        <div class="card">
                            <ul class="sub-cat-items">
                                <li class="sub-cat-name"><a href="{{ route('category.products',['id'=>encrypt($subcategory->id),'type'=>'subcategory']) }}">{{ __($subcategory->name) }}</a></li>
                                @foreach ($subcategory->subSubCategories->data as $subsubcategory)
                                    
                                    <li><a href="{{ route('category.products',['id'=>encrypt($subsubcategory->id),'type'=>'subsubcategory']) }}">{{ __($subsubcategory->name) }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
