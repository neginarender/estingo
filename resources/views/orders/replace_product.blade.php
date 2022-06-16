@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <!-- <a href="{{ route('sellers.create')}}" class="btn btn-info pull-right">{{translate('add_new')}}</a> -->
    </div>
</div>

<br>

<!-- Basic Data Tables -->
<!--===================================================-->
<div class="panel">
    <div class="panel-heading bord-btm clearfix pad-all h-100">
        <h3 class="panel-title pull-left pad-no">{{translate('Replacement Requests')}}</h3>
        <div class="pull-right clearfix">
            <!--<form class="" id="sort_customers" action="{{ route('search_history.download') }}" method="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <div class="box-inline pad-rgt pull-left">
                    <div class="" style="min-width: 200px;">
                    
                        <input type="text" class="form-control datepicker" id="date_from" name="date_from" value="{{ date('d-m-Y')}}" placeholder="Date From">
                    
                     </div>
                </div>
                
                <div class="box-inline pad-rgt pull-left">
                
                    <div class="" style="min-width: 200px;">
                        <input type="text" class="form-control datepicker" id="date_to" name="date_to" value="{{ date('d-m-Y')}}" placeholder="Date To">
                    
                     </div>
                </div>
                
                <div class="box-inline pad-rgt pull-left">
               
                    <div class="" style="min-width: 200px;">
                       <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type email or name & Enter') }}">
                    
                    <button type="submit" class="btn btn-success btn-sm">Download Excel</span></a>
                     </div>
                </div>
            </form> -->
        </div>
    </div>
    <div class="panel-body">
    <form action="{{ route('search_history.delete') }}" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
        
            <thead>
            
                <tr>
                    
                    <th>#</th>
                    <th>{{translate('Order Code')}}</th>
                    <th>{{translate('Product')}}</th>
                    <th>{{translate('Reason')}}</th>
                    <th>{{translate('Photos')}}</th>
                    <th>Assign</th>
                    <th>{{translate('Approve')}}</th>
                </tr>
            </thead>
            <tbody>
            
                    @if(count($replacement_requests))
                    @foreach($replacement_requests as $key => $replacement)    
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ $replacement->order->code }}</td>
                            
                            <td>{{ \App\Product::where('id',$replacement->order_detail->product_id)->first()->name }}</td>
                            <td>{{ $replacement->message }}</td>
                            <td>
                                @php 
                                    $photos = json_decode($replacement->photos);
                                @endphp
                                @foreach($photos as $ky => $photo)
                                    @if($ky==0)
                                    <a href="{{ Storage::disk('s3')->url($photo)}}" target="_blank">
                                    <img  src="{{ Storage::disk('s3')->url($photo)}}" width="50" height="50" />
                                </a>
                                        @if(count($photos)>1)
                                       + {{ count($photos)-1}}
                                        @endif

                                    @endif
                                @endforeach
                            </td>
                            <td>
                                            @php
                                                $sortinghubid = (Auth::user()->staff->role->name == "Sorting Hub Manager") ? Auth::user()->sortinghubmanager->sorting_hub_id : Auth::user()->id;
                                                $getDeliveryBoy = \App\DeliveryBoy::where('sorting_hub_id',$sortinghubid)->get();
                                                
                                            @endphp
                                <input type="hidden" name="replacement_request_id[]" id="replacement_request_id{{$key+1}}" value="{{ $replacement->id }}" />
                                
                                <select name="delivery_boy[]" class="form-control demo-select2" id="replacement_assign_order{{$key+1}}" onchange='assign_order("{{ $key+1}}")'>
                                    <option value="">Select</option>
                                        @foreach($getDeliveryBoy as $k=>$value)
                                                <?php
                                                $delivery_boy_name = \App\User::where('id',$value['user_id'])->first('name');
                                                ?>
                                                <option value="<?php echo $value['id']; ?>" @if($value['id'] == $replacement->delivery_boy_id) selected @endif><?php  echo $delivery_boy_name['name']; ?></option>
                                                @endforeach
                                </select>
                            </td>
                            <td>
                                <label class="switch">
                                <input onclick='update_approve_status("{{ $key+1 }}")' id="approve{{$key+1}}" type="checkbox" <?php if($replacement->approve == 1) echo "checked";?> >
                                <span class="slider round"></span></label>
                            </td>
                        </tr>
                         @endforeach
                        @else
                         <tr>
                             <td colspan="7">No Records found</td>
                         </tr>
                        @endif

               
            </tbody>
            
        </table>
        <form>
         <div class="clearfix">
            <div class="pull-right">
                {{ $replacement_requests->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $("#allCheck").click(function(){
    $('input:checkbox').not(this).prop('checked', this.checked);
});

$('.datepicker').datepicker({
    format: 'dd-mm-yyyy'
});

function assign_order(rid){
$.post("{{ route('replacement.assign_order') }}",{_token:"{{ csrf_token() }}",id:$("#replacement_request_id"+rid).val(),delivery_boy:$("#replacement_assign_order"+rid).val()},function(data){
    if(data=='1')
    {
        showAlert('success', 'Order has been assigned.');
    }
    else{
        showAlert('danger', 'Something went wrong!');
    }
    
});
}

function update_approve_status(rid){
    var status = 0;
    var id = $("#replacement_request_id"+rid).val();
    if($("#approve"+rid).prop('checked')==true){
        status = 1;
    }
$.post("{{ route('approve.replacement') }}",{_token:"{{ csrf_token() }}",id:id,approve:status},function(data){
    if(data=='1')
    {
        showAlert('success', 'Order has been assigned.');
    }
    else{
        showAlert('danger', 'Something went wrong!');
    }
    
});
}

    
</script>
@endsection

