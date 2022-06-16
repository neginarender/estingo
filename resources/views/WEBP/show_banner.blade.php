@extends('layouts.app')

@section('content')
@php

$all_files = glob("" .'public/uploads/webp/' . "{*.jpg,*.gif,*.png,*.webp}", GLOB_BRACE);
@endphp

    <div class="tab-base">

        <!--Nav Tabs-->
        <ul class="nav nav-tabs">
            <li class="active">
                <a data-toggle="tab" href="#demo-lft-tab-1" aria-expanded="true">{{ translate('Convert To webp Format') }}</a>
            </li>
      
        </ul>

        <!--Tabs Content-->
        <div class="tab-content">

            <div id="demo-lft-tab-1" class="tab-pane fade active in">
               <form method="post" action = "{{route('webp.convert')}}" enctype = "multipart/form-data" id = "webp">
               @csrf
                <div class="row">
                    <div class="col-sm-12">
                        <input type = "file" id = "file" name = "photo" class="btn btn-rounded btn-info pull-right">{{translate('Upload File')}}</a>
                    </div>
                </div>
                </form>

                <br>

                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{translate('WEBP')}}</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped table-bordered demo-dt-basic" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{translate('Webp')}}</th>
                                    <!-- <th>Size</th> -->
                                
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($all_files as $key => $value)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td><a href="{{asset($value)}}" download><img loading="lazy"  class="img-md" src="{{asset($value)}}" alt="Slider Image" ></a></td>
                                      
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
  
            <div id="demo-lft-tab-3" class="tab-pane fade">

                <div class="row">
                    <div class="col-sm-12">
                        <a onclick="add_banner_2()" class="btn btn-rounded btn-info pull-right">{{translate('Add New Banner')}}</a>
                    </div>
                </div>

                <br>

                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{translate('Home banner')}} ({{translate('Max 3 published')}})</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped table-bordered demo-dt-basic" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{translate('Photo')}}</th>
                                    <th>{{translate('Position')}}</th>
                                    <th>{{translate('Published')}}</th>
                                    <th width="10%">{{translate('Options')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Banner::where('position', 2)->get() as $key => $banner)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td><img loading="lazy"  class="img-md" src="{{ my_asset($banner->photo)}}" alt="banner Image"></td>
                                        <td>{{ translate('Banner Position ') }}{{ $banner->position }}</td>
                                        <td><label class="switch">
                                            <input onchange="update_banner_published(this)" value="{{ $banner->id }}" type="checkbox" <?php if($banner->published == 1) echo "checked";?> >
                                            <span class="slider round"></span></label></td>
                                        <td>
                                            <div class="btn-group dropdown">
                                                <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                                    {{translate('Actions')}} <i class="dropdown-caret"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li><a onclick="edit_home_banner_2({{ $banner->id }})">{{translate('Edit')}}</a></li>
                                                    <li><a onclick="confirm_modal('{{route('home_banners.destroy', $banner->id)}}');">{{translate('Delete')}}</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
            <div id="demo-lft-tab-4" class="tab-pane fade">

                <div class="row">
                    <div class="col-sm-12">
                        <a onclick="add_home_category()" class="btn btn-rounded btn-info pull-right">{{translate('Add New Category')}}</a>
                    </div>
                </div>

                <br>

                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{translate('Home Categories')}}</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped table-bordered demo-dt-basic" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{translate('Category')}}</th>
                                    <th>{{ translate('Status') }}</th>
                                    <th width="10%">{{translate('Options')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\HomeCategory::all() as $key => $home_category)
                                    @if ($home_category->category != null)
                                        <tr>
                                            <td>{{$key+1}}</td>
                                            <td>{{$home_category->category->name}}</td>
                                            <td><label class="switch">
                                                <input onchange="update_home_category_status(this)" value="{{ $home_category->id }}" type="checkbox" <?php if($home_category->status == 1) echo "checked";?> >
                                                <span class="slider round"></span></label></td>
                                            <td>
                                                <div class="btn-group dropdown">
                                                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                                        {{translate('Actions')}} <i class="dropdown-caret"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                        <li><a onclick="edit_home_category({{ $home_category->id }})">{{translate('Edit')}}</a></li>
                                                        <li><a onclick="confirm_modal('{{route('home_categories.destroy', $home_category->id)}}');">{{translate('Delete')}}</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
            <div id="demo-lft-tab-5" class="tab-pane fade">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">{{translate('Top 10 Information')}}</h3>
                    </div>

                    <!--Horizontal Form-->
                    <!--===================================================-->
                    <form class="form-horizontal" action="{{ route('top_10_settings.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-sm-3" for="url">{{translate('Top Categories (Max 10)')}}</label>
                                <div class="col-sm-9">
                                    <select class="form-control demo-select2-max-10" name="top_categories[]" multiple>
                                        @foreach (\App\Category::all() as $key => $category)
                                            <option value="{{ $category->id }}" @if($category->top == 1) selected @endif>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3" for="url">{{translate('Top Brands (Max 10)')}}</label>
                                <div class="col-sm-9">
                                    <select class="form-control demo-select2-max-10" name="top_brands[]" multiple>
                                        @foreach (\App\Brand::all() as $key => $brand)
                                            <option value="{{ $brand->id }}" @if($brand->top == 1) selected @endif>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer text-right">
                            <button class="btn btn-purple" type="submit">{{translate('Save')}}</button>
                        </div>
                    </form>
                    <!--===================================================-->
                    <!--End Horizontal Form-->

                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')

<script type="text/javascript">
document.getElementById("file").onchange = function() {
    document.getElementById("webp").submit();
};
</script>

@endsection
