<?php

namespace App\Http\Controllers\Api\v4;

use App\Http\Resources\v4\WalletCollection;
use App\User;
use App\Wallet;
use App\Address;
use App\WalletLog;
use App\OrderDetail;
use App\Order;
use Razorpay\Api\Api;
use DB;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function balance($id)
    {
        
        // $user = User::find($id);
        // return response()->json([
        //     'balance' => $user->balance
        // ]);
        $user = User::find($id);
        $peer_discount = Order::leftJoin('order_details', function($join) {
              $join->on('orders.id', '=', 'order_details.order_id');
            })
            ->where('orders.user_id',$id)
            ->whereNotNull('order_details.id')
            ->where('order_details.delivery_status','delivered')
            ->where('order_details.payment_status','paid')
            ->sum('order_details.peer_discount');

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



}