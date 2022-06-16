<?php

namespace App\Http\Resources\v5;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SliderCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return $this->getLink($data->link,$data->link_type,$data->photo);
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

    public function getLink($url,$link_type,$photo){

        $link = explode('?',$url);

        if(isset($link[1])){
            $cat_sub = explode('=',$link[1]);
            if($cat_sub[0] == 'q'){
                $link_type ="search";
            }
        }else{
            $cat_sub = explode('/',$url);
        }


        $data = [];

        if($link_type =="category"){

            $category = \App\Category::where(['slug'=>$cat_sub[1]])->first();
            if(empty($category)){
                $category_id = 0;
                $category_name = NULL;
            }else{
                $category_id = $category->id;
                $category_name = $category->name;
            }
            $data['type_query_id'] = $category_id;
            $data['type'] = "category";
            $data['name'] = trans($category_name);
            $data['slug'] = $cat_sub[1];
            $data['photo'] = $photo;
            $data['category_banner'] = $category['banner'];
            

        }

        if($link_type =="subcategory"){
            
            $subcategory = \App\SubCategory::where(['slug'=>$cat_sub[1]])->first();
            if(empty($subcategory)){
                $subcategory_id = 0;
                $subcategory_name = NULL;
            }else{
                $subcategory_id = $subcategory->id;
                $subcategory_name = $subcategory->name;
            }
            $data['type_query_id'] = $subcategory_id;
            $data['type'] = "subcategory";
            $data['name']  = trans($subcategory_name);
            $data['slug'] = $cat_sub[1];
            $data['photo'] = $photo;
            $data['category_banner'] = $subcategory->category->banner;

        }
        if($link_type=="default"){
            $data['type_query_id'] = 0;
            $data['type'] = "default";
            $data['name']  = "";
            $data['slug'] = "";
            $data['photo'] = $photo;
            $data['category_banner'] = "";

        }

        if($link_type =="subsubcategory"){

            $subsubcategory = \App\SubSubCategory::where(['slug'=>$cat_sub[1]])->first();
            if(empty($subsubcategory)){
                $subsubcategory_id = 0;
                $subsubcategory_name = NULL;
            }else{
                $subsubcategory_id = $subsubcategory->id;
                $subsubcategory_name = $subsubcategory->name;
            }
            $data['type_query_id'] = $subsubcategory_id;
            $data['type'] = "subsubcategory";
            $data['name'] = trans($subsubcategory_name);
            $data['slug'] = $cat_sub[1];
            $data['photo'] = $photo;
            $data['category_banner'] = $subsubcategory->subcategory->category->banner;

        }


        /*if($link_type =="flashdeal"){

            if(isset($cat_sub[4])){
                $flashdeal = \App\FlashDeal::where(['slug'=>$cat_sub[4]])->first();
                $flashdeal_slug = $cat_sub[4];
            }else{
                $flashdeal = \App\FlashDeal::where(['slug'=>$cat_sub[1]])->first();
                $flashdeal_slug = $cat_sub[1];
            }
            
            if(empty($flashdeal)){
                $flashdeal_id = 0;
                $flashdeal_title = NULL;
            }else{
                $flashdeal_id = $flashdeal->id;
                $flashdeal_title = $flashdeal->title;
            }
            $data['type_query_id'] = $flashdeal_id;
            $data['type'] = "flashdeal";
            $data['name'] = $flashdeal_title;
            $data['slug'] = $flashdeal_slug;
            $data['photo'] = $photo;
            $data['category_banner'] = "";
        }*/

        if($link_type =="product"){

            $product = \App\Product::where(['slug'=>$cat_sub[4]])->first();
            
            if(empty($product)){
                $product_id = 0;
                $product_name = NULL;
            }else{
                $product_id = $product->id;
                $product_name = $product->name;
            }
            $data['type_query_id'] = $product_id;
            $data['type'] = "product";
            $data['name'] = trans($product_name);
            $data['slug'] = $cat_sub[4];
            $data['photo'] = $photo;
            $data['category_banner'] = "";
        }

        /*if($link_type =="search"){
            $data['type_query_id'] = 0;
            $data['type'] = "search";
            $data['name'] = NULL;
            $data['slug'] = NULL;
            $data['photo'] = $photo;
            $data['category_banner'] = "";
        }*/

        return $data;
    }
}
