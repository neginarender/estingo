@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <a href="{{ route('flash_deals.create')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add New Flash Deal Products')}}</a>
    </div>
</div>

<br>

<!-- Basic Data Tables -->
<!--===================================================-->
<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <h3 class="panel-title pull-left pad-no">{{translate('Flash Deals')}}</h3>
        <div class="pull-right clearfix">
            <form class="" id="sort_flash_deals" action="" method="GET">
                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        @php
                    $sortinghub = \App\ShortingHub::all();
                @endphp
                       <select class="form-control demo-select2" name="sorting_hub" id="sorting_hub" onchange="submit_form()">
                            <option value="">Select Sorting Hub</option>
                            @foreach($sortinghub as $key => $sorthub)
                            <option value="{{ $sorthub->user_id }}" @if($sorting_hub==$sorthub->user_id) selected @endif>{{ $sorthub->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name & Enter') }}">
                    </div>
                </div>
            </form> 

        </div>
    </div>
    <div class="panel-body">
        <table class="table res-table table-responsive mar-no" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    
                    <th>{{translate('Image')}}</th>
                    <th>{{ translate('Name') }}</th>
                    <th>{{ translate('Price') }}</th>
                    <th>{{ translate('Discount') }}</th>
                    <th>{{ translate('Status') }}</th>
                    
                </tr>
            </thead>
            <tbody>
                @if(!empty($products))
                @foreach($products as $key => $product)
                @php
                    if(!empty($shortId)){
                $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$product->id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();           
            }else{
                $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$product->id.'"')->latest('id')->first();
            }
                @endphp
                      <tr>
                          <td>{{ $key+1 }}</td>
                          <td><img src="{{ Storage::disk('s3')->url($product->thumbnail_img) }}" width="50" height="50" /></td>
                          <td>{{ $product->name }}</td>
                          <td>{{ single_price(price($product->id,$shortId)) }}</td>
                          <td>{{ substr($peer_discount_check->customer_discount,1,-1) }}%</td>
                          
                          <td><div class="btn-group dropdown">
                                <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                    {{translate('Actions')}} <i class="dropdown-caret"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    
                                    <li><a href="{{route('flash_deals.remove.product', ['sorting_hub'=>$sorting_hub,'product_id'=>$product->id])}}">{{translate('Delete')}}</a></li>
                                </ul>
                            </div></td>
                      </tr> 
                      @endforeach 
                      @endif   
             </tbody>
        </table>
        <div class="clearfix">
            <div class="pull-right">
                @if(count($products))
                {{ $products->appends(request()->input())->links() }}
                @endif
            </div>
        </div>
    </div>
</div>

@endsection


@section('script')
    <script type="text/javascript">
        function submit_form(){
            $("#sort_flash_deals").submit();
        }
    </script>
@endsection
