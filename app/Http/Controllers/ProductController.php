<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\ProductStock;
use App\Category;
use App\Language;
use Auth;
use App\SubSubCategory;
use Session;
use ImageOptimizer;
use DB;
use CoreComponentRepository;
use Illuminate\Support\Str;
use App\User;
use App\SubCategory;
use Image;
use Illuminate\Support\Facades\Storage;
use App\MappingProduct;
use App\Gallery;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_products(Request $request)
    {
        //CoreComponentRepository::instantiateShopRepository();
      
        $type = 'In House';
        $col_name = null;
        $query = null;
        $sort_search = null;

        // $products = Product::where('added_by', 'admin')->where('status', 1);
        $products = DB::table('products')->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')->where('products.added_by', 'admin')->where('products.status', 1);

        if ($request->type != null){
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }
        // if ($request->search != null){
        //     $products = $products->where('name', 'like', '%'.$request->search.'%');
        //     // $products = $products->Where('sku', $request->search);
        //     // $products = ProductStock::where('sku', 'like', '%'.$request->search.'%');
        //     $sort_search = $request->search;
        // }

        if ($request->has('search')){
                $sort_search = $request->search;
                $products = $products->where('products.name', 'like', '%'.$sort_search.'%')->orWhere('product_stocks.sku', $sort_search);
                // $products = $products->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')->where('products.name', 'like', '%'.$sort_search.'%')->orWhere('product_stocks.sku', 'like', '%'.$sort_search.'%');
                
             } 
             // DB::enableQueryLog();
             // $products = $products->get();
             // dd(DB::getQueryLog()); 
        // $products = $products->where('digital', 0)->orderBy('created_at', 'desc')->paginate(15);
        $products = $products->where('digital', 0)->select('products.*','product_stocks.sku')->orderBy('products.id', 'desc')->paginate(15);

        return view('products.index', compact('products','type', 'col_name', 'query', 'sort_search'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function seller_products(Request $request)
    {
        $col_name = null;
        $query = null;
        $seller_id = null;
        $sort_search = null;
        $products = Product::where('added_by', 'seller');
        if ($request->has('user_id') && $request->user_id != null) {
            $products = $products->where('user_id', $request->user_id);
            $seller_id = $request->user_id;
        }
        if ($request->search != null){
            $products = $products
                        ->where('name', 'like', '%'.$request->search.'%');
            $sort_search = $request->search;
        }
        if ($request->type != null){
            $var = explode(",", $request->type);
            $col_name = $var[0];
            $query = $var[1];
            $products = $products->orderBy($col_name, $query);
            $sort_type = $request->type;
        }

        $products = $products->orderBy('created_at', 'desc')->paginate(15);
        $type = 'Seller';

        return view('products.index', compact('products','type', 'col_name', 'query', 'seller_id', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         // dd($request->all());
        try {
            $check_product = Product::where('name', $request->name)->get();
            if(count($check_product)==0){
                
                    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();

                    $product = new Product;
                    $product->name = $request->name;
                    $product->added_by = $request->added_by;
                    

                    if(Auth::user()->user_type == 'seller'){
                        $userInfo = \App\User::with('seller')->where('id', Auth::user()->id)->first();
                        $product->user_id = Auth::user()->id;
                        $product->manage_by = $userInfo->seller->seller_type;
                        $product->user_id = $userInfo->id;
                    }
                    else{
                        $product->user_id = \App\User::where('user_type', 'admin')->first()->id;
                        $product->manage_by = 0;
                        $product->user_id = 0;
                    }

                    $product->category_id = $request->category_id;
                    $product->subcategory_id = $request->subcategory_id;
                    $product->subsubcategory_id = $request->subsubcategory_id;
                    $product->brand_id = $request->brand_id;
                    $product->current_stock = $request->current_stock;
                    $product->barcode = $request->barcode;

                    if ($refund_request_addon != null && $refund_request_addon->activated == 1) {
                        if ($request->refundable != null) {
                            $product->refundable = 1;
                        }
                        else {
                            $product->refundable = 0;
                        }
                    }

                    $photos = array();

                    if($request->hasFile('photos')){
                        foreach ($request->photos as $key => $photo) {
                            $path = $photo->store('uploads/products/photos');
                            array_push($photos, $path);
                            //ImageOptimizer::optimize(base_path('public/').$path);
                        }
                        $product->photos = json_encode($photos);
                    }

                    if($request->hasFile('thumbnail_img')){
                        // $destinationPath = public_path('uploads/products/thumbnail');
                        // $pat = 'uploads/products/thumbnail';
                        // $thumbnail = $request->thumbnail_img;
                        // $imagename = time().'.'.$thumbnail->extension();
                        // $img = Image::make($thumbnail->path());
                        // $img->resize(100, 100, function ($constraint) {
                        //     $constraint->aspectRatio();
                        // })->save($destinationPath.'/'.$imagename);
                        // $img->encode();
                        // $product->thumbnail_img = $pat.'/'.$imagename;
                        // $path = Storage::disk('s3')->put($pat, $img->__toString(), ['visibility' => 'public']);
                        $product->thumbnail_img = $request->thumbnail_img->store('uploads/products/thumbnail');
                        //ImageOptimizer::optimize(base_path('public/').$product->thumbnail_img);
                    }

                    $request->tags = array_map('strtolower',$request->tags);
                    $request->jsontags = array_map('strtolower',$request->jsontags);

                    $product->unit = $request->unit;
                    $product->min_qty = $request->min_qty;
                    $product->tags = implode('|',$request->tags);
                    $product->json_tags = json_encode(array_map('trim', array_filter(explode(',', $request->jsontags[0]), 'trim')));
                    $product->hsn_code = $request->hsn_code;
                    $product->description = $request->description;
                    $product->video_provider = $request->video_provider;
                    $product->video_link = $request->video_link;
                    $product->unit_price = $request->purchase_price;
                    $product->purchase_price = $request->purchase_price;
                    $product->tax = $request->tax;
                    $product->tax_type = $request->tax_type;
                    $product->discount = $request->discount;
                    $product->discount_type = $request->discount_type;
                    $product->shipping_type = $request->shipping_type;
                    // if(!is_null($request->gift_card)){
                    //     $product->gift_card = 1;
                    // }
                    if ($request->has('shipping_type')) {
                        if($request->shipping_type == 'free'){
                            $product->shipping_cost = 0;
                        }
                        elseif ($request->shipping_type == 'flat_rate') {
                            $product->shipping_cost = $request->flat_shipping_cost;
                        }
                    }
                    $product->meta_title = $request->meta_title;
                    $product->meta_description = $request->meta_description;

                    if($request->hasFile('meta_img')){
                        $product->meta_img = $request->meta_img->store('uploads/products/meta');
                        //ImageOptimizer::optimize(base_path('public/').$product->meta_img);
                    } else {
                        $product->meta_img = $product->thumbnail_img;
                    }

                    if($product->meta_title == null) {
                        $product->meta_title = $product->name;
                    }

                    if($product->meta_description == null) {
                        $product->meta_description = $product->description;
                    }

                    if($request->hasFile('pdf')){
                        $product->pdf = $request->pdf->store('uploads/products/pdf');
                    }

                    $product->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name)).'-'.Str::random(5);

                    if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
                        $product->colors = json_encode($request->colors);
                    }
                    else {
                        $colors = array();
                        $product->colors = json_encode($colors);
                    }

                    $choice_options = array();

                    if($request->has('choice_no')){
                        foreach ($request->choice_no as $key => $no) {
                            $str = 'choice_options_'.$no;

                            $item['attribute_id'] = $no;
                            $item['values'] = explode(',', implode('|', $request[$str]));

                            array_push($choice_options, $item);
                        }
                    }

                    if (!empty($request->choice_no)) {
                        $product->attributes = json_encode($request->choice_no);
                    }
                    else {
                        $product->attributes = json_encode(array());
                    }

                    $product->choice_options = json_encode($choice_options);

                    //$variations = array();

                    if($product->save()){

                        //combinations start
                        $options = array();
                        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
                            $colors_active = 1;
                            array_push($options, $request->colors);
                        }

                        if($request->has('choice_no')){
                            foreach ($request->choice_no as $key => $no) {
                                $name = 'choice_options_'.$no;
                                $my_str = implode('|',$request[$name]);
                                array_push($options, explode(',', $my_str));
                            }
                        }

                        //Generates the combinations of customer choice options
                        $combinations = combinations($options);
                        if(count($combinations[0]) > 0){
                            $product->variant_product = 1;
                            foreach ($combinations as $key => $combination){
                                $str = '';
                                foreach ($combination as $key => $item){
                                    if($key > 0 ){
                                        $str .= '-'.str_replace(' ', '', $item);
                                    }
                                    else{
                                        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
                                            $color_name = \App\Color::where('code', $item)->first()->name;
                                            $str .= $color_name;
                                        }
                                        else{
                                            $str .= str_replace(' ', '', $item);
                                        }
                                    }
                                }
                                // $item = array();
                                // $item['price'] = $request['price_'.str_replace('.', '_', $str)];
                                // $item['sku'] = $request['sku_'.str_replace('.', '_', $str)];
                                // $item['qty'] = $request['qty_'.str_replace('.', '_', $str)];
                                // $variations[$str] = $item;

                                $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                                if($product_stock == null){
                                    $product_stock = new ProductStock;
                                    $product_stock->product_id = $product->id;
                                }

                                //17dec2021
                                $product_sku_name = ProductStock::orderBy('id', 'desc')->first();
                                $last_sku = $product_sku_name['sku'];
                                $split_sku = explode("ROZ",$last_sku);
                                $new_sku = $split_sku[1] + 1;
                                $sku = 'ROZ'.$new_sku;
                                // echo '<pre>'; print_r($sku); die;

                                $product_stock->variant = $str;
                                $product_stock->price = $request['price_'.str_replace('.', '_', $str)];
                                // $product_stock->sku = $request['sku_'.str_replace('.', '_', $str)];
                                $product_stock->sku = $sku;
                                $product_stock->discount = $request['discount_'.str_replace('.', '_', $str)];
                                $product_stock->discount_type = $request['discount_type_'.str_replace('.', '_', $str)];
                                $product_stock->qty = $request['qty_'.str_replace('.', '_', $str)];
                                $product_stock->save();
                            }
                        }
                        
                        if(Auth::user()->user_type == 'seller'){
                            $userInfo = \App\User::with('seller')->where('id', Auth::user()->id)->first();
                            if($userInfo->seller->seller_type == 0){

                                $response = ECom_Sku_Create($product->id);
                                $res = json_decode($response, true);
                                if($res["responseMessage"] == "Success"){
                                    $data['ecom_sku_status'] = '1';
                                    $data['ecom_sku_response'] = $response;
                                    $products = Product::where('id', $product->id)->update($data);
                                }
                            }
                        }

                        if(Auth::user()->user_type == 'admin'){
                            if($request->manage_type_id == 0){
                                
                                $response = ECom_Sku_Create($product->id);
                                $res = json_decode($response, true);
                                if($res["responseMessage"] == "Success"){
                                    $data['ecom_sku_status'] = '1';
                                    $data['ecom_sku_response'] = '0';
                                    // $products = Product::where('id', $product->id)->update($data);
                                }
                            }
                        }
                    }
                    //combinations end

                    foreach (Language::all() as $key => $language) {
                        $data = openJSONFile($language->code);
                        $data[$product->name] = $product->name;
                        //saveJSONFile($language->code, $data);
                    }

                    $product->save();
                    flash(translate('Product has been inserted successfully'))->success();
                    if(Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff'){
                        return redirect()->route('products.admin');
                    }
                    else{
                        if(\App\Addon::where('unique_identifier', 'seller_subscription')->first() != null && \App\Addon::where('unique_identifier', 'seller_subscription')->first()->activated){
                            $seller = Auth::user()->seller;
                            $seller->remaining_uploads -= 1;
                            $seller->save();
                        }
                        return redirect()->route('seller.products');
                    }
            }else{
                flash(translate('Product name already exist'))->error();
                return back();
           } 
        } catch (Exception $e) {
            flash(translate('Something went wrong'))->error();
            return back();
        }              
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function admin_product_edit($id)
    {
        $product = Product::findOrFail(decrypt($id));
        $tags = json_decode($product->tags);
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories', 'tags'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function seller_product_edit($id)
    {
        $product = Product::findOrFail(decrypt($id));
        $tags = json_decode($product->tags);
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $check_product = Product::where('name', $request->name)->whereNotIn('id', [$id])->get();
        if(count($check_product)==0){
                    $request->tags = array_map('strtolower',$request->tags);
                    $request->jsontags = array_map('strtolower',$request->jsontags);
                    $refund_request_addon = \App\Addon::where('unique_identifier', 'refund_request')->first();
                    $product = Product::findOrFail($id);
                    $product->name = $request->name;
                    $product->category_id = $request->category_id;
                    $product->subcategory_id = $request->subcategory_id;
                    $product->subsubcategory_id = $request->subsubcategory_id;
                    $product->brand_id = $request->brand_id;
                    $product->current_stock = $request->current_stock;
                    $product->barcode = $request->barcode;
                    // if(!is_null($request->gift_card)){
                    //     $product->gift_card = 1;
                    // }else{
                    //     $product->gift_card = 0;
                    // }
                    if ($refund_request_addon != null && $refund_request_addon->activated == 1) {
                        if ($request->refundable != null) {
                            $product->refundable = 1;
                        }
                        else {
                            $product->refundable = 0;
                        }
                    }

                    if($request->has('previous_photos')){
                        $photos = $request->previous_photos;
                    }
                    else{
                        $photos = array();
                    }

                    if($request->hasFile('photos')){
                        foreach ($request->photos as $key => $photo) {
                            $path = $photo->store('uploads/products/photos');
                            array_push($photos, $path);
                            //ImageOptimizer::optimize(base_path('public/').$path);
                        }
                    }
                    $product->photos = json_encode($photos);

                    $product->thumbnail_img = $request->previous_thumbnail_img;
                    if($request->hasFile('thumbnail_img')){
                        $product->thumbnail_img = $request->thumbnail_img->store('uploads/products/thumbnail');
                        //ImageOptimizer::optimize(base_path('public/').$product->thumbnail_img);
                    }

                    $product->unit = $request->unit;
                    $product->min_qty = $request->min_qty;
                    $product->tags = implode('|',$request->tags);
                    $product->json_tags = json_encode(array_map('trim', array_filter(explode(',', $request->jsontags[0]), 'trim')));
                    $product->description = $request->description;
                    $product->video_provider = $request->video_provider;
                    $product->video_link = $request->video_link;
                    $product->unit_price = $request->purchase_price;
                    $product->purchase_price = $request->purchase_price;
                    $product->tax = $request->tax;
                    $product->tax_type = $request->tax_type;
                    $product->discount = $request->discount;
                    $product->shipping_type = $request->shipping_type;
                    if ($request->has('shipping_type')) {
                        if($request->shipping_type == 'free'){
                            $product->shipping_cost = 0;
                        }
                        elseif ($request->shipping_type == 'flat_rate') {
                            $product->shipping_cost = $request->flat_shipping_cost;
                        }
                    }
                    $product->discount_type = $request->discount_type;
                    $product->meta_title = $request->meta_title;
                    $product->meta_description = $request->meta_description;

                    $product->meta_img = $request->previous_meta_img;
                    if($request->hasFile('meta_img')){
                        $product->meta_img = $request->meta_img->store('uploads/products/meta');
                        //ImageOptimizer::optimize(base_path('public/').$product->meta_img);
                    }

                    if($product->meta_title == null) {
                        $product->meta_title = $product->name;
                    }

                    if($product->meta_description == null) {
                        $product->meta_description = $product->description;
                    }

                    if($request->hasFile('pdf')){
                        $product->pdf = $request->pdf->store('uploads/products/pdf');
                    }

                    $product->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name)).'-'.substr($product->slug, -5);

                    if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
                        $product->colors = json_encode($request->colors);
                    }
                    else {
                        $colors = array();
                        $product->colors = json_encode($colors);
                    }

                    $choice_options = array();

                    if($request->has('choice_no')){
                        foreach ($request->choice_no as $key => $no) {
                            $str = 'choice_options_'.$no;

                            $item['attribute_id'] = $no;
                            $item['values'] = explode(',', implode('|', $request[$str]));

                            array_push($choice_options, $item);
                        }
                    }

                    if($product->attributes != json_encode($request->choice_attributes)){
                        foreach ($product->stocks as $key => $stock) {
                            $stock->delete();
                        }
                    }

                    if (!empty($request->choice_no)) {
                        $product->attributes = json_encode($request->choice_no);
                    }
                    else {
                        $product->attributes = json_encode(array());
                    }

                    $product->choice_options = json_encode($choice_options);

                    foreach (Language::all() as $key => $language) {
                        $data = openJSONFile($language->code);
                        unset($data[$product->name]);
                        $data[$request->name] = "";
                        //saveJSONFile($language->code, $data);
                    }

                    //combinations start
                    $options = array();
                    if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
                        $colors_active = 1;
                        array_push($options, $request->colors);
                    }

                    if($request->has('choice_no')){
                        foreach ($request->choice_no as $key => $no) {
                            $name = 'choice_options_'.$no;
                            $my_str = implode('|',$request[$name]);
                            array_push($options, explode(',', $my_str));
                        }
                    }

                    $combinations = combinations($options);
                    if(count($combinations[0]) > 0){
                        $product->variant_product = 1;
                        foreach ($combinations as $key => $combination){
                            $str = '';
                            foreach ($combination as $key => $item){
                                if($key > 0 ){
                                    $str .= '-'.str_replace(' ', '', $item);
                                }
                                else{
                                    if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
                                        $color_name = \App\Color::where('code', $item)->first()->name;
                                        $str .= $color_name;
                                    }
                                    else{
                                        $str .= str_replace(' ', '', $item);
                                    }
                                }
                            }

                            // $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                            $product_stock = ProductStock::where('product_id', $product->id)->first();
                            if($product_stock == null){
                                $product_stock = new ProductStock;
                                $product_stock->product_id = $product->id;
                            }

                            $product_stock->variant = $str;
                            $product_stock->price = $request['price_'.str_replace('.', '_', $str)];
                            // $product_stock->sku = $request['sku_'.str_replace('.', '_', $str)];
                            $product_stock->sku = $product_stock->sku;
                            $product_stock->discount = $request['discount_'.str_replace('.', '_', $str)];
                            $product_stock->discount_type = $request['discount_type_'.str_replace('.', '_', $str)];
                            $product_stock->qty = $request['qty_'.str_replace('.', '_', $str)];

                            $product_stock->save();
                        }
                    }

                    $product->save();

                    flash(translate('Product has been updated successfully'))->success();
                    if(Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff'){
                        return redirect()->route('products.admin');
                    }
                    else{
                        return redirect()->route('seller.products');
                    }
           }else{
            flash(translate('Product name already exist'))->error();
            return back();
       }           
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if(Product::destroy($id)){
            foreach (Language::all() as $key => $language) {
                $data = openJSONFile($language->code);
                unset($data[$product->name]);
                //saveJSONFile($language->code, $data);
            }
            flash(translate('Product has been deleted successfully'))->success();
            if(Auth::user()->user_type == 'admin'){
                return redirect()->route('products.admin');
            }
            else{
                return redirect()->route('seller.products');
            }
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    //03-11-2021
    public function delete_product($id){
        $product = Product::findOrFail($id);
        
        $update = DB::table('products')
        ->where('id', $id)
        ->update([
            'status'     => 0,
            'updated_by' =>Auth::user()->id,
            
        ]);
        
        if($update){
            flash(translate('Product has been deleted successfully'))->success();
            if(Auth::user()->user_type == 'admin'){
                return redirect()->route('products.admin');
            }
            else{
                return redirect()->route('seller.products');
            }
        }else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    /**
     * Duplicates the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function duplicate($id)
    {
        $product = Product::find($id);
        $product_new = $product->replicate();
        $product_new->slug = substr($product_new->slug, 0, -5).Str::random(5);

        if($product_new->save()){
            flash(translate('Product has been duplicated successfully'))->success();
            if(Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff'){
                return redirect()->route('products.admin');
            }
            else{
                return redirect()->route('seller.products');
            }
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function get_products_by_subcategory(Request $request)
    {
        $products = Product::where('subcategory_id', $request->subcategory_id)->get();
        return $products;
    }

    public function get_products_by_brand(Request $request)
    {
        $products = Product::where('brand_id', $request->brand_id)->get();
        return view('partials.product_select', compact('products'));
    }

    public function updateTodaysDeal(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->todays_deal = $request->status;
        if($product->save()){
            return 1;
        }
        return 0;
    }

    public function updateSearchStatus(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->search_status = $request->status;
        if($product->save()){
            return 1;
        }
        return 0;
    }

    public function updatePublished(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->published = $request->status;

        if($product->added_by == 'seller' && \App\Addon::where('unique_identifier', 'seller_subscription')->first() != null && \App\Addon::where('unique_identifier', 'seller_subscription')->first()->activated){
            $seller = $product->user->seller;
            if($seller->invalid_at != null && Carbon::now()->diffInDays(Carbon::parse($seller->invalid_at), false) <= 0){
                return 0;
            }
        }

        $product->save();
        return 1;
    }

    public function updateFeatured(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->featured = $request->status;
        if($product->save()){
            return 1;
        }
        return 0;
    }

    public function updatetopproducts(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->top_product = $request->status;
        if($product->save()){
            return 1;
        }
        return 0;
    }

    public function sku_combination(Request $request)
    {
        $options = array();
        $optionsName = array();

        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
            $colors_active = 1;
            array_push($options, $request->colors);
        }
        else {
            $colors_active = 0;
        }
      
        $unit_price = $request->unit_price;
        $product_name = $request->name;

        if($request->has('choice_no')){
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_'.$no;
                $my_str = implode('', $request[$name]);
                $str = substr($my_str, 0,1);
                array_push($options, explode(',', $my_str));
                array_push($optionsName, explode(',', $my_str));
            }
        }

        $combinations = combinations($options);
        $combinationsName = combinations($optionsName);

        if(Auth::user()->user_type == 'seller'){
            $seller = User::with('seller')->where('id', Auth::user()->id)->where('user_type', 'seller')->first();
            $manageby = $seller->seller->seller_type;
        }
        else{
            $seller = User::where('id', $request->seller_id)->where('user_type', 'seller')->first();
            $manageby = $request->manage_type_id;
        }

        $category  = Category::where('id', $request->category_id)->first();
        $products = Product::orderBy('id', 'desc')->first();
        $productStock = ProductStock::orderBy('id', 'desc')->first();

        if(!empty($products)){
            $producstID = sprintf("%02d", $products->id);
        }else{
            $producstID = sprintf("%02d", 1);
        }

        if(!empty($productStock)){
           $productStockID = sprintf("%02d", $productStock->id);
        }else{
            $productStockID = sprintf("%02d", 1);
        }

        if(!empty($request->subcategory_id)){
            $subCategory = SubCategory::where('id', $request->subcategory_id)->first();
          
            $data = substr($category->name, 0,2).'-'.substr($subCategory->name, 0,2).'-'.$producstID.$request->manage_type_id;
        }else{
            $data = substr($category->name, 0,2).substr($request->name, 0,2).'-'.$producstID.$request->manage_type_id;
        }

        return view('partials.sku_combinations', compact('combinations', 'unit_price', 'colors_active', 'product_name'))->with([
        'data' => $data, 'productStockID' => $productStockID, 'manageby' => $manageby]);
    }

    public function set_attribute_session($combinations,$request)
    {
        $variant_price = array();
        $variant_qty = array();
        $variant_purchase_price = array();
        $variant_discount = array();
        $discountType = array();
        foreach($combinations as $key => $value)
        {
            foreach($value as $akey => $attr)
            {
                $vattr = str_replace(' ','',$attr);
                $vattr = str_replace('.','_',$vattr);
                $vprice = 'price_'.$vattr;
                $vqty = 'qty_'.$vattr;
                $vpprice = 'purchase_'.$vattr;
                $vdiscount = 'discount_'.$vattr;
                $vdiscount_type = 'discountType_'.$vattr;
                $variant_price[$vattr] = $request->$vprice;
                $variant_qty[$vattr] = $request->$vqty;
                $variant_purchase_price[$vattr] = $request->$vpprice;
                $variant_discount[$vattr] = $request->$vdiscount;
                $discountType[$vattr] = $request->$vdiscount_type;
                Session::put('vprice',$variant_price);
                Session::put('vqty',$variant_qty);
                Session::put('vpprice',$variant_purchase_price);
                Session::put('variant_discount',$variant_discount);
                Session::put('variant_discount_type',$discountType);
            }
        }
    }

    public function sku_combination_edit(Request $request)
    {
        $product = Product::findOrFail($request->id);

        $options = array();
        if($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0){
            $colors_active = 1;
            array_push($options, $request->colors);
        }
        else {
            $colors_active = 0;
        }

        $product_name = $request->name;
        $unit_price = $request->unit_price;
        $discount = $request->discount;

        if($request->has('choice_no')){
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_'.$no;
                $my_str = implode('|', $request[$name]);
                array_push($options, explode(',', $my_str));
            }
        }

        $combinations = combinations($options);
        $this->set_attribute_session($combinations,$request);
        if(Auth::user()->user_type == 'seller'){
            $seller = User::with('seller')->where('id', Auth::user()->id)->where('user_type', 'seller')->first();
            $manageby = $seller->seller->seller_type;
        }
        else{
            $seller = User::where('id', $request->seller_id)->where('user_type', 'seller')->first();
            $manageby = $request->manage_type_id;
        }

        $category  = Category::where('id', $request->category_id)->first();
        $products = Product::orderBy('id', 'desc')->where('id', '<', $product->id)->first();
        $productStock = ProductStock::orderBy('id', 'desc')->where('product_id', '<', $product->id)->first();

        if(!empty($products)){
            $producstID = sprintf("%02d", $products->id);
        }else{
            $producstID = sprintf("%02d", 1);
        }

        if(!empty($productStock)){
           $productStockID = sprintf("%02d", $productStock->id);
        }else{
            $productStockID = sprintf("%02d", 1);
        }
       
        if(!empty($request->subcategory_id)){
            $subCategory = SubCategory::where('id', $request->subcategory_id)->first();
            $data = substr($category->name, 0,2).'-'.substr($subCategory->name, 0,2).'-'.$producstID.$request->manage_type_id;
        }elseif(empty($request->subcategory_id)){
            
            $data = substr($category->name, 0,2).substr($product_name, 0,2).'-'.$producstID.$request->manage_type_id;
          
        }
    
        return view('partials.sku_combinations_edit', compact('combinations', 'unit_price', 'colors_active', 'product_name', 'product','discount'))->with([
        'data' => $data, 'productStockID' => $productStockID, 'manageby' => $manageby]);;
    }

    public function getlist(Request $request)
    {
        $id=$request->get('id');
     
        $optionattributes = DB::table('attribute_options_values')
         ->where('attribute_id',$id)
         ->select('attribute_option_value_id', 'attribute_option_value' )
         ->get();

         if(count($optionattributes)>0){
           echo '<select name="choice_opt[]" id="choice_opt_'.$id.'" class="form-control demo-select2 option_attributeval" multiple>';
                 foreach ($optionattributes as $key => $value) {

                    echo '<option name="'.$value->attribute_option_value.'" id="myid_'.$value->attribute_option_value_id.'" value="' . $value->attribute_option_value_id . '">' . $value->attribute_option_value . '</option>';
                 }
            echo '</select>';
         }else{
            echo 'No data found';
         }   
         echo "<script>
                 $(document).ready(function() {
                    $('.option_attributeval').select2();                                        
                    $('#choice_opt_$id').change(function(){ 
                        
                        var selected = [];
                        if ($(this).select2('data').length){
                        $.each($(this).select2('data'), function(key, item){
                            selected.push(item.text);
                        });
                        // alert(selected.toString());                        
                        // console.log(selected.toString())
                        }
                        // alert($id);
                        // alert(selected.toString()); 
                        $('#choice_options_$id').val(selected.toString());                                                 
                        update_sku();                                                                                                                  
                    });
                });
              </script>"; 
         // return $optionattributes;
        // $attribute = Attribute::findOrFail($id);
        // return view('attribute.attributeval', compact('attribute', 'optionattributes'));
    }


    public function minPurchaseLimit(REQUEST $request){
        $product = Product::findOrFail($request->id);
        $product->max_purchase_qty = $request->max_purchase_qty;
        if($product->save()){
            return 1;

        }else{
            return 0;
        }

    }

    public function min_PurchaseLimit(REQUEST $request){
        $productmap = MappingProduct::findOrFail($request->id);
        $productmap->max_purchaseprice = $request->max_purchase_qty;
        if($productmap->save()){
            return 1;

        }else{
            return 0;
        }

    }

    public function admin_media(Request $request)
    {
        // $type = 'In House';
        $gallery = Gallery::orderBy('created_at', 'desc')->paginate(15);
        return view('products.img_index', compact('gallery'));
    }

    public function createmedia()
    {       
        return view('products.add_img');
    }

    public function storemedia(Request $request)
    {
        // dd($request->all());
        $photos = array();
        $gallery = new Gallery;
        if($request->hasFile('photos')){
            foreach ($request->photos as $key => $photo) {
                $path = $photo->store('uploads/products/photos');
                array_push($photos, $path);
            }
            $gallery->photos = json_encode($photos);
        }

        if($request->hasFile('thumbnail_img')){
            $gallery->thumbnail_img = $request->thumbnail_img->store('uploads/products/thumbnail');
        }        

        $gallery->save();
        flash(translate('Media has been inserted successfully'))->success();
        // return redirect()->route('products.add_img'); 
         return view('products.add_img');     
    }

}
