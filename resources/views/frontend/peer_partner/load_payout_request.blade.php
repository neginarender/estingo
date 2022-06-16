@php 
                    $wallet_balance = $transfer_request->user->balance;
                    $requested_amount = $transfer_request->amount;
                    $tds = ($transfer_request->amount*$transfer_request->tds)/100;
                    $final_amount = $transfer_request->amount-$tds;
                    $payout_id = "";
                    $status = "Pending";
                    $badge = "warning";
                    $payout = \App\Wallet::where('payout_request_id',$transfer_request->id)->select('payment_details')->first();
                            if(!is_null($payout)){
                                $payout_id = json_decode($payout->payment_details)->id;
                            }
                        if($transfer_request->status==1){
                            $status = "Approved";
                            $badge = "success";
                            
                            
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
                    
<div class="modal-body">
         
          @if($wallet_balance<=0 || $wallet_balance<$requested_amount)
          <div class="row">
              {{ $wallet_balance }}
              <div class="col-md-12">
                <span class="alert alert-danger" style="display:block;">Important! Wallet Balance is not sufficient. Current Wallet Balance is 0</span>      
              </div>
          </div>
          @endif
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
      <input type="hidden" name="id" id="request_id" value="{{ $transfer_request->id }}" />
       <div class="row">
           <div class="col-md-4">
               <strong>Partner Name</strong>
               <p id="partner_name">{{ $transfer_request->user->name }}</p>
           </div>
           <div class="col-md-4">
               <strong>Account Holder Name</strong>
               <p id="holder_name">{{ $transfer_request->holder_name }}</p>
           </div>
           <div class="col-md-4">
               <strong>Account Number/UPI ID</strong>
               <p id="account_no"> {{ $ac_no }}</p>
           </div>
           <div class="col-md-4">
               <strong>IFSC Code</strong>
               <p id="ifsc">{{ $transfer_request->ifsc }}</p>
           </div>
           <div class="col-md-4">
               <strong>Amount</strong>
               <p id="amount">{{ single_price($transfer_request->amount) }}</p>
           </div>
           <div class="col-md-4">
               <strong>TDS</strong>
               <p id="tds">{{ $transfer_request->tds }}%</p>
           </div>
           <div class="col-md-4">
               <strong>Final Amount</strong>
               <p id="final_amount">{{ single_price($final_amount) }}</p>
           </div>
           <div class="col-md-4">
               <strong>Status</strong>
               <br />
               <p id="status"><span class="badge badge-{{ $badge }}">{{ $status}}</span></p>
           </div>
           @if(!is_null($payout))
           <div class="col-md-4" id="payout_id" style="display:none;">
               <strong>Payout ID</strong>
               <br />
               <p id="payout_idd">{{ $payout_id }}</p>
           </div>
           @endif
          </div>
          @if($transfer_request->status==0)
          <div class="row">
          <div class="col-md-6" id="action_status">
               <select class="form-control demo-select2" name="status">
               @if($wallet_balance>=$requested_amount)
                   <option value="1">Approve</option>
                   @endif
                   <option value="2">Reject</option>
               </select>
           </div>
           <div class="col-md-6">
              <textarea name="remarks" id="remarks" class="form-control" placeholder="Remarks"></textarea>
           </div>
          </div>
          @endif
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        @if($transfer_request->status==0)
        <button type="submit" id="submit" class="btn btn-primary">Save changes</button>
        @endif
      </div>