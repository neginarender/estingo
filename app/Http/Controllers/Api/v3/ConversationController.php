<?php
namespace App\Http\Controllers\Api\v3;

use Illuminate\Http\Request;
use App\Conversation;
use App\BusinessSetting;
use App\Message;
use Auth;
use App\Product;
use Mail;
use App\Mail\ConversationMailManager;
use App\User;

class ConversationController extends Controller{

    public function store(Request $request)
{   
    $prod = Product::findOrFail($request->product_id);

    if(!empty($prod)){
        $user_id = Product::where('id',$request->product_id)->pluck('user_id')->first();

            if($user_id!=''){
                 $user_type = User::where('id', $user_id)->pluck('user_type')->first();
            }else{
                 $user_type = '';
            }

            $conversation = new Conversation;
            $conversation->sender_id = $request->user_id;
            // $conversation->receiver_id = $user_id;
            $conversation->title = $request->title;
            $conversation->receiver_id = $request->sortinghubid;

            if($conversation->save()) {
                $message = new Message;
                $message->conversation_id = $conversation->id;
                $message->user_id = $request->user_id;
                $message->message = $request->message;

                if ($message->save()) {
                    $this->send_message_to_seller($conversation, $message, $user_type);
                }
                return response()->json([
                    'success'=>true,
                    'message'=>"Message has been send to seller"
                ]);
            }
            return response()->json([
                'success'=>false,
                'message'=>"Something went wrong"
            ]);

    }else{
        return response()->json([
            'success'=>false,
            'message'=>"Product not found."
        ]);
    }
    
}

public function send_message_to_seller($conversation, $message, $user_type)
{
    $array['view'] = 'emails.conversation';
    $array['subject'] = 'Sender:- '.Auth::user()->name;
    $array['from'] = env('mail_from_address');
    $array['content'] = 'Hi! You recieved a message from '.Auth::user()->name.'.';
    $array['sender'] = Auth::user()->name;

    if($user_type == 'admin') {
        $array['link'] = route('conversations.admin_show', encrypt($conversation->id));
    } else {
        $array['link'] = route('conversations.show', encrypt($conversation->id));
    }

    $array['details'] = $message->message;
    if(!is_null($conversation->receiver)){
        try {
            $mail = Mail::to($conversation->receiver->email)->queue(new ConversationMailManager($array));
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}

}
