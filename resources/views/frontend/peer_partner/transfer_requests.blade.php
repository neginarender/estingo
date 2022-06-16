@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-sm-12">
           <a href="{{ route('peer_partner.createpeer')}}" class="btn btn-rounded btn-info pull-right">{{translate('Add New Peer Discount')}}</a>
           <a href="{{ route('peer_partner.peer_commision')}}" class="btn btn-rounded btn-info pull-right" style="margin-right: 6px">{{translate('View All Peer Commission')}}</a>
        </div>
    </div>

    <br>

    <!-- Basic Data Tables -->
    <!--===================================================-->
    <div class="panel">
        <div class="panel-heading bord-btm clearfix pad-all h-100">
            <h3 class="panel-title pull-left pad-no">{{translate('Payout Requests')}}</h3>
            <div class="pull-right clearfix">
                <!-- <form class="" id="sort_sellers" action="" method="GET">
                    <div class="box-inline pad-rgt pull-left">
                        <div class="select" style="min-width: 300px;">
                            <select class="form-control demo-select2" name="approved_status" id="approved_status" onchange="sort_sellers()">
                                <option value="">{{translate('Filter by Approval')}}</option>
                                <option value="1"  @isset($approved) @if($approved == 'paid') selected @endif @endisset>{{translate('Approved')}}</option>
                                <option value="0"  @isset($approved) @if($approved == 'unpaid') selected @endif @endisset>{{translate('Non-Approved')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="box-inline pad-rgt pull-left">
                        <div class="" style="min-width: 200px;">
                            <input type="text" class="form-control" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name or email & Enter') }}">
                        </div>
                    </div>
                </form> -->
            </div>
        </div>
        <div class="panel-body">
            <table class="table table-striped res-table mar-no" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Partner Name')}}</th>
                    <th>{{translate('Ac Holder Name')}}</th>
                    <th>{{translate('Ac No./UPI ID')}}</th>
                    <th>{{translate('Ac Type')}}</th>
                    <th>{{translate('Amount')}}</th>
                    <th>{{translate('TDS')}}</th>
                    <th>{{translate('Final Amount')}}</th>
                    <th>{{ translate('Status') }}</th>
                    <th>{{translate('Date')}}</th>
                    <th width="10%">{{translate('Options')}}</th>
                </tr>
                </thead>
                <tbody>
                    @php 
                     $wallet_balance = 0;
                     $requested_amount = 0;
                    @endphp
                @foreach($transfer_requests as $key => $transfer_request)
                    @php 
                    $wallet_balance = $transfer_request->user->balance;
                    $requested_amount = $transfer_request->amount;
                    $tds = ($transfer_request->amount*$transfer_request->tds)/100;
                    $final_amount = $transfer_request->amount-$tds;
                    $payout_id = "";
                    $status = "Pending";
                    $badge = "warning";
                        if($transfer_request->status==1){
                            $status = "Approved";
                            $badge = "success";
                            $payout = \App\Wallet::where('payout_request_id',$transfer_request->id)->select('payment_details')->first();
                            if(!is_null($payout)){
                                $payout_id = json_decode($payout->payment_details)->id;
                            }
                            
                        }
                        else if($transfer_request->status==2){
                            $status = "Rejected";
                            $badge = "danger";
                        }
                        if(is_null($transfer_request->upi_id))  {
                            $ac_no = $transfer_request->account_number;
                            $ac_type = "Bank Transfer";
                        }  
                           
                        else{
                            $ac_no = $transfer_request->upi_id;
                            $ac_type = "UPI";
                        }
                    @endphp
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ $transfer_request->user->name }}<br />{{ $wallet_balance}}</td>
                            <td>{{ $transfer_request->holder_name }}</td>
                            <td>
                            {{ $ac_no }}
                          </td>
                            <td>
                            {{ $ac_type }} 
                           </td>
                            <td>{{ single_price($transfer_request->amount) }}</td>
                            <td>{{ $transfer_request->tds }}%</td>
                            <td>{{ single_price($final_amount) }}</td>
                            <td><span class="badge badge-{{ $badge }}">{{ $status}}</span></td>
                            <td>{{ date('d-m-Y',strtotime($transfer_request->created_at)) }}</td>
                         
                            <td>
                               
                                <div class="btn-group dropdown">
                                    <button class="btn btn-primary dropdown-toggle dropdown-toggle-icon" data-toggle="dropdown" type="button">
                                        {{translate('Actions')}} <i class="dropdown-caret"></i>
                                    </button>
                                   
                                    <ul class="dropdown-menu dropdown-menu-right">
                                    
                                    <li><a href="javacsript:void(0);" onclick='loadPayoutRequest("{{ $transfer_request->id }}")' data-toggle="modal" data-target="#exampleModal">View Details</a></li>
                                   

                                </ul>
                                     
                                </div>
                              

                             
                            </td>
                        </tr>
                  
                @endforeach
                </tbody>
            </table>
            <div class="clearfix">
                <div class="pull-right">
                    {{ $transfer_requests->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>

    <style>

.loader {
  border: 5px solid #f3f3f3; /* Light grey */
  border-top: 5px solid #3498db; /* Blue */
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 15s linear infinite;
  text-align:center;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}


</style>
@endsection

@section('script')
   
<script type="text/javascript">
function details(payout_id,request_id,partner_name,holder_name,ifsc,account_no,amount,tds,final_amount,status,status_class){
$("#request_id").val(request_id);
$("#partner_name").html(partner_name);
$("#holder_name").html(holder_name);
$("#account_no").html(account_no);
$("#ifsc").html(ifsc);
$("#amount").html(amount);
$("#tds").html(tds);
$("#final_amount").html(final_amount);
$("#status").html(status);
$("#status").removeAttr('class');
$("#status").addClass("badge badge-"+status_class);
$("#submit").show();
if(status!="Pending"){
$("#action_status").hide();
$("#remarks").hide();
$("#submit").hide();
}else{
$("#action_status").show();
$("#remarks").show();
$("#submit").show();  
}
if(payout_id!=""){
$("#payout_id").show();
$("#payout_idd").html(payout_id);
}
else{
$("#payout_id").hide();
$("#payout_idd").html("");  
}

}

function loadPayoutRequest(payout_request_id){
    $("#load_payout_request").html("<center><div class='loader'></div></center>");
$.post("{{ route('payout_requests.load_request') }}",{_token:"{{ csrf_token() }}",payout_id:payout_request_id},function(data){
$("#load_payout_request").html(data);
});
}
</script>
@endsection

@section('modal')
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Payout Request</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('payout_requests.update_status') }}" method="post">
      <div id="load_payout_request"></div>
      
</form>
    </div>
  </div>
</div>
<style type="text/css">
.tab { font-size: 11px; padding: 5px 7px; color: #fff; }
.success { background: #80ae00; } .pending { background: #b78e41d1; }
</style>
@endsection

