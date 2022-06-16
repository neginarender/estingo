<?php

namespace App\Http\Controllers\Api\v5;
use Illuminate\Http\Request;

class NotificationController extends Controller {

    public function notificationList($user_id){
        $arr = array();
        $customerList = \App\User::where('peer_user_id',$user_id)->get();
        foreach($customerList as $row){
            array_push($arr,$row['id']);
        }

        $notifications = \App\Notification::select('id','is_view','content','user_id','order_type')->where('user_id',$user_id)->orWhereIn('user_id',$arr)->orderBy('id','desc')->get();
        $notifications = $notifications->map(function($item){
            $content = json_decode($item->content);
            return [
                'notification_id'=>$item->id,
                'title'=>$content->title,
                'order_code'=>$content->body->order_code,
                'order_id'=>$content->body->order_id,
                'address'=>$content->body->address,
                'is_view'=>$item->is_view,
                'user_id'=>$item->user_id,
                'order_type'=>$item->order_type
            ];
        });
        return response()->json([
            'success'=>true,
            'data'=>$notifications
        ]);
    }

    public function updateNotificationStatus(Request $request){
        $notification = \App\Notification::find($request->notification_id);
        $notification->is_view = 1;
        if($notification->save()){
            return response()->json([
                'success'=>true,
                'message'=>'Status updated'
            ]);
        }
        return response()->json([
            'success'=>false,
            'message'=>'something went wrong'
        ]);

    }
}