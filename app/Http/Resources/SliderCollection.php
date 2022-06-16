<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SliderCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'photo' => $data->photo,
                    // 'url' => $data->link,
                    // 'position' => $data->position,
                    'data'=>$this->getLink($data->link,$data->link_type)
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

    public function getLink($url,$link_type){
        
        
        $data = ['type'=>"",'link'=>"",'name'=>""];
        if($link_type=="category"){
            $link = explode('?',$url);
            $cat_sub = explode('=',$link[1]);
            $category = \App\Category::where(['slug'=>$cat_sub[1]])->first();
            if(!is_null($category)){
                $id = $category->id;
                $data['type'] = "category";
                $data['link'] = route('api.products.category', $id);
                $data['name'] = trans(\App\Category::where(['slug'=>$cat_sub[1]])->first()->name);
                $data['category_banner'] = $category->banner;
            }
            

        }

        if($link_type=="subcategory"){
            $link = explode('?',$url);
            $cat_sub = explode('=',$link[1]);
            $subcategory = \App\SubCategory::where(['slug'=>$cat_sub[1]])->first();
            if(!is_null($subcategory)){
                $id = $subcategory->id;
                $data['type'] = "subcategory";
                $data['link'] = route('subCategories.index', $id);
                $data['name']  = trans(\App\SubCategory::where(['slug'=>$cat_sub[1]])->first()->name);
                $data['category_banner'] = $subcategory->category->banner;
                }
            

        }

        if($link_type=="subsubcategory"){
            $link = explode('?',$url);
            $cat_sub = explode('=',$link[1]);
            $subsubcategory = \App\SubSubCategory::where(['slug'=>$cat_sub[1]])->first();
            if(!is_null($subsubcategory)){
                $id = $subsubcategory->id;
                $data['type'] = "subsubcategory";
                $data['link'] = route('subCategories.index', $id);
                $data['name'] = trans(\App\SubSubCategory::where(['slug'=>$cat_sub[1]])->first()->name);
                $data['category_banner'] = $subsubcategory->subcategory->category->banner;
            }
        }
        if($link_type=="default"){
                $data['type'] = "default";
                $data['link'] = "";
                $data['name'] = "";
                $data['category_banner'] = "";

        }

        // if($link_type=="search"){
        //         $link = explode('?',$url);
        //         $cat_sub = explode('=',$link[1]);

        //         $data['type'] = "search";
        //         $data['link'] = route('products.search',$cat_sub[1]);
        //         $data['name'] = str_replace('%20',' ', $cat_sub[1]);

        // }

        // if($link_type=="flashdeal"){
        //         $data['type'] = "flash_deal";
        //         $data['link'] = "flash-deal";
        //         $data['name'] = "Flash Deal";

        // }

        return $data;
    }

}
