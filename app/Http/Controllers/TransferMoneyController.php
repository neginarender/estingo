<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Traits\WalletTrait;
use App\ShortingHub;
use App\WithdrawRequest;

class TransferMoneyController extends Controller
{
    //
    use WalletTrait;

    public function __construct(){
    }

    public function viewContactInfo(){
        $endpoint = "https://api.razorpay.com/v1/contacts";
        $requestData = "";
        $res = $this->apiRequest('GET',$endpoint,$requestData);
        $contacts = array();
        if($res->getData()->success == true){
            $contacts = $res->getData()->response->items;
        }
        return view('transfer_money.index',compact('contacts'));
    }


    public function addAccount(REQUEST $request,$cont_id){
        $contact_id = decrypt($cont_id);
        return view('transfer_money.create',compact('contact_id'));
    }

    public function storeAccount(REQUEST $request){
        $endpoint = "https://api.razorpay.com/v1/fund_accounts";
        if($request->account_type == 'vpa'){
            $requestData = [
                "contact_id"=>$request->contact_id,
                "account_type"=>$request->account_type,
                "vpa"=>[
                  "address"=>$request->address
                ]
            ];

        }else{
            $requestData = [
                "contact_id"=>$request->contact_id,
                "account_type"=>$request->account_type,
                "bank_account"=>[
                  "name"=>$request->name,
                  "ifsc"=>$request->IFSC_Code,
                  "account_number"=>$request->account_number
                ]
            ];

        }
        
        $res = $this->apiRequest('POST',$endpoint,$requestData);
        if($res->getData()->success == true){
            flash("account has created")->success();
            return redirect()->route('razorpayx.getcontactlist');
        }else{
            flash($res->getData()->response)->error();
            return back();
        }
        
    }

    public function viewContactForm(){
        $shorting_hub = ShortingHub::get();
        return view('transfer_money.create_contact',compact('shorting_hub'));
    }

    public function storeContactForm(REQUEST $request){
        $endpoint = "https://api.razorpay.com/v1/contacts";
        $requestData = [
            "name"=>$request->name,
            "email"=>$request->email,
            "contact"=>$request->contact,
            "type"=>"customer",
            "reference_id"=>$request->reference_id,
            "notes"=>[
              "notes_key_1"=>"Rozana peer partner",
              "notes_key_2"=>"Rozana peer partner"
            ]
        ];

        $res = $this->apiRequest('POST',$endpoint,$requestData);
        if($res->getData()->success == true){
            flash("Contact has created")->success();
            return redirect()->route('razorpayx.getcontactlist');
        }else{
            flash($res->getData()->response)->error();
            return back();
        }
    }


    public function viewTransferMoney(REQUEST $request,$contact_id){
        $cont_id = decrypt($contact_id);
        $account_list = array();
        $endpoint = "https://api.razorpay.com/v1/fund_accounts";
        $requestData = [
            "contact_id"=>$cont_id
        ];

        $res = $this->apiRequest('GET',$endpoint,$requestData);
        if($res->getData()->success == true){
            $account_list = $res->getData()->response->items;
            return view('transfer_money.view_transfer_money',compact('account_list'));
        }else{
            flash('Please Add Bank Account')->error();
            return back();
        }

    }

    public function sendMoney(REQUEST $request){

        $account_type = explode('=',$request->account_type);
        $endpoint = "https://api.razorpay.com/v1/payouts";
        if($account_type[1] == 'bank_account'){
            $requestData = [
                "account_number"=>'2323230031364031',
                "fund_account_id"=> $account_type[0],
                "amount"=> round($request->amount,2)*100,
                "currency"=>"INR",
                "mode"=>"IMPS",
                "purpose"=>"refund",
                "queue_if_low_balance"=>true,
                "reference_id"=>"test",
                "narration"=>"test",
                "notes"=>[
                  "notes_key_1"=>"Rozana, Peer Partner Fund Transfer",
                  "notes_key_2"=>"Rozana, Peer Partner Fund Transfer"
                ]
            ];

        }elseif($account_type[1] == 'vpa'){
            $requestData = [
                "account_number"=>'2323230031364031',
                "fund_account_id"=>$account_type[0],
                "amount"=>round($request->amount,2)*100,
                "currency"=>"INR",
                "mode"=>"UPI",
                "purpose"=>"refund",
                "queue_if_low_balance"=>true,
                "reference_id"=> "test",
                "narration"=>"test",
                "notes"=>[
                  "notes_key_1"=>"Rozana, Peer Partner Fund Transfer",
                  "notes_key_2"=>"Rozana, Peer Partner Fund Transfer"
                ]
            ];
        }

        $res = $this->apiRequest('POST',$endpoint,$requestData);
        if($res->getData()->success == true){
            flash($res->getData()->response->status)->success();
            return back();
        }else{
            flash($res->getData()->response)->error();
            return back();
        }

    }


    public function getWithdrawRequestByUsers(REQUEST $request){
        $withdrawrequest = WithdrawRequest::paginate(20);
        return view('transfer_money.withdraw_request_list',compact('withdrawrequest'));
    }

    public function updateWithdrawRequestStatus($id,$status){
        $requestId = decrypt($id);
        $withdrawrequest = WithdrawRequest::find($requestId);
        $withdrawrequest->request_status = $status;
        if($withdrawrequest->save()){
            flash("Request status updated")->success();
            return back();
        }
        flash("Something went wrong!")->error();
        return back();
    }

    public function rejectWithdrawRequest(REQUEST $request){
        
        $requestId = $request->request_id;
        $withdrawrequest = WithdrawRequest::find($requestId);
        $withdrawrequest->request_status = 3;
        $withdrawrequest->message = $request->reason;
        if($withdrawrequest->save()){
            flash("Request status updated")->success();
            return back();
        }
        flash("Something went wrong!")->error();
        return back();
    }
}
