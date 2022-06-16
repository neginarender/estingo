<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Twilio\TwiML\MessagingResponse;

trait WhatsAppTrait {

    public function createMessage($to,$message){
       
        $sid = getenv("TWILIO_ACCOUNT_SID");
        $token = getenv("TWILIO_AUTH_TOKEN");
        $twilio = new Client($sid, $token);

        $message = $twilio->messages
                        ->create("whatsapp:".$to, // to
                                [
                                    "from" => "whatsapp:+14155238886",
                                    "body" => $message
                                ]
                        );

        print($message->sid);
    }

    public function incomingMessageWebHook(Request $request){
        $response = new MessagingResponse();
        $message = $response->message('Thanks for the message');
        $message->body('Hello World!');
        $response->redirect('https://demo.twilio.com/welcome/sms/');

        echo $response;
    }

    public function callbackStatus(Request $request){
        info($request->all());
        info('Callback status webhook');
        $response = "delivered";
        $fulfillment = array(
            "fulfillmentText" => $response
        );

        return json_encode($fulfillment);
    }

    public function notifyOnOrderPlaced(){
        $to = "+917388991991";
        $msg = "Dear Customer, your order ORD123456777 for Rs 1000 will be delivered on Mon 20-8-2021 between 08:00 AM and 08:00 PM. Fresh Fruits, Dairy & Vegetables will be delivered separately between 08:00 AM and 10 AM. You can track your order on https://www.rozana.in/track_order Thank You for placing an order from Rozana. We are eager to become a part of your daily lives! Team Rozana";
        $this->createMessage($to,$msg);
    }

    public function notifyOrderOnDelivery(){
        $to = "+917388991991";
        //$msg = "Your order ORD123456777 from Rozana is out for delivery, please share delivery code 1234 with the executive. For further queries call 9667018020 Thank you, Team Rozana";
       $msg = "Your login code for Rozana is 123456";
        $this->createMessage($to,$msg);
    }
}