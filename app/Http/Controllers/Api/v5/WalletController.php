<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\WalletCollection;
use App\User;
use App\Wallet;
use App\Address;
use App\WalletLog;
use App\OrderDetail;
use App\Order;
use Razorpay\Api\Api;
use DB;
use Illuminate\Http\Request;
use App\Traits\WalletTrait;

class WalletController extends Controller
{
    use WalletTrait;
    public function balance($id)
    {
        // $user = User::find($id);
        // return response()->json([
        //     'balance' => $user->balance
        // ]);
        $user = User::find($id);
        $peer_discount = Order::leftJoin('order_details','orders.id','=','order_details.order_id')
            ->where('orders.user_id',$id)
            ->whereNotNull('order_details.id')
            ->whereNull('order_details.deleted_at')
            ->where('order_details.delivery_status','delivered')
            ->where('order_details.payment_status','paid')
            ->sum('order_details.peer_discount');
            //->toSql();
            //dd($peer_discount);

        $sub_peer = Order::leftJoin('order_details', function($join) {
              $join->on('orders.id', '=', 'order_details.order_id');
            })
            ->where('orders.user_id',$id)
            ->whereNotNull('order_details.id')
            ->where('order_details.delivery_status','delivered')
            ->where('order_details.payment_status','paid')
            ->sum('order_details.sub_peer');

        $master_peer = Order::leftJoin('order_details', function($join) {
              $join->on('orders.id', '=', 'order_details.order_id');
            })
            ->where('orders.user_id',$id)
            ->whereNotNull('order_details.id')
            ->where('order_details.delivery_status','delivered')
            ->where('order_details.payment_status','paid')
            ->sum('order_details.master_peer');

        return response()->json([
            'balance' => $user->balance,
            'orderCommission' => round($peer_discount,2),
            'peerCommission' => round($sub_peer,2),
            'masterCommission' => round($master_peer,2)
        ]);

    }

    public function walletRechargeHistory($id)
    {
        return new WalletCollection(Wallet::where('user_id', $id)->latest()->paginate(10));
    }

    public function processPayment(Request $request)
    {
        $order = new OrderController;
        $user = User::find($request->user_id);

        if ($user->balance >= $request->grand_total) {
            $user->balance -= $request->grand_total;
            $user->save();

            return $order->processOrder($request);
        }
        else {
            return response()->json([
                'success' => false,
                'message' => 'The order was not completed becuase the payment is invalid'
            ]);
        }
    }

    public function recharge(Request $request){
        $user_id = $request->user_id;
        $amount = $request->amount;
        $payment_method = $request->payment_method;
        $payment_type = $request->payment_type;

        $address = \App\Address::where('user_id',$user_id)->first();
        $user = \App\User::where('id',$user_id)->first();

        if($payment_type == 'wallet_payment') {

            $receiptId = rand(100000, 999999);
            $live_key_id = env('RAZOR_KEY');
            $live_key_secret = env('RAZOR_SECRET');

            $api = new Api($live_key_id, $live_key_secret);
            $orderinfo = $api->order->create(array(
                'receipt' => $receiptId,
                'amount' =>  round($amount,2)*100,
                'currency' => 'INR'
            ));

            $orderId = $orderinfo['id'];
            $orderinfo  = $api->order->fetch($orderId);
            $order_detalis = json_encode(array(     
                'id' => $orderinfo['id'],
                'entity' => $orderinfo['entity'],
                'amount' => $orderinfo['amount'],
                'currency' => $orderinfo['currency'],
                'receipt' => $orderinfo['receipt'],
                'status' => $orderinfo['status'],
                'attempts' => $orderinfo['attempts']
            ));

            $requestParams = json_encode(array('id' => $receiptId, 'amount' => $orderinfo['amount'] *100, 'currency' => 'INR'));

            $response = [
                'orderId' => $orderinfo['id'],
                'razorpayId' => env('RAZOR_KEY'),
                'amount' => $orderinfo['amount'],
                'name' => $user->name,
                'currency' => 'INR',
                'email' => $user->email,
                'contactNumber' => $user->phone,
                'address' =>$address->address,
                'description' => 'Order Payment',
                'receiptId' => $receiptId
            ];

            //insert data in wallet
            $wallet = new WalletLog;
            $wallet->user_id = $user_id;
            $wallet->amount = $amount;
            $wallet->payment_method = 'recharge';
            $wallet->order_id = $receiptId;
            $wallet->tr_type = 'credit';
            $wallet->save();

            }

        return response()->json([
            'success' => true,
            'message' => 'success',
            'response' => $response,
            'order_detail' => json_decode($order_detalis),
        ]);

    }

