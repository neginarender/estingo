<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use App\User;
use App\Order;

class WatiController extends Controller
{
    //

    private $auth_token;
    private $client;

    public function __construct(){
        $this->client = new \GuzzleHttp\Client();
        $this->auth_token = "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiI2MTQ4YzJjYi05MDU2LTQ4ZjktYTYzNi0zN2FkNTY4ZjkzODUiLCJ1bmlxdWVfbmFtZSI6Im1hbmlzaC55LjE4cGl4ZWxzQGdtYWlsLmNvbSIsIm5hbWVpZCI6Im1hbmlzaC55LjE4cGl4ZWxzQGdtYWlsLmNvbSIsImVtYWlsIjoibWFuaXNoLnkuMThwaXhlbHNAZ21haWwuY29tIiwiYXV0aF90aW1lIjoiMDEvMDgvMjAyMiAwNTo1Mjo1NiIsImRiX25hbWUiOiJ3YXRpX2FwcCIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6IlRSSUFMIiwiZXhwIjoxNjQyMjkxMjAwLCJpc3MiOiJDbGFyZV9BSSIsImF1ZCI6IkNsYXJlX0FJIn0.Slwm58JyiLOYLc6qzd0wGlbt_QcDvxp6qyovdJcJ9f0";
    }

    public function getMessageByNum(){
        
        $request = $this->client->get('https://agrijunctions.com/api/v1/allcategory');
        $response = $request->getBody();
        dd(json_decode($response));

    }

    public function getTemplateMessage(){
        $url = "https://app-server.wati.io/api/v1/getMessageTemplates";
        

    }

    public function neodoveAgenetLogin(REQUEST $request){
        $array = array();
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $array['token'] = $tokenResult->accessToken;
        return $array;

    }


    public function getUserWithMobile(REQUEST $request){
        $data = array();
        $country_code = "+91";
        $mobile = preg_replace("/^\+?{$country_code}/",'',$request->mobile);
        $user = User::select('id','name','email','phone')->where('phone',$mobile)->first();
        $data['user_details']['name'] = $user->name;
        $data['user_details']['email'] = $user->email;
        $data['user_details']['mobile'] = $user->phone;
        $order = Order::where('user_id',$user->id)->orderBy('created_at','desc')->first();
        if(!empty($order)){
            $data['user_details']['last_order']['order_id'] = $order->code;
            $data['user_details']['last_order']['shipping_address'] = json_decode($order->shipping_address);
            foreach($order->orderDetails as $key => $value){
                $data['user_details']['last_order']['order_details'][$key]['product_name'] = $value->product->name;

            }

        }
        return $data;
    }
}
