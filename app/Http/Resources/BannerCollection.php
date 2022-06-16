<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BannerCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'photo' => $data->photo,
                    'url' => $data->link,
                    'position' => $data->position,
                    'link_type'=>$data->link_type,
                    'data'=>$this->getLink($data->link)
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

    public function getLink($url){
    	$link = explode('?',$url);
    	$cat_sub = explode('=',$link[1]);

    	$data = [];
    	if($cat_sub[0]=="category"){
    		$id = \App\Category::where(['slug'=>$cat_sub[1]])->first()->id;
    		$data['type'] = "category";
            $data['link'] = route('api.products.category', $id);
            $data['name'] = \App\Category::where(['slug'=>$cat_sub[1]])->first()->name;

    	}

    	if($cat_sub[0]=="subcategory"){
    		$id = \App\SubCategory::where(['slug'=>$cat_sub[1]])->first()->id;
    		$data['type'] = "subcategory";
            $data['link'] = route('subCategories.index', $id);
            $data['name']  = \App\SubCategory::where(['slug'=>$cat_sub[1]])->first()->name;

    	}

    	if($cat_sub[0]=="subsubcategory"){
    		$id = \App\SubSubCategory::where(['slug'=>$cat_sub[1]])->first()->id;
    		$data['type'] = "subsubcategory";
            $data['link'] = route('subCategories.index', $id);
            $data['name'] = \App\SubSubCategory::where(['slug'=>$cat_sub[1]])->first()->name;

    	}

    	return $data;
    }
}
