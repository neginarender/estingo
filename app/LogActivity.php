<?php

namespace App;
use Request;
use Carbon\Carbon;
use DB;
use App\PaymentLog;
use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    public static function addToPayment($requestparams,  $orderID, $response, $status, $order_payment_phase, $hash, $paymentMethod)
    {  
        $data['letzpay_hash'] = $hash;

        //$data['platform_device'] = $platform;

        $data['payment_phase']= $order_payment_phase;

        $data['payment_method']= $paymentMethod;

        $data['status']= $status;

        $data['response']= $response;

        $data['request']= $requestparams;

        $data['url']= Request::fullUrl();

        $data['method'] = Request::method();

        $data['ip']= \Request::ip();

        $data['agent'] = Request::header('user-agent');

        $data['user_id']= @Auth()->user()->id;

        $data['order_id']= $orderID;

        $data['created_at'] = Carbon::now()->toDateTimeString();

        PaymentLog::insert($data);

    }
}
