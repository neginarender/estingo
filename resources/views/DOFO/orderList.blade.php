
@extends('layouts.app')

@section('content')
@php
    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
@endphp
<!-- Basic Data Tables -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.4.1/css/buttons.dataTables.min.css">

<!--===================================================-->
<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <h3 class="panel-title pull-left pad-no">{{translate('Orders')}}</h3>
        <div class="pull-right clearfix">
            <form class="" id="sort_orders" action="" method="GET">
                
                <div class="box-inline pad-rgt pull-left">
                    <div class="select" style="min-width: 200px;">
                        <select class="form-control demo-select2" name="delivery_status" id="delivery_status" onchange="sort_orders()">
                            <option value="">{{translate('Filter by Delivery Status')}}</option>
                            <option value="pending"   @isset($delivery_status) @if($delivery_status == 'pending') selected @endif @endisset>{{translate('Pending')}}</option>
                            <option value="on_review"   @isset($delivery_status) @if($delivery_status == 'on_review') selected @endif @endisset>{{translate('On review')}}</option>
                            <option value="on_delivery"   @isset($delivery_status) @if($delivery_status == 'on_delivery') selected @endif @endisset>{{translate('On delivery')}}</option>
                            <option value="delivered"   @isset($delivery_status) @if($delivery_status == 'delivered') selected @endif @endisset>{{translate('Delivered')}}</option>
                        </select>
                    </div>
                </div>
                <div class="box-inline pad-rgt pull-left">
                    <div class="select" style="min-width: 200px;">
                        <select class="form-control demo-select2" name="pay_type" id="pay_type" onchange="sort_orders()">
                            <option value="">{{translate('Filter by Payment Method')}}</option>
                            <option value="cash_on_delivery"   @isset($pay_type) @if($pay_type == 'cash_on_delivery') selected @endif @endisset>{{translate('Cash On Delivery')}}</option>
                            <option value="letzpay_payment"   @isset($pay_type) @if($pay_type == 'letzpay_payment') selected @endif @endisset>{{translate('Letzpay Payment')}}</option>
                             <option value="razorpay"   @isset($pay_type) @if($pay_type == 'razorpay') selected @endif @endisset>{{translate('Razorpay Payment')}}</option>
                            <option value="wallet"   @isset($pay_type) @if($pay_type == 'wallet') selected @endif @endisset>{{translate('Wallet')}}</option>
                            
                        </select>
                    </div>
                </div>
                <div class="box-inline pad-rgt pull-left">
                    <div class="select" style="min-width: 200px;">
                        <select class="form-control demo-select2" name="payment_type" id="payment_type" onchange="sort_orders()">
                            <option value="">{{translate('Filter by Payment Status')}}</option>
                            <option value="paid"  @isset($payment_status) @if($payment_status == 'paid') selected @endif @endisset>{{translate('Paid')}}</option>
                            <option value="unpaid"  @isset($payment_status) @if($payment_status == 'unpaid') selected @endif @endisset>{{translate('Un-Paid')}}</option>
                        </select>
                    </div>
                </div>



                <div class="box-inline pad-rgt pull-left">

                    <div  style="min-width: 150px;">

                        <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%" >

                            <i class="fa fa-calendar"></i>&nbsp;

                            <span></span> <i class="fa fa-caret-down"></i>

                        </div>

                    </div>

                </div>

                 <input type="hidden" class="form-control" name="dateRangeStart">

                <input type="hidden" class="form-control" name="dateRangeEnd">

                

                @if(@$days_between != null && @$endDayFromCurrentDate == null)

                <input type="hidden" name="start" value="{{$days_between}}">

                @endif

               

                <input type="hidden" name="endDate" value="{{@$endDayFromCurrentDate}}">

                <input type="hidden" name="startDate" value="{{@$startDayFromCurrentDate}}">
                
            </form>
           
        </div>
    </div>
    <div class="panel-body">
     <a class="dt-button buttons-excel buttons-html5" tabindex="0" aria-controls="example" href="{{route('DOFO.orders-download')}}"><span>Excel</span></a>
     <a class="dt-button buttons-excel buttons-html5" tabindex="0" aria-controls="example" href="javascript:void(0)" onclick = "deleteOrder()"><span>Delete Order</span></a>
        <table class="table table-striped" id="example" cellspacing="0" width="100%">
             <!-- <table class="table table-striped res-table mar-no" id="example" cellspacing="0" width="100%"> -->
            <thead>
                <tr>
                <th><input type="checkbox" onclick="toggle(this);" name="delete_button"></th>
                    <th>#</th>
                    <th>{{translate('Order Code')}}</th>
                    <th>{{translate('Num. of Products')}}</th>
                    <th>{{translate('Customer')}}</th>
                    <th>{{translate('Address')}}</th>
                    <th>{{translate('Pin Code')}}</th>
                    <th>{{translate('Order Date')}}</th>
                    @if(in_array(Auth::user()->user_type, ['admin']))
                    <th>{{translate('Sorting HUB')}}</th>
                    @endif
                    @if(Auth::user()->staff!='')
                        @if(Auth::user()->staff->role->name == "Sorting Hub" || Auth::user()->staff->role->name == "Sorting Hub Manager")
                        <th>{{translate('Assign Order')}}</th>
                        @endif
                    @endif
                    <th>{{translate('Amount')}}</th>
                    <th>{{translate('Delivery Status')}}</th>
                    <th>{{translate('Delivery Boy')}}</th>
                    <th>{{translate('Payment Method')}}</th>
                    <th>{{translate('Payment Status')}}</th>
                    @if ($refund_request_addon != null && $refund_request_addon->activated == 1)
                        <th>{{translate('Refund')}}</th>
                    @endif
                    <th width="10%">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
            @php 
            $total_today_amount=0;
            $refund_amount = 0;
            @endphp
                @foreach ($orders as $key => $order_id)
                    @php
                        $order = \App\Order::find($order_id->id);
                        $refund_amount += \App\RefundRequest::where(array('refund_status'=>1,'order_id'=>$order_id->id))->sum('refund_amount');
                        $total_today_amount+=$order_id->grand_total;
                    @endphp
                    @if($order != null)
                          
                        <tr>
                        <td><input type="checkbox" name="delete_dofo" value = {{$order_id->id}}></td>
                            <td>
                                {{ ($key+1)}}
                            </td>
                            <td>
                                {{ $order->code }} @if($order->viewed == 0) <span class="pull-right badge badge-info">{{ translate('New') }}</span> @endif
                            </td>
                            <td>
                                <!-- {{ count($order->orderDetails->where('seller_id', $admin_user_id)) }} -->
                                {{ $order->orderDetails->where('order_id', $order_id->id)->sum('quantity') }}
                            </td>
                            <td>
                                @if ($order->user != null)
                                    {{ $order->user->name }}
                                @else
                                    Guest ({{ $order->guest_id }})
                                @endif
                            </td>
                            
                            <td>
                            @php
                            $address = json_decode($order->shipping_address);
                            @endphp
                            {{$address->address}}
                            </td>

                            <td>
                            {{$order->shipping_pin_code}}
                            </td>
                            <td>
                            {{date('l, F d y h:i:s A', $order->date)}}
                            </td>
                            @if(Auth::user()->staff!='')
                                @if(Auth::user()->staff->role->name == "Sorting Hub" || Auth::user()->staff->role->name == "Sorting Hub Manager")
                                            @php
                                            $sortinghubid = (Auth::user()->staff->role->name == "Sorting Hub Manager") ? Auth::user()->sortinghubmanager->sorting_hub_id : Auth::user()->id;
                                            $getDeliveryBoy = \App\DeliveryBoy::where('sorting_hub_id',$sortinghubid)->get();
                                            $getAssignedBoy = \App\AssignOrder::where('order_id',$order->id)->first('delivery_boy_id');
                                            @endphp
                                            <input type="hidden" class="order_id" value="{{$order->id}}">
                                            <td>
                                            <select class="form-control demo-select2 assign_order"  data-minimum-results-for-search="Infinity"  @php echo ($delivery_status == 'delivered')?"disabled":"" @endphp>
                                                <option value="">{{translate('Select Delivery')}}</option>
                                                @foreach($getDeliveryBoy as $key=>$value)
                                                <?php
                                                $delivery_boy_name = \App\User::where('id',$value['user_id'])->first('name');
                                                ?>
                                                <option value="<?php echo $value['id']; ?>" @php  echo $getAssignedBoy['delivery_boy_id'] == $value['id']?"selected":"";  @endphp order_id = {{$order->id}}><?php  echo $delivery_boy_name['name']; ?></option>
                                                @endforeach
                                                
                                            </select>
                                            </td>
                                        @endif
                            @endif

                            @if(in_array(Auth::user()->user_type, ['admin']))
                            <td>
                            @php
                            $sortingHub = \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $order->shipping_pin_code . '"]\')')->first();
                            if(!empty($sortingHub)){
                              echo  @$sortingHub->user->name;

                            }else{
                                echo "Not Available";
                            }
                            @endphp
                            </td>
                            @endif
                            <td>
                            
                                <!-- {{ single_price($order->orderDetails->where('order_id', $order_id->id)->sum('price') + $order->orderDetails->where('order_id', $order_id->id)->sum('tax')) }} -->
                                {{ single_price($order->orderDetails->where('order_id', $order_id->id)->where('delivery_status','!=', 'return')->sum('price') + $order->orderDetails->where('order_id', $order_id->id)->sum('tax') - $order->orderDetails->where('order_id', $order_id->id)->where('delivery_status','!=', 'return')->sum('peer_discount'))}}
                            </td>
                            <td>
                                 {{-- {{ ucfirst(str_replace('_', ' ', $order->order_status)) }} --}}
                                    <select class="form-control demo-select2" name="delivery_status"  id="delivery_status" onchange="change_status('delivery_status',this)">
                                        <option value="pending" order_id =  "{{$order->id}}" @isset($order->order_status) @if($order->order_status == 'pending') selected @endif @endisset>{{translate('Pending')}}</option>
                                        <option value="on_review" order_id =  "{{$order->id}}"  @isset($order->order_status) @if($order->order_status == 'on_review') selected @endif @endisset>{{translate('On review')}}</option>
                                        <option value="on_delivery" order_id =  "{{$order->id}}"  @isset($order->order_status) @if($order->order_status == 'on_delivery') selected @endif @endisset>{{translate('On delivery')}}</option>
                                        <option value="delivered" order_id =  "{{$order->id}}"  @isset($order->order_status) @if($order->order_status == 'delivered') selected @endif @endisset>{{translate('Delivered')}}</option>
                                    </select>
                                    {{$order->updated_at}}
                                <!-- @if(($order->orderDetails->first()['delivery_status']=='pending')&&($order->payment_status == 'paid')&&($order->payment_type == 'letzpay_payment')) 
                                    Under Processing

                                @elseif(($order->orderDetails->first()['delivery_status']=='pending')&&($order->payment_status == 'unpaid')&&($order->payment_type == 'letzpay_payment'))   
                                     Payment Failed

                                @elseif(($order->orderDetails->first()['delivery_status']=='pending')&&($order->payment_status == 'unpaid')&&($order->payment_type == 'cash_on_delivery'))   
                                     Under Processing

                                @else
                                     {{ ucfirst(str_replace('_', ' ', $order->orderDetails->first()['delivery_status'])) }}     
                                @endif -->

                               
                            </td>
                           
                            @php
                            
                            $sortinghubid = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$order->shipping_pin_code.'"]\')')->first();
                            
                            $getDeliveryBoy = \App\DeliveryBoy::where('sorting_hub_id',@$sortinghubid->user_id)->get();
                            
                            $getAssignedBoy = \App\AssignOrder::where('order_id',$order->id)->first();
                            @endphp
                            <td>
                            <select class="form-control demo-select2 assign_order"  data-minimum-results-for-search="Infinity"  @php echo ($delivery_status == 'delivered')?"disabled":"" @endphp>
                                <option value="">{{translate('Select Delivery Boy')}}</option>
                                    @foreach($getDeliveryBoy as $key=>$value)
                                    <?php
                                        $delivery_boy_name = \App\User::where('id',$value['user_id'])->first('name');
                                        
                                    ?>
                                    <option value="<?php echo $value['id']; ?>" @php  echo $getAssignedBoy['delivery_boy_id'] == $value['id']?"selected":"";  @endphp order_id = {{$order->id}}><?php  echo $delivery_boy_name['name']; ?></option>
                                    @endforeach
                                
                            </select>
                            </td>
                            {{-- <td>
                            
                            @php 
                            $checkAssignOrder = \App\AssignOrder::where('order_id',$order->id)->first();

                            @endphp

                            @if($checkAssignOrder)
                                <button class = "btn btn-sm" onclick="change_status('delivery_boy',{{$order->id}})" value="">Delete Delivery Boy</button>
                            @endif     --}}
                                    {{-- <select class="form-control demo-select2" name="delivery_status" id="delivery_status" onchange="change_status('delivery_boy',this)">
                                        <option value="0">Set Zero</option>
                                        <option value="0">Set Zero</option>
                                    </select>   --}}
                            {{-- </td> --}}
                            <td>
                                {{ ucfirst(str_replace('_', ' ', $order->payment_type)) }}
                            </td>
                            <td>
                                <span class="badge badge--2 mr-4"><i class="bg-green"></i> {{ $order->payment_status }}</span>
                            </td>
                            @if ($refund_request_addon != null && $refund_request_addon->activated == 1)
                                <td>
                                    @if (count($order->refund_requests) > 0)
                                        {{ count($order->refund_requests) }} {{ translate('Refund') }}
                                    @else
                                        {{ translate('No Refund') }}
                                    @endif
                                </td>
                            @endif
                            <td>
                                <div class="btn-group dropdown">
                                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                        {{translate('Actions')}} <i class="dropdown-caret"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a href="{{ route('orders.show', encrypt($order->id)) }}">{{translate('View')}}</a></li>
                                        <li><a href="{{ route('seller.invoice.download', $order->id) }}">{{translate('Download Invoice')}}</a></li>
                                        @if(@Auth::user()->staff->role->name!="Sorting Hub Manager" && @Auth::user()->staff->role->name!="Call Center Department")
                                        <li><a onclick="confirm_modal('{{route('orders.destroy', $order->id)}}');">{{translate('Delete')}}</a></li>
                                        @endif
                                        </ul>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
               
                <span class="pull-right badge badge-success" style="padding:10px;">Total amount = {{ single_price($total_today_amount-$refund_amount) }}</span>
            </tbody>
        </table>
         {{$orders->appends(request()->input())->links() }}
    </div>
