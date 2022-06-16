<?php

namespace App\Http\Resources\v4;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\User;

class ReviewCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'user' => [
                        // 'name' => $data->user->name
                        'name' => $this->getUserDetail($data->user_id)
                    ],
                    'rating' => $data->rating,
                    'comment' => $data->comment,
                    'time' => $data->created_at->diffForHumans()
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    public function getUserDetail($id){
        $user = User::where('id',$id)->first();
        if(strlen($user->name) > 0 ){
            return $user->name;
        }else{
            $phone = substr($user->phone,0,2)."******".substr($user->phone,8);
            return $phone;
        }
    }
}
