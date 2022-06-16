<?php

namespace App\Http\Resources\v4;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\SearchHistory;

class SearchHistoryCollection extends ResourceCollection
{
    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'search'=>$data->search,
                    'category_id'=>$data->category_id
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
 
    
    
}
