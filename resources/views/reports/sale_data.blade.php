@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">
<div class="row">
    <div class="col-md-10">
        <?php 
            $newdate = date('Y-m-d', strtotime('-7 days'));
            $from_date = date('Y-m-d'); 
            if(empty($start_date))
            {
               $start_date = $newdate;
            }else{
               $start_date = $start_date;
            }

            $to_date = date('Y-m-d'); 
            if(empty($end_date))
            {
               $end_date = $from_date;
            }else{
               $end_date = $end_date;
            }

        ?>
        <form class="" action="{{ route('sale_data.index') }}" method="GET">
            
            <div class="box-inline pad-rgt pull-left" style="margin-left: 12px">
                <label for="users-list-role">Start Date</label>
                    <div class="" style="">
                            <input type="date" class="datepicker" id="start_date" name="start_date" autocomplete="off" value="<?php echo $start_date; ?>">
                    </div>
              </div>

              <div class="box-inline pad-rgt pull-left">
                <label for="users-list-role">End Date</label>
                    <div class="" style="">
                            <input type="date" class="datepicker" id="end_date" name="end_date" autocomplete="off" value="<?php echo $end_date; ?>">
                    </div>
              </div>

			<div class="box-inline mar-btm pad-rgt">
                 {{ translate('Sort by Sorting Hub') }}: 
                 <div class="select">
                     <select id="demo-ease" class="demo-select2" name="sorting_id" required>
                           @foreach (\App\ShortingHub::where('status', 1)->get() as $keyn => $sh)
                            @php 
                                $names = \App\User::where('id', $sh->user_id)->select('name')->first();
                            @endphp    
                             <option value="{{ $sh->user_id }}" <?php if(isset($sorting_hub_id)){if($sh->user_id == $sorting_hub_id){ echo "selected";}} ?>>{{$names->name}}</option>
                         @endforeach
                     </select>
                 </div>
            </div>
            <!-- <div class="box-inline mar-btm pad-rgt">
                 {{ translate('Sort by Category') }}:
                 <div class="select">
                     <select id="demo-ease" class="demo-select2" name="category_id" required>
                         @foreach (\App\Category::all() as $key => $category)
                             <option value="{{ $category->id }}" <?php //if($category->id == $categoryid) echo "selected"; ?>>{{ __($category->name) }}</option>
                         @endforeach
                     </select>
                 </div>
            </div> -->
            <button class="btn btn-default" type="submit">{{ translate('Filter') }}</button>
        </form>
    </div>

    <div class="col-md-2">
                <form method="GET" action="{{route('report.sale_data_export')}}" name="sale_data_export" id="sale_data_export">
                    <input type="date" class="datepicker" id="start_date" name="start_date" autocomplete="off" value="<?php echo $start_date; ?>" style="display: none">
                    <input type="date" class="datepicker" id="end_date" name="end_date" autocomplete="off" value="<?php echo $end_date; ?>" style="display: none">
                    <input type="hidden" name="sortinghubid" id="sortinghubid" value="<?php if(isset($sorting_hub_id)){ echo $sorting_hub_id;}?>">
                    <input type="submit" name="submit" id="submit" value="Export" class="btn btn-rounded btn-info pull-right" style="float:right;margin-right: 30px;" >
                </form>
            </div>
        </div>
    <div class="row">
             
    </div>

    <div class="col-md-12">
        <div class="panel">
            <!--Panel heading-->
            <div class="panel-heading">
                <h3 class="panel-title">{{ translate('Sale data report') }}</h3>

            </div>



            <!--Panel body-->
            <div class="panel-body">
                <div class="table-responsive">
                    <!-- <table class="table table-striped mar-no demo-dt-basic"> -->
                         <table class="table table-striped" id="example" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>{{ translate('Sno.') }}</th>
								<th>{{ translate('Date & Time') }}</th>
								<th>{{ translate('Order No.') }}</th>
								<th>{{ translate('Sorting Hub') }}</th>

                                <th>{{ translate('SKU Code') }}</th>
                                <th>{{ translate('SKU Name') }}</th>
                                <th>{{ translate('Customer Name') }}</th>
                                <th>{{ translate('Mobile No.') }}</th>

                                <th>{{ translate('Address') }}</th>
                                <th>{{ translate('MRP') }}</th>
                                <th>{{ translate('Buying Price') }}</th>
                                <th>{{ translate('Selling Price') }}</th>

                                <th>{{ translate('Weight') }}</th>
                                <th>{{ translate('Weight Unit') }}</th>
                                <th>{{ translate('HSN Code') }}</th>
                                <th>{{ translate('GST%') }}</th>

                                <th>{{ translate('Margin') }}</th>
                                <th>{{ translate('Peer Commission') }}</th>
                                <th>{{ translate('Master Commission') }}</th>
                                <th>{{ translate('Rozana Margin') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $key => $order)
                            @php
                                $sku = \App\ProductStock::where('product_id', $order->product_id)->first();
                                $product_detail = \App\Product::where('id', $order->product_id)->first();
                                $product_mapped = \App\MappingProduct::where('product_id', $order->product_id)->first();
                                
                            @endphp
                                <tr>
                                    <td>{{ __($order->id) }}</td>
									<td>{{ (date('d-m-Y H:i:s',strtotime($order->created_at))) }}</td>
									<td>{{  __($order->code) }}</td>
									<td>
                                        @php
                                            $sortingHub = \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $order->shipping_pin_code . '"]\')')->first();
                                            if(!empty($sortingHub)){
                                              echo  @$sortingHub->user->name;

                                            }else{
                                                echo "NA";
                                            }
                                        @endphp
                                    </td>

                                    <td>{{ $sku['sku'] }}</td>
                                    <td>{{ $product_detail['name']}}</td>
                                    <td>{{ json_decode($order->shipping_address)->name }}</td>
                                    <td>{{ json_decode($order->shipping_address)->phone }}</td>

                                    <td>{{ json_decode($order->shipping_address)->address }}</td>
                                    <td>₹ {{ ($sku['price']) }}</td>
                                    <td>
                                         @if($product_mapped['purchased_price'] == 0)
                                            ₹ {{ ($product_detail['purchase_price']) }}
                                        @else
                                            ₹ {{ ($product_mapped['purchased_price']) }}
                                        @endif
                                    </td>
                                    <td>

                                        {{ single_price($order->price/$order->quantity - $order->peer_discount/$order->quantity) }}
                                        
                                        <!-- @if($product_mapped['selling_price'] == 0)
                                            ₹ {{ ($product_detail['stock_price']) }}
                                        @else
                                            ₹ {{ ($product_mapped['selling_price']) }}
                                        @endif -->
                                    </td>

                                    <td>@if (@$product_detail['choice_options'] != null)
                                            @foreach (json_decode($product_detail['choice_options']) as $key => $choice)
                                                @foreach ($choice->values as $key => $value)
                                                    {{ $value }}
                                                @endforeach
                                                {{'/'}}                                         
                                            @endforeach
                                        @endif
                                    </td>
                                    <td><?php if (@$product_detail['choice_options'] != null){
                                            $arr = array();
                                            foreach (json_decode($product_detail['choice_options']) as $key => $choice){
                                                $name = App\Attribute::where('id',$choice->attribute_id)->first()->name;
                                                array_push($arr,$name);
                                            }
                                            echo implode(' /',$arr);
                                        }
                                        ?>
                                        
                                    </td>
                                    <td>{{ $product_detail['hsn_code']}}</td>
                                    <td>
                                        {{ $product_detail['tax'] }}
                                    </td>

                                    <td>{{ __($order->sub_peer) }}</td>
                                    <td>{{ __($order->master_peer) }}</td>
                                    <td>{{  __($order->orderrozana_margin) }}</td>
                                    <td>{{  __($order->order_margin) }}</td>

                                    
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                     <div class="clearfix">
                        <div class="pull-right">
                            {{ $orders->appends(request()->input())->links() }}
                        </div>
                    </div>

        
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
 <script type="text/javascript">
        $(document).ready(function() {
            $('#example').DataTable( {
                "bPaginate": false,
            "bLengthChange": false,
            "bFilter": true,
            "bInfo": false,
            "bAutoWidth": false,
                 // "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
                 "scrollX":true,
                 // "paging": false,
                //"pageLength": 50,
               // "lengthMenu": [[20, 25, 50, -1], [20, 25, 50, "All"]],
                // "pagingType": "full_numbers"
                // dom: 'lBfrtip',
                // buttons: [
                //     'excelHtml5', 'csvHtml5'
                // ]
            } );
        } );

    </script>
@endsection