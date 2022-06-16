@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-sm-12">
       <a href="{{ route('sorting_hub.map_new_categories')}}" class="btn btn-rounded btn-info pull-right">{{translate('Map New Category')}}</a>
    </div>
</div>

<br>

<!-- Basic Data Tables -->
<!--===================================================-->
<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <h3 class="panel-title pull-left pad-no">{{translate('Categories')}}</h3>
        <!-- <div class="pull-right clearfix">
            <form class="" id="sort_categories" action="" method="GET">
                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name & Enter') }}">
                    </div>
                </div>
            </form>
        </div> -->
    </div>
    <div class="panel-body">
        <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Banner')}}</th>
                    <th>{{translate('Icon')}}</th>
                    <th>{{translate('Status')}}</th>
                   
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $key => $category)
                @php 
                    $cat = \App\Category::where('id',$category->category_id)->first();
                @endphp
                    <tr>
                        <td>{{ ($key+1) }}</td>
                        <td>{{__($cat->name)}}</td>
                        <td><img loading="lazy"  class="img-md" src="{{ my_asset($cat->banner) }}" alt="{{translate('banner')}}"></td>
                        <td><img loading="lazy"  class="img-xs" src="{{ my_asset($cat->icon) }}" alt="{{translate('icon')}}"></td>
                        
                        <td>
                            <label class="switch">
                                <input onchange="update_status(this)" value="{{ $category->category_id }}" type="checkbox" <?php if($category->status == 1) echo "checked";?> >
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="clearfix">
            <div class="pull-right">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script type="text/javascript">
        function update_status(el){
            if(el.checked){
                var status = 1;
            }
            else{
                var status = 0;
            }
            $.post('{{ route('sorting_hub.update_category_status') }}', {_token:'{{ csrf_token() }}', id:el.value, status:status}, function(data){
                if(data == 1){
                    showAlert('success', 'Categories Status updated successfully');
                }
                else{
                    showAlert('danger', 'Something went wrong');
                }
            });
        }
    </script>
@endsection