    public function wallet_recharge_done(Request $request){
        $requestPar = $request->all();
        $user = \App\user::where('id',$request->user_id)->first();
        $user->balance = $user->balance + $request->amount;
        $user->save();

        $wallet = new Wallet;
        $wallet->user_id = $request->user_id;
        $wallet->amount = $request->amount;
        $wallet->payment_method = 'recharge';
        $wallet->order_id = $request->receiptId;
        $wallet->payment_details = json_encode($requestPar['order_detail']);
        $wallet->tr_type = 'credit';
        $wallet->save();

        return response()->json([
            'success' => true,
            'message' => 'Wallet Recharge successfully.',
            'balance'=> round($user->balance,2)
        ]);

    }

    public function addFundAccound(REQUEST $request){
        try{
            $status = true;
            $message = "";
            $data = [];
            $account_type="bank_account";
            $contact_id = $request->razorpay_contact_id;
            if(empty($contact_id)){
                $res = $this->createContactOnFundAdd($request->user_id,$request->name,$request->phone_number);
               
                
                if($res->success==false){
                    return response()->json([
                        'success'=>false,
                        'message'=>$res->response,
                        'data'=>[]
                    ]);
                }
                $contact = $res->response;
                $contact_id = $contact->id;
            }
            $check_account = \App\BankAccount::where(['user_id'=>$request->user_id,'account_type'=>$request->account_type])->first();
            DB::beginTransaction();
            if($check_account == null){
                $add_account = new \App\BankAccount;
                $add_account->holder_name = $request->name;
                $add_account->user_id = $request->user_id;
                $add_account->account_type = $request->account_type;
                $add_account->phone_num = $request->phone_number;
                if($request->account_type == "bank_transfer"){
                    $add_account->bank_name = $request->bank_name;
                    $add_account->account_number = $request->account_number;
                    $add_account->ifsc = $request->ifsc_code;
                    $add_account->bank_account_type = $request->bank_account_type;
                }elseif($request->account_type == "upi"){
                    $account_type = "vpa";
                    $add_account->upi = $request->upi;
                }
                if($add_account->save()){
                    $add_account->setAttribute('rzp_contact_id',$contact_id);
                    $data = $add_account;
                    
                    if($account_type=="bank_account"){
                        $requestData = [
                            "contact_id"=>$contact_id,
                            "account_type"=>"bank_account",
                            "bank_account"=>[
                              "name"=>$request->name,
                              "ifsc"=>$request->ifsc_code,
                              "account_number"=>$request->account_number
                            ]
                        ];
                    }else{
                       
                        $requestData = [
                            "contact_id"=>$contact_id,
                            "account_type"=>"vpa",
                            "vpa"=>[
                                "address"=>$request->upi
                            ]
                            
                        ];
                    }
                    
                    $res = $this->createFundAccount($requestData);
                    $res = $res->getData();
                    if($res->success){
                        //Get Fund account id
                        $fund_account = $res->response;
                        $fund_account_id = $fund_account->id;
                        \App\BankAccount::where('id',$data->id)->update(['fund_account_id'=>$fund_account_id]);
                        DB::commit();
                        
                    }else{
                        DB::rollback();
                       return response()->json([
                           'success'=>false,
                           'message'=>$res->response,
                           'data'=>[]
                       ]);
                    }

                    $message = "Congratulations!!! Account has been added.";    
                }
            }else{
                    $message = $request->account_type." is already exists.Please Update.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data
            ]);
            
        }catch(\Exception $e){
            $message = $e->getMessage();
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data
            ]);
        }

    }

    public function getAccount($user_id){
        $status = true;
        $message = "";
        $get_account = \App\BankAccount::where('user_id',$user_id)->get();
        
        if(count($get_account)){
            $message = "get data.";
            $success = true;
        }else{
            $success = false;
            $message = "Please add fund account";
        }
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $get_account
        ]);
    }

    public function payRequest(REQUEST $request){
        try{
            $message = "";
            $payoutrequest = new \App\WalletPayout;
            $account = \App\BankAccount::find($request->acount_id);
            $payoutrequest->user_id = $request->user_id;
            $payoutrequest->holder_name = $account->holder_name;
           
            $payoutrequest->fund_account_number = $account->fund_account_id;
            if($account->account_type=="bank_transfer"){
                $payoutrequest->account_number = $account->account_number;
                $payoutrequest->ifsc = $account->ifsc;
            }else{
                $payoutrequest->upi_id = $account->upi;;
            }
            
            $payoutrequest->amount = $request->amount;

            if($payoutrequest->save()){
                $success = true;
                $message = "Your request has been created.";

            }
        }catch(\Exception $e){
            $success = false;
            $message = $e->getMessage();
        }

        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
    }



}