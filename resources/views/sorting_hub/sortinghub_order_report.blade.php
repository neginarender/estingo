@extends('layouts.app')

@section('content')
@php
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp
<!-- Basic Data Tables -->
<!--===================================================-->

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
<style>
    .form-control{
        border:1px solid #b7b7b7!important;
    }
</style>
<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <!-- <h3 class="panel-title pull-left pad-no">Today's Orders ( <?php echo date("d-m-Y"); ?> )</h3> -->
        <div class="pull-left clearfix">
        
        <form method="post" action="{{ route('sortinghuborders.showbydate') }}">
          @csrf
          <div class="box-inline pad-rgt pull-left" style="width:200px;">
                <label for="users-list-role">Sorting Hub</label>
                    <div class="" style="">
                    @if(Auth::user()->user_type!="admin")
                    <input type="hidden" name="sorting_hub_id" value="{{ Auth::user()->id }}" />
                    @endif
                                <select class="form-control" name="sorting_hub_id" @if(Auth::user()->user_type!="admin") disabled @endif>
                                    <option value="">Select</option>
                                    @foreach(\App\ShortingHub::all() as $key => $sorthub)
                                    <option value="{{ $sorthub->user_id }}" @if(Auth::user()->id==$sorthub->user_id || $sorthub->user_id==$sortId) selected @endif>{{ $sorthub->user->name }}</option>
                                    @endforeach
                                </select>             
                    </div>
              </div>

              <div class="box-inline pad-rgt pull-left">
                <label for="users-list-role">Select Date</label>
                    <div class="" style="">
                            <input type="date" class="form-control datepicker" id="start_date" name="start_date" autocomplete="off" value="{{ $from_date }}">
                    </div>
              </div>
              <div class="box-inline pad-rgt pull-left">
                <label for="users-list-role">End Date</label>
                    <div class="" style="">
                            <input type="date" class="form-control datepicker" id="end_date" name="end_date" autocomplete="off" value="{{ $to_date }}">
                    </div>
              </div>
              <div class="box-inline pad-rgt pull-left">
                <label for="users-list-role">Start Time</label>
                    <div class="" style="">
                           <input type="time" id="start_time" class="form-control" name="start_time" value="{{ $start_time }}">
                    </div>
              </div>
              <div class="box-inline pad-rgt pull-left">
                <label for="users-list-role">End Time</label>
                    <div class="" style="">
                           <input type="time" id="end_time" class="form-control" name="end_time" value="{{ $end_time }}">
                    </div>
              </div>
              <div class="box-inline pad-rgt pull-left"><br>
                  <button class="btn btn-success" type="submit">Apply Filters</button>
              </div>
          
        </form>
            
        </div>
    </div>
    <div class="panel-body">
       
        <table class="table table-striped" id="example" cellspacing="0" width="100%">
              <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Product ID')}}</th>
                    <th>{{translate('Product Name')}}</th>
                    <th>{{translate('Quantity')}}</th>
                    <th>{{translate('UOM')}}</th>
                    <th>{{translate('Distributors')}}</th>
                    <th>{{translate('Category')}}</th>
                    <th>{{translate('Order Id')}}</th>

                </tr>
            </thead>
            <tbody>
                @forelse($all_products as $key => $product)   
                @php 
                $pr = \App\Product::find($product->product_id);
                $product_name = (!is_null($pr)) ? $pr->name : "N/A";
                $mappingProduct = \App\MappingProduct::where(['sorting_hub_id'=>$sortId,'product_id'=>$product->product_id])->first();
                $distributorsIds = (!is_null($mappingProduct)) ? json_decode($mappingProduct->distributors) : [];
                if(!is_null($product)){
                    $productStock = \App\ProductStock::where('product_id',$product->product_id)->first();
                    $varaint = (!is_null($productStock)) ? $productStock->variant: "N/A";
                }
                @endphp
                        <tr>
                            <td>{{ $key+1}} <input type="hidden" value="{{ $product->orderIds }}" /></td>
                            <td>{{ $product->product_id }}</td>
                            <td>{{ $product_name }}</td>
                            <td>{{ $product->total_quantity }}</td>
                            <td>{{ $product->variation }}</td>
                            <td> @if(!empty($distributorsIds)) 
                                @foreach(\App\Distributor::whereIn('id',$distributorsIds)->get() as $key => $distributor)
                                <span class="badge badge-info">{{ $distributor->name }}</span>
                                <br />
                                @endforeach
                                    @else 
                                    N/A 
                                    @endif
                        </td>  
                        <td>{{$pr->category->name}}</td> 
                        <td>{{getOrderCodes($product->orderIds)}}</td>              
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center;"> No data found</td>
                           
                        </tr>
                        @endforelse
            </tbody>
        </table>
        
    </div>
</div>

@endsection


@section('script')
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#example').DataTable( {
                dom: 'Bfrtip',
                buttons: [
                    'excelHtml5', 'csvHtml5'
                ]
            } );
        } );
    </script>
@endsection
