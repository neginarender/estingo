@extends('layouts.app')

@section('content')


<div class="row">
    <div class="col-lg-12 pull-right">
        <a href="{{ route('product-mapping.create')}}" class="btn btn-rounded btn-info pull-right">{{translate('Create Product Mapping')}}</a>
    </div>
</div>
<br>

<div class="panel">
    <!--Panel heading-->
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <h3 class="panel-title pull-left pad-no">{{ translate('Mapped Products') }}</h3>
        <div class="pull-right clearfix">
            <form class="" id="sort_products" action="" method="GET">
                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type & Enter') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="panel-body">
        <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th width="20%">{{translate('Name')}}</th>
                    <th>{{translate('Category')}}</th>
                    <th>{{translate('Price')}}</th>
                    <th>{{translate('Published')}}</th>
                    <th>{{translate('Added date')}}</th>
                    <th>{{translate('Options')}}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($products as $key => $product)
                    <tr>
                        <td>{{ ($key+1) + ($products->currentPage() - 1)*$products->perPage() }}</td>
                        <td>
                            <a href="{{ route('product', $product->slug) }}" target="_blank" class="media-block">
                                <div class="media-left">
                                    <img loading="lazy"  class="img-md" src="{{ my_asset($product->thumbnail_img)}}" alt="Image" style="width: 50px !important;">
                                </div>
                                <div class="media-body">{{ __($product->name) }}</div>
                            </a>
                        </td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ number_format($product->unit_price,2) }}</td>
                        <td>
                            <label class="switch">
                                <input value="{{ $product->id }}" disabled="" type="checkbox" <?php if($product->published == 1) echo "checked";?> >
                                <span class="slider round"></span></label>
                            </td>
                        <td>
                            {{ date('d M Y', strtotime($product->date)) }}
                        </td>
                        <td>
                            <div class="btn-group dropdown">
                                <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                    {{translate('Actions')}} <i class="dropdown-caret"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="{{route('mapped.product.edit', 'id' => $product->mapping_id)}}">{{translate('Edit')}}</a></li>
                                    <li>
                                        <a onclick="getMapModal('{{$product->mapping_id}}');" href="javascript:;">{{translate('View')}}</a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="clearfix">
            <div class="pull-right">
              
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="profile_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-content">

        </div>
    </div>
</div>

@endsection


@section('script')
    <script type="text/javascript">

    	function getMapModal(id){
		$.post('{{ route('mapped.product.modal') }}',{_token:'{{ @csrf_token() }}', id:id}, function(data){
                $('#profile_modal #modal-content').html(data);
                $('#profile_modal').modal('show', {backdrop: 'static'});
            });
    	}

    </script>
@endsection
