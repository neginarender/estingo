<?php

namespace App\Http\Controllers\Api\v3;
use Illuminate\Http\Request;

use App\Http\Resources\v3\ReviewCollection;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index($id)
    {
        return new ReviewCollection(Review::where('product_id', $id)->latest()->get());
    }

    public function store(Request $request)
    {
         
         $review = new Review;
         $review->product_id = $request->product_id;
         $review->user_id = $request->user_id;
         $review->rating = $request->rating;
         $review->comment = $request->comment;
         if($review->save()){
             return response()->json([
                 'success'=>true,
                 'message'=>'Thank you for your feedback'
             ]);
             return response()->json([
                 'success'=>false,
                 'message'=>'Something went wrong.'
             ]);
         }
    }
}
