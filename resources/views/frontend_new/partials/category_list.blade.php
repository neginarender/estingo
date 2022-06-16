@foreach($categories as $category)
    <li class="category-nav-element" data-id="{{ $category->id }}">
        <a href="{{ route('category.products',['id'=>encrypt($category->id),'type'=>'category']) }}">
            <img class="cat-image lazyload" src="{{ static_asset('frontend/new/assets/images/placeholder.jpg') }}" data-src="{{ Storage::disk('s3')->url($category->icon) }}" width="30" alt="{{ translate($category->name) }}">
            <span class="cat-name">{{ translate($category->name) }}</span>
        </a>
        <div class="sub-cat-menu c-scrollbar">
            <div class="c-preloader">
                <i class="fa fa-spin fa-spinner"></i>
            </div>
            <div class="sub-cat-main row no-gutters"> 
        </div>
    </li>
@endforeach