</div>

@endsection


@section('script')
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
 <script type="text/javascript">
        $(document).ready(function() {
            $('#example').DataTable( {
                 "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
                 "scrollX":true,
                //"pageLength": 50,
               // "lengthMenu": [[20, 25, 50, -1], [20, 25, 50, "All"]],
                 "pagingType": "full_numbers"
                dom: 'lBfrtip'//,
                /*buttons: [
                    'excelHtml5', 'csvHtml5'
                ]*/
            } );
        } );
    </script>
    <script type="text/javascript">
        function sort_orders(el){
            $('#sort_orders').submit();
        }
            $('.assign_order').on('change',function(){
                var order_id = $('option:selected', this).attr('order_id');
                console.log(order_id);
            
                var delivery_boy_id = $(this).val();
                $.post('{{ route('sorthinghub.assign_order') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,delivery_boy_id:delivery_boy_id}, function(data){
                    showAlert('success', 'Order has been assigned.');
                });
            
        });

    function change_status(purpose,el){
        
            var order_id = el;
            var delivery_status = null;
            if(purpose == "delivery_status"){
                delivery_status = $('option:selected', el).attr('value');
                order_id = $('option:selected', el).attr('order_id');
                console.log(order_id);

            }
             $.post('{{ route('DOFO.change-purpose') }}', {_token:'{{ @csrf_token() }}',order_id:order_id,purpose:purpose,delivery_status:delivery_status}, function(data){
                 console.log(data);
                 if(data.purpose == "delivery_boy"){
                    
                 }

                 if(data.status == 1 ){
                        showAlert('success', 'Executed successfully.');
                    }else{
                        showAlert('error', 'Something Went Wrong.');
                    }
                 
                //showAlert('success', 'Order has been assigned.');
            });

    }


     function toggle(source){
        var checkboxes = document.querySelectorAll('input[type="checkbox"]');
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i] != source)
                checkboxes[i].checked = source.checked;
        }

    }

    function deleteOrder(){
        var order_id = [];
        $('input[name="delete_dofo"]:checked').each(function() {
            order_id.push(this.value);
        });
        if(order_id.length>0){
            $.post('{{ route('DOFO.delete-order') }}', {_token:'{{ @csrf_token() }}',order_id:order_id}, function(data){

                console.log(data);

                if(data == 1){
                    showAlert('success', 'Deleted successfully.');
                    location.reload();
                }else{
                    showAlert('error', 'Something Went Wrong.');
                }
                
            });

        }else{
           alert('please select orders');

        }
    }


       $(function() {

            var returnDays =  $('input[name="start"]').val();

            var endDate = $('input[name="endDate"]').val();

            var startDate = $('input[name="startDate"]').val();



            if(returnDays != "" && startDate == ""){ 

                        

                var start =moment().subtract(returnDays, 'days');

            }

            else if(startDate != "" ){

                var start =moment().subtract(startDate, 'days');

            }

            else{

                var start = moment().subtract(89, 'days');

            }



            if(endDate != "" && endDate != startDate){          

            var end =moment().subtract(endDate , 'days');

            }else{

                var end = moment();

            }

            //var end = moment();



            function cb(start, end) {

                $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

                lstart = moment($('#reportrange').data('daterangepicker').startDate).format('DD-MM-YYYY'),

                lend = moment($('#reportrange').data('daterangepicker').endDate).format('DD-MM-YYYY');

                $('input[name="dateRangeStart"]').val(lstart);

                $('input[name="dateRangeEnd"]').val(lend);
                
                //---25-09-2021
                $('#date_from_export').val(lstart);

                $('#date_to_export').val(lend);
                

            }



            $('#reportrange').daterangepicker({

                startDate: start,

                endDate: end,

                maxDate: end,

                ranges: {

                'Today': [moment(), moment()],

                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],

                'Last 7 Days': [moment().subtract(6, 'days'), moment()],

                'Last 30 Days': [moment().subtract(29, 'days'), moment()],

                'Last 90 Days': [moment().subtract(89, 'days'), moment()],

                'This Month': [moment().startOf('month'), moment().endOf('month')],

                //'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]

                }

            }, cb);

            $('#reportrange').on('apply.daterangepicker', (e, picker) => {

                $('#sort_orders').submit();

            });

            cb(start, end);

        });    


   
    </script>
@endsection
