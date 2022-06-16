<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Auth;
use Hash;
use App\Category;
use App\FlashDeal;
use App\Brand;
use App\SubCategory;
use App\SubSubCategory;
use App\Product;
use App\ProductStock;
use App\PickupPoint;
use App\CustomerPackage;
use App\CustomerProduct;
use App\User;
use App\Seller;
use App\Shop;
use App\Color;
use App\Order;
use App\State;
use App\City;
use App\BusinessSetting;
use App\Http\Controllers\SearchController;
use ImageOptimizer;
use Cookie;
use Illuminate\Support\Str;
use App\Mail\SecondEmailVerifyMailManager;
use Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\PeerPartner;
use App\PeerSetting;
use DB;
use MappingProduct;
use App\Area;
use App\Services\HttpService;

class HomeController extends Controller
{
    public $shortId = [];

    public function __construct()
    {
        $pincode = Cookie::get('pincode');
        if(Cookie::has('sid')){
            $this->shortId['sorting_hub_id'] = decrypt(Cookie::get('sid'));
        }else{
            $this->shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')
                ->selectRaw('user_id as sorting_hub_id')
                ->first('sorting_hub_id');
        }
        //$this->middleware('auth');
    }
    
    public function login()
    {
        if(Auth::check()){
            return redirect()->route('home');
        }
        return view('frontend.user_login');
    }
    public function login_otp()
        {
            if(Auth::check()){
                return redirect()->route('home');
            }
            return view('frontend.user_login_otp');
        }

    public function verify_otp()
        {
            if(Auth::check()){
                return redirect()->route('home');
            }
            return view('frontend.verify_otp');
        }

    public function register_phone()
        {
            if(Auth::check()){
                return redirect()->route('home');
            }
            return view('frontend.user_registration_phone');
        }
          

    public function user_otp()
    {
        if(Auth::check()){
            return redirect()->route('home');
        }
        return view('frontend.user_otp_verify');
    }

        
    public function registration(Request $request)
    {
        if(Auth::check()){
            return redirect()->route('home');
        }
        if($request->has('referral_code')){
            Cookie::queue('referral_code', $request->referral_code, 43200);
        }
        return view('frontend.user_registration');
    }

    // public function user_login(Request $request)
    // {
    //     $user = User::whereIn('user_type', ['customer', 'seller'])->where('email', $request->email)->first();
    //     if($user != null){
    //         if(Hash::check($request->password, $user->password)){
    //             if($request->has('remember')){
    //                 auth()->login($user, true);
    //             }
    //             else{
    //                 auth()->login($user, false);
    //             }
    //             return redirect()->route('dashboard');
    //         }
    //     }
    //     return back();
    // }

    public function cart_login(Request $request)
    {
        $user = User::whereIn('user_type', ['customer', 'seller','partner','staff'])->where('banned',0)->where(function($query) use($request){
            return $query->where('email', $request->email)->orWhere('phone', $request->email);
         })->first();
        if($user != null){
            $ids = $user->id;
            if ($ids != ''){
                $peer_codes = \App\PeerPartner::where('user_id', $ids)->where('verification_status', 1)->where('peertype_approval', 0)->select('code', 'user_id', 'discount')->first(); 
                if(!empty($peer_codes)){
                     Session::put('partner_id', $peer_codes->user_id);
                     Session::put('referal_discount', $peer_codes->discount);
                     Session::put('referal_code', $peer_codes->code);
                }
                //13-10-2021 - start
                else{
                    if(!empty($user->used_referral_code)){
                        $prev_peer_codes = \App\PeerPartner::where('code', $user->used_referral_code)->where('verification_status', 1)->where('peertype_approval', 0)->select('code','user_id', 'discount')->first(); 
                        if(!empty($prev_peer_codes)){
                            Session::put('partner_id', $prev_peer_codes->user_id);
                            Session::put('referal_discount', $prev_peer_codes->discount);
                            Session::put('referal_code', $prev_peer_codes->code);
                        }

                    }
                }
                //13-10-2021 - end
            }
            updateCartSetup();
            if(Hash::check($request->password, $user->password)){
                if($request->has('remember')){
                    Session::flash('success','Login successfull');
                    auth()->login($user, true);
                }
                else{
                    auth()->login($user, false);
                }
            }
        }
        return back();
    }


    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_dashboard()
    {
        return view('dashboard');
    }

    /**
     * Show the customer/seller dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        if(Auth::user()->user_type == 'seller'){
            return view('frontend.seller.dashboard');
        }
        elseif(Auth::user()->user_type == 'customer'){
            return view('frontend.customer.dashboard');
        }
        elseif(Auth::user()->user_type == 'partner'){
            return view('frontend.customer.dashboard');
        }
        elseif(Auth::user()->user_type == 'staff' && Auth::user()->peer_partner==1){
            return view('frontend.customer.dashboard');
        }
        else {
            abort(404);
        }
    }

    public function profile(Request $request)
    {
        if(Auth::user()->user_type == 'customer'){
            return view('frontend.customer.profile');
        }
        elseif(Auth::user()->user_type == 'seller'){
            return view('frontend.seller.profile');
        }
        elseif(Auth::user()->user_type == 'partner'){
            return view('frontend.customer.profile');
        }
        elseif(Auth::user()->user_type == 'staff' && Auth::user()->peer_partner==1){
            return view('frontend.customer.profile');
        }
        else {
            abort(404);
        }
    }

    public function customer_update_profile(Request $request)
    {
        if(env('DEMO_MODE') == 'On'){
            flash(translate('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        $user = Auth::user();
        $user->name = $request->name;
        $user->address = $request->address;
        $user->country = $request->country;
        $user->city = $request->city;
        $user->postal_code = $request->postal_code;
        $user->phone = $request->phone;

        if($request->new_password != null && ($request->new_password == $request->confirm_password)){
            $user->password = Hash::make($request->new_password);
        }

        if($request->hasFile('photo')){
            
            $user->avatar_original = $request->photo->store('uploads/users');
            Storage::disk('s3')->put('/uploads/users', file_get_contents($request->photo), 'public');
        }

        if($user->save()){
            flash(translate('Your Profile has been updated successfully!'))->success();
            return redirect()->route('home');
            // return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }


    public function seller_update_profile(Request $request)
    {
        if(env('DEMO_MODE') == 'On'){
            flash(translate('Sorry! the action is not permitted in demo '))->error();
            return back();
        }

        $user = Auth::user();
        $user->name = $request->name;
        $user->address = $request->address;
        $user->country = $request->country;
        $user->city = $request->city;
        $user->postal_code = $request->postal_code;
        $user->phone = $request->phone;

        if($request->new_password != null && ($request->new_password == $request->confirm_password)){
            $user->password = Hash::make($request->new_password);
        }

        if($request->hasFile('photo')){
            $user->avatar_original = $request->photo->store('uploads');
        }

        $seller = $user->seller;
        $seller->cash_on_delivery_status = $request->cash_on_delivery_status;
        $seller->bank_payment_status = $request->bank_payment_status;
        $seller->bank_name = $request->bank_name;
        $seller->bank_acc_name = $request->bank_acc_name;
        $seller->bank_acc_no = $request->bank_acc_no;
        $seller->bank_routing_no = $request->bank_routing_no;

        if($user->save() && $seller->save()){
            flash(translate('Your Profile has been updated successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }

    /**
     * Show the application frontend home.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $msc = microtime(true);
        
            $shortId  = $this->shortId;
            $getSlider = [];    
            if(!empty($shortId)){
                // start sliders
                if(Cache::has('sliders'.$shortId['sorting_hub_id'])){
                    //Cache::forget('sliders'.$shortId['sorting_hub_id']);
                    if(!is_null(Cache::get('sliders'.$shortId['sorting_hub_id']))){
                        $getSlider = Cache::get('sliders'.$shortId['sorting_hub_id']);
                    
                    }else{
                    $getSlider = \App\SortingHubSlider::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'published'=>1])->get();
                    Cache::put('sliders'.$shortId['sorting_hub_id'],$getSlider,3600);
                    }
                    
                }
                else{
                $getSlider = \App\SortingHubSlider::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'published'=>1])->get();
                Cache::put('sliders'.$shortId['sorting_hub_id'],$getSlider,3600);
                }

                // start featured categories
                $featured_categories = featured_categories($shortId);
                
                // $getSlider = \App\SortingHubSlider::where(['sorting_hub_id'=>$getSortingHubId['sorting_hub_id'],'published'=>1])->get();
                // $featured_categories = featured_categories($getSortingHubId);
                // $getNews = \App\SortingHubNews::where('sorting_hub_id',$getSortingHubId['sorting_hub_id'])->first();
                // News start
                if(Cache::has('getnews'.$shortId['sorting_hub_id'])){
                    if(!is_null(Cache::get('getnews'.$shortId['sorting_hub_id']))){
                        $getNews = Cache::get('getnews'.$shortId['sorting_hub_id']);
                    }
                    else{
                        $getNews = \App\SortingHubNews::where('sorting_hub_id',$shortId['sorting_hub_id'])->first();
                        Cache::put('getnews'.$shortId['sorting_hub_id'],$getNews,3600);
                    }

                }
                else{
                    $getNews = \App\SortingHubNews::where('sorting_hub_id',$shortId['sorting_hub_id'])->first();
                    Cache::put('getnews'.$shortId['sorting_hub_id'],$getNews,3600);
                }
      
            }else
            {
               
            //$productIds = [];
            $featured_categories = \App\Category::where('featured', 1)->orderBy('sorting','asc')->get();
            $getSlider=\App\Slider::where('published', 1)->get();
            $getNews = "";

            }


        

           //$num_todays_deal = count(filter_products(\App\Product::where('published', 1)->where('todays_deal', 1 ))->get());
           //$brand_sliders = \App\BrandSlider::where('featured', 1)->get();
           //$msc = microtime(true)-$msc;
           //echo $msc." Seconds";exit;
           //$msc = microtime(true)-$msc;
           //echo $msc." Seconds";exit;
        return view('frontend.index',compact('shortId','featured_categories','getNews','getSlider'));
    }

    public function maintenance(){
        
        return view('frontend.maintenance');
    }

    public function flash_deal_details($slug)
    {
        //$flash_deal = FlashDeal::where('slug', $slug)->first();
        $shortId = "";
        if(!empty(Cookie::get('pincode'))){ 
            $pincode = Cookie::get('pincode');
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        
        }
        $flash_deal = FlashDeal::where('status', 1)->where('featured', 1)->where('sorting_hub_id',$shortId['sorting_hub_id'])->first();
        if($flash_deal != null)
            return view('frontend.flash_deal_details', compact('flash_deal'));
        else {
            abort(404);
        }
    }

    public function load_featured_section(){
        return view('frontend.partials.featured_products_section');
    }

    public function load_best_selling_section(){
        return view('frontend.partials.best_selling_section');
    }

    public function load_home_categories_section(Request $request){
        $shortId = $this->shortId;
        $offset = $request->offset;
        $client = new HttpService('http://elastic.rozana.in/api/v1/');
        $body['json'] = ["sorting_hub_id"=>$this->shortId['sorting_hub_id'],"offset"=>$offset];
        $response = $client->apiRequest("POST","elastic-search/home-categories-products",$body);
        $categories = $response->getData()->response->categories;
        return view('frontend.partials.home_categories_section',compact('offset','categories','shortId'));
    }

    public function load_best_sellers_section(){
        return view('frontend.partials.best_sellers_section');
    }

    public function load_banner_slider_section(){
         return view('frontend.partials.banner_slider');
    }

    public function load_finance_banner_section(){
         return view('frontend.partials.finance_banner_section');
    }

    public function load_master_banner_section(){
         return view('frontend.partials.maste_banner_section');
    }

    public function trackOrder(Request $request)
    {
        
        if($request->has('order_code')){
            $order = Order::where('code', $request->order_code)->first();
            if($order != null){
                return view('frontend.track_order', compact('order'));
            }
            $message = "Sorry! Your order doesn't exists";
            return view('frontend.track_order',compact('message'));
        }
        
        return view('frontend.track_order');
    }

    public function product(Request $request, $slug)
    {
        
        $detailedProduct  = Product::where('slug', $slug)->first();

        if($detailedProduct!=null && $detailedProduct->published){
            updateCartSetup();
            if($request->has('product_referral_code')){
                Cookie::queue('product_referral_code', $request->product_referral_code, 43200);
                Cookie::queue('referred_product_id', $detailedProduct->id, 43200);
            }
            if($detailedProduct->digital == 1){
                return view('frontend.digital_product_details', compact('detailedProduct'));
            }
            else {
                return view('frontend.product_details', compact('detailedProduct'));
            }
            // return view('frontend.product_details', compact('detailedProduct'));
        }
        abort(404);
    }

    public function shop($slug)
    {
        $shop  = Shop::where('slug', $slug)->first();
        if($shop!=null){
            $seller = Seller::where('user_id', $shop->user_id)->first();
            if ($seller->verification_status != 0){
                return view('frontend.seller_shop', compact('shop'));
            }
            else{
                return view('frontend.seller_shop_without_verification', compact('shop', 'seller'));
            }
        }
        abort(404);
    }

    public function filter_shop($slug, $type)
    {
        $shop  = Shop::where('slug', $slug)->first();
        if($shop!=null && $type != null){
            return view('frontend.seller_shop', compact('shop', 'type'));
        }
        abort(404);
    }

    public function listing(Request $request)
    {
        // $products = filter_products(Product::orderBy('created_at', 'desc'))->paginate(12);
        // return view('frontend.product_listing', compact('products'));
        return $this->search($request);
    }

    public function all_categories(Request $request)
    {
        $shortId  = $this->shortId;
        $categories = featured_categories($shortId);//Category::where('status', 1)->whereIn('id',sortinghubProductDetails("cid")['cid'])->get();
        return view('frontend.all_category', compact('categories'));
    }
    public function all_brands(Request $request)
    {
        $categories = Category::all();
        return view('frontend.all_brand', compact('categories'));
    }

    public function show_product_upload_form(Request $request)
    {
        if(\App\Addon::where('unique_identifier', 'seller_subscription')->first() != null && \App\Addon::where('unique_identifier', 'seller_subscription')->first()->activated){
            if(Auth::user()->seller->remaining_uploads > 0){
                $categories = Category::all();
                return view('frontend.seller.product_upload', compact('categories'));
            }
            else {
                flash(translate('Upload limit has been reached. Please upgrade your package.'))->warning();
                return back();
            }
        }
        $categories = Category::all();
        return view('frontend.seller.product_upload', compact('categories'));
    }

    public function show_product_edit_form(Request $request, $id)
    {
        $categories = Category::all();
        $product = Product::find(decrypt($id));
        return view('frontend.seller.product_edit', compact('categories', 'product'));
    }

    public function seller_product_list(Request $request)
    {
        $search = null;
        $products = Product::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc');
        if ($request->has('search')) {
            $search = $request->search;
            $products = $products->where('name', 'like', '%'.$search.'%');
        }
        $products = $products->paginate(10);
        return view('frontend.seller.products', compact('products', 'search'));
    }

    public function ajax_search(Request $request)
    {
        $query = $request->search;
        $keywords = array();
        $all_tags = Product::select('tags')->where(['published' => 1,'search_status'=>1])->get();
        $tags_array = array();
        foreach ($all_tags as $k => $tags) {
            $tg = explode(',', $tags->tags);
            foreach ($tg as $tk => $tar) {
                array_push($tags_array,$tar);
            }
        }
        $percentage = array();
        foreach ($tags_array as $atk => $alltag) {
            $similar = similar_text($alltag, $request->search);
            array_push($percentage, $similar);
        }
        $tags_per = array_combine($tags_array,$percentage);
        $dym = array_search(max($tags_per), $tags_per);
        

        $products = Product::where(['published' => 1,'search_status'=>1])->where('tags', 'like', '%'.$request->search.'%')->get();
        foreach ($products as $key => $product) {
            foreach (explode(',',$product->tags) as $key => $tag) {
                if(stripos($tag, $request->search) !== false){
                    if(sizeof($keywords) > 5){
                        break;
                    }
                    else{
                        if(!in_array(strtolower($tag), $keywords)){
                            array_push($keywords, strtolower($tag));
                        }
                    }
                }
            }
        }

        
        $productIds = [];
        $categoryIds = "";
        $subsubcategoryIds = "";
        $subcategoryIds = "";
        if(!empty(Cookie::get('pincode'))){
            $pincode = Cookie::get('pincode');
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->pluck('product_id')->all();
                $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
                $subcategoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('subcategory_id')->all();
                $subsubcategoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('subsubcategory_id')->all();
                $products = Product::whereIn('id',$productIds);
                
    
            }else{
                $products = Product::where(['published' => 1,'search_status'=>1])->whereIn('id',[]);

            }
        }else{
            $products = Product::where(['published' => 1,'search_status'=>1])->whereIn('id',[]);
        }

        $products->whereRaw('json_contains(json_tags, \'["' . strtolower($query). '"]\')');


           $products->orderByRaw("IF(name = '{$query}',2,IF(name LIKE '{$query}%',1,0)) DESC");
           $category = Category::where('name','like','%'.$query.'%')->orderByRaw("IF(name = '{$query}',2,IF(name LIKE '{$query}%',1,0)) DESC")->where(['status'=>1,'featured'=>1]);
           $subcategory = SubCategory::where('name','like','%'.$query.'%');
           $subsubcategory = SubSubCategory::where('name','like','%'.$query.'%');
           
           if(!empty(Cookie::get('pincode'))){

               $category->whereIn('id',$categoryIds);
               $subcategory->whereIn('id',$subcategoryIds);
               $subsubcategory->whereIn('id',$subsubcategoryIds);
           }
           

        //    if(!empty($category->first()))
        //    {               
        //        $products->orWhere('category_id',$category->first()['id']);
        //    }          
           
        //    if(!empty($subcategory->first()))
        //    {
        //        $products->orWhere('subcategory_id',$subcategory->first()['id']);
        //    }
          
        //    if(!empty($subsubcategory->first()))
        //    {
        //        $products->orWhere('subsubcategory_id',$subsubcategory->first()['id']);
        //    }

        $products = filter_products($products)->get()->take(3);

        $subsubcategories = SubSubCategory::where('name', 'like', '%'.$request->search.'%')->get()->take(3);

        $shops = Shop::whereIn('user_id', verified_sellers_id())->where('name', 'like', '%'.$request->search.'%')->get()->take(3);

        if(sizeof($keywords)>0 || sizeof($subsubcategories)>0 || sizeof($products)>0 || sizeof($shops) >0 || $dym){
            return view('frontend.partials.search_content', compact('products', 'subsubcategories', 'keywords', 'shops','dym'));
        }
        
        return '0';
    }

    public function search(Request $request)
    {
       
        $query = str_replace('\'', '', $request->q);
        $brand_id = (Brand::where('slug', $request->brand)->first() != null) ? Brand::where('slug', $request->brand)->first()->id : null;
        $sort_by = $request->sort_by;
        $category_id = (Category::where('slug', '=', $request->category)->first() != null) ? Category::where('slug', '=', $request->category)->first()->id : null;
        $subcategory_id = (SubCategory::where('slug', $request->subcategory)->first() != null) ? SubCategory::where('slug', $request->subcategory)->first()->id : null;
        $subsubcategory_id = (SubSubCategory::where('slug', $request->subsubcategory)->first() != null) ? SubSubCategory::where('slug', $request->subsubcategory)->first()->id : null;
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $seller_id = $request->seller_id;

        $conditions = ['published' => 1,'search_status'=>1];

        if($brand_id != null){
            $conditions = array_merge($conditions, ['brand_id' => $brand_id]);
        }
        if($category_id != null){
            $conditions = array_merge($conditions, ['category_id' => $category_id]);
        }
        if($subcategory_id != null){
            $conditions = array_merge($conditions, ['subcategory_id' => $subcategory_id]);
        }
        if($subsubcategory_id != null){
            $conditions = array_merge($conditions, ['subsubcategory_id' => $subsubcategory_id]);
        }
        if($seller_id != null){
            $conditions = array_merge($conditions, ['user_id' => Seller::findOrFail($seller_id)->user->id]);
        }
        $dym="";
        $productIds = [];
        $categoryIds = [];
        $subsubcategoryIds = [];
        $subcategoryIds = [];
        $productstocks = [];
        if(!empty(Cookie::get('pincode'))){
            $productstocks = ProductStock::where('price', 0)->pluck('product_id')->all();
            $pincode = Cookie::get('pincode');
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
       
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->where('flash_deal',0)->pluck('product_id')->all();
               
                if($query != null){
                    $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->pluck('product_id')->all();
                }
                
                //$productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->pluck('product_id')->all();
                $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
                $subcategoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('subcategory_id')->all();
                $subsubcategoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('subsubcategory_id')->all();
                $products = Product::whereIn('products.id',$productIds);
                // dd($products);
                $products = $products->whereNotIn('products.id', $productstocks);
                 // dd($products);

                
                
    
            }else{
                $products = Product::whereIn('id',[]);

            }
            $products->where($conditions);
        }else{
            $products = Product::where($conditions);
        }

        if($min_price != null && $max_price != null){
            $products = $products->where('unit_price', '>=', $min_price)->where('unit_price', '<=', $max_price);
        }    


        if($query != null){
            $this->store_search($query);
            $searchController = new SearchController;
            $searchController->store($request);
            $brand = Brand::where('name',$query)->first(); 
           // $products = $products->where('name', 'like', '%'.$query.'%')->orWhere('tags', 'like', '%'.$query.'%');
           
           $products->whereRaw('json_contains(json_tags, \'["' . strtolower($query). '"]\')');

        //    $products->where(function($q) use ($query) {
        //     $q->whereRaw('json_contains(json_tags, \'["' . strtolower($query). '"]\')')
        //         ->orWhere('name', 'like', '%'.$query.'%');
        // });
        //    $products->where('name', 'like', '%'.$query.'%');

        //    if(!empty($brand)){
        //     $products->where('brand_id',$brand['id']);
        //    }


           $products->orderByRaw("IF(name = '{$query}',2,IF(name LIKE '{$query}%',1,0)) DESC");
           $category = Category::where('name','like','%'.$query.'%')->orderByRaw("IF(name = '{$query}',2,IF(name LIKE '{$query}%',1,0)) DESC")->where(['status'=>1,'featured'=>1]);
           $subcategory = SubCategory::where('name','like','%'.$query.'%');
           $subsubcategory = SubSubCategory::where('name','like','%'.$query.'%');
           
           if(!empty(Cookie::get('pincode'))){

               $category->whereIn('id',$categoryIds);
               $subcategory->whereIn('id',$subcategoryIds);
               $subsubcategory->whereIn('id',$subsubcategoryIds);
           }
         

        //    if(!empty($category->first()))
        //    {               
        //        $products->orWhere('category_id',$category->first()['id']);
        //    }          
           
        //    if(!empty($subcategory->first()))
        //    {
        //        $products->orWhere('subcategory_id',$subcategory->first()['id']);
        //    }
          
        //    if(!empty($subsubcategory->first()))
        //    {
        //        $products->orWhere('subsubcategory_id',$subsubcategory->first()['id']);
        //    }

            $all_tags = Product::select('tags')->where('published',1)->get();
            $tags_array = array();
            foreach ($all_tags as $k => $tags) {
                $tg = explode(',', $tags->tags);
                foreach ($tg as $tk => $tar) {
                    array_push($tags_array,$tar);
                }
            }
            $percentage = array();
            foreach ($tags_array as $atk => $alltag) {
                $similar = similar_text($alltag, $query);
                array_push($percentage, $similar);
            }
            $tags_per = array_combine($tags_array,$percentage);
            $dym = array_search(max($tags_per), $tags_per);
        }

        if($sort_by != null){
            switch ($sort_by) {
                case '1':
                    $products->orderBy('created_at', 'desc');
                    break;
                case '2':
                    $products->orderBy('created_at', 'asc');
                    break;
                case '3':
                    $products->orderBy('unit_price', 'asc');
                    break;
                case '4':
                    $products->orderBy('unit_price', 'desc');
                    break;
                default:
                    // code...
                    break;
            }
        }


        $non_paginate_products = filter_products($products)->get();


        //Attribute Filter

        $attributes = array();
        foreach ($non_paginate_products as $key => $product) {
            if($product->attributes != null && is_array(json_decode($product->attributes))){
                foreach (json_decode($product->attributes) as $key => $value) {
                    $flag = false;
                    $pos = 0;
                    foreach ($attributes as $key => $attribute) {
                        if($attribute['id'] == $value){
                            $flag = true;
                            $pos = $key;
                            break;
                        }
                    }
                    if(!$flag){
                        $item['id'] = $value;
                        $item['values'] = array();
                        foreach (json_decode($product->choice_options) as $key => $choice_option) {
                            if($choice_option->attribute_id == $value){
                                $item['values'] = $choice_option->values;
                                break;
                            }
                        }
                        array_push($attributes, $item);
                    }
                    else {
                        foreach (json_decode($product->choice_options) as $key => $choice_option) {
                            if($choice_option->attribute_id == $value){
                                foreach ($choice_option->values as $key => $value) {
                                    if(!in_array($value, $attributes[$pos]['values'])){
                                        array_push($attributes[$pos]['values'], $value);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


                // print_r($attributes);
                foreach($attributes as $k=>$v){
                    // sort($attributes[$k]['values']);
                    
                        $value = array();
                        $arrangevalue = array();
                        // dd($v['values']);
                        foreach($v['values'] as $attrk=>$attrv){
                            
                                $qty = explode(" ",$attrv);
                                if(isset($qty[1])){
                                    $value[$qty[0]] = $qty[1];  
                                }
                        }
        
                        ksort($value); 
                        $attributes[$k]['values'] = array();
                        $i = 0;
                        foreach($value as $arrk=>$arrv){
                            // array_push($arrangevalue ,$arrk." ".$arrv);
                            $attributes[$k]['values'][$i] = $arrk." ".strtolower($arrv);
                            $i++;
                        }
                        // dd($arrangevalue);
                       
                        // array_push($attributes[$k]['values'],$arrangevalue);
                    
                }

        $selected_attributes = array(); 
        $attr = array();
        $vr = array();
        foreach ($attributes as $key => $attribute) {
            array_push($attr, $attribute['id']);
            if($request->has('attribute_'.$attribute['id'])){
                foreach ($request['attribute_'.$attribute['id']] as $key => $value) {
                    $str = strtolower($value);
                    array_push($vr, $str);
                    //$products = $products->where('choice_options', 'like', '%'.$str.'%');
                    
                }
                

                $item['id'] = $attribute['id'];
                $item['values'] = $request['attribute_'.$attribute['id']];
                array_push($selected_attributes, $item);
            }
        }


        //Color Filter
        $all_colors = array();

        foreach ($non_paginate_products as $key => $product) {
            if ($product->colors != null) {
                foreach (json_decode($product->colors) as $key => $color) {
                    if(!in_array($color, $all_colors)){
                        array_push($all_colors, $color);
                    }
                }
            }
        }

        $selected_color = null;

        if($request->has('color')){
            $str = '"'.$request->color.'"';
            $products = $products->where('colors', 'like', '%'.$str.'%');
            $selected_color = $request->color;
        }
        // if(!empty(session()->get('pincode'))){
        //     $pincode = session()->get('pincode');
        //     $distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->pluck('id')->all();
        //     if(!empty($distributorId)){
        //         $productIds = \App\MappingProduct::whereIn('distributor_id',$distributorId)->where('published',1)->pluck('product_id')->all();
        //         $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
        //             $products->whereIn('id',$productIds); 
                
                
    
        //     }else{
        //         $products->whereIn('id',[]);

        //     }
        // }

        //  dd($products->toSql());

        $products = filter_products($products)->paginate(24)->appends(request()->query());

            $dataArray = $non_paginate_products;
            
            $test = array();
            $vkk=array();
         foreach ($attributes as $key => $attribute) {
            
            if($request->has('attribute_'.$attribute['id'])){
                
                foreach($dataArray as $key=>$row){
               
            if(!empty($row['choice_options'])){
              
                $decodeAttr = json_decode($row['choice_options']);
                
                foreach($decodeAttr as $k=>$r){
                    if(in_array($r->attribute_id,$attr)){
                       foreach($r->values as $vk=>$v){
                        if(in_array(strtolower($v),$vr)){
                           array_push($test, $row['id']);
                           
                        }
                        
                       } 
                    }
                    
                }
               

            }
        }
                    
                }
        }
        // echo '<pre>';
        // print_r($products);exit;
        if(count($test)>0){
            
            $products = filter_products(Product::whereIn('id',$test))->paginate(24)->appends(request()->query());;

}

        return view('frontend.product_listing', compact('products', 'query', 'category_id', 'subcategory_id', 'subsubcategory_id', 'brand_id', 'sort_by', 'seller_id','min_price', 'max_price', 'attributes', 'selected_attributes', 'all_colors', 'selected_color','dym'));
    }

    public function product_content(Request $request){
        $connector  = $request->connector;
        $selector   = $request->selector;
        $select     = $request->select;
        $type       = $request->type;
        productDescCache($connector,$selector,$select,$type);
    }

    public function home_settings(Request $request)
    {
        return view('home_settings.index');
    }

    public function top_10_settings(Request $request)
    {
        foreach (Category::all() as $key => $category) {
            if(is_array($request->top_categories) && in_array($category->id, $request->top_categories)){
                $category->top = 1;
                $category->save();
            }
            else{
                $category->top = 0;
                $category->save();
            }
        }

        foreach (Brand::all() as $key => $brand) {
            if(is_array($request->top_brands) && in_array($brand->id, $request->top_brands)){
                $brand->top = 1;
                $brand->save();
            }
            else{
                $brand->top = 0;
                $brand->save();
            }
        }

        flash(translate('Top 10 categories and brands have been updated successfully'))->success();
        return redirect()->route('home_settings.index');
    }

    public function variant_price(Request $request)
    {
        $shortId = "";
        $product = Product::find($request->id);
        $str = '';
        $quantity = 0;

        if(!empty(Cookie::get('pincode'))){ 
            $pincode = Cookie::get('pincode');
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        
        }

        if($request->has('color')){
            $data['color'] = $request['color'];
            $str = Color::where('code', $request['color'])->first()->name;
        }

        if(json_decode(Product::find($request->id)->choice_options) != null){
            foreach (json_decode(Product::find($request->id)->choice_options) as $key => $choice) {
                if($str != null){
                    $str .= '-'.str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                }
                else{
                    $str .= str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                }
            }
        }
        if($str != null && $product->variant_product){
            $product_stock = $product->stocks->where('variant', $str)->first();
            // $product_stock = ProductStock::where('variant', $str)->first();
                if(!empty($shortId)){
                    $mappedProductPrice = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$product->id])->first(); 
                    $quantity = $mappedProductPrice->qty;
                    if($mappedProductPrice['selling_price'] !=0){
                        $price = $mappedProductPrice['selling_price'];
                    }else{
                        $price = $product_stock->price;
                    }

                }else{
                    $price = $product_stock->price;
                    $quantity = $product_stock->qty;

                }
            // $quantity = $product_stock->qty;
            // $quantity = $mappedProductPrice->qty;
        }
        else{
            if(!empty($shortId)){
                $mappedProductPrice = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$product->id])->first();
                
                if($mappedProductPrice['purchased_price'] !=0){
                    $price = $mappedProductPrice['purchased_price'];
                }else{
                    $price = $product_stock->unit_price;
                } 
                $quantity = $mappedProductPrice->qty;
            }else{
                $price = $product->unit_price;
                $quantity = $product->current_stock;

            }
            
            
        }

        //discount calculation
        $flash_deals = \App\FlashDeal::where('status', 1)->get();
        $inFlashDeal = false;
        foreach ($flash_deals as $key => $flash_deal) {
            if ($flash_deal != null && $flash_deal->status == 1 && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && \App\FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first() != null) {
                $flash_deal_product = \App\FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first();
                if($flash_deal_product->discount_type == 'percent'){
                    $price -= ($price*$flash_deal_product->discount)/100;
                }
                elseif($flash_deal_product->discount_type == 'amount'){
                    $price -= $flash_deal_product->discount;
                }
                $inFlashDeal = true;
                break;
            }
        }

        // if (!$inFlashDeal) {
        //     if($product->discount_type == 'percent'){
        //         $price -= ($price*$product->discount)/100;
        //     }
        //     elseif($product->discount_type == 'amount'){
        //         $price -= $product->discount;
        //     }
        // }

        // if($product->tax_type == 'percent'){
        //     $price += ($price*$product->tax)/100;
        // }
        // elseif($product->tax_type == 'amount'){
        //     $price += $product->tax;
        // }


        if(Session::has('referal_discount')){
           // $referal_discount = ( $price * Session::get('referal_discount')) / 100;
           //  $price -= $referal_discount;
            $id = $request->id;
            if(!empty($shortId)){
                 $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
            }else{
                $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
            }  

            // $product = Product::findOrFail($id);
            // $productstock = ProductStock::where('product_id', $id)->select('price')->first();  
            if(!empty($shortId)){
                // DB::enableQueryLog();
                 $product = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
                // dd(DB::getQueryLog()); 
                // print_r($product); die;
                $price = $product['purchased_price'];
                $stock_price = $product['selling_price'];
                if($price == 0 || $stock_price == 0){
                    $product = Product::findOrFail($id);
                    $price = $product->unit_price;
                    $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                    $stock_price = $productstock->price;
                }  

            }else{
                $product = Product::findOrFail($id);
                $price = $product->unit_price;
                $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                $stock_price = $productstock->price;  

            }         

            if(!empty($peer_discount_check)){
                if(!empty($peer_discount_check->customer_off)){
                    $price = $stock_price - $peer_discount_check->customer_off;
                    // return $price;
                }else{
                    // $stock_price = $stock_price;  
                    $discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
                    $price = ($stock_price * $discount_percent)/100;
                    // return $price;
                }

            }else{
                $price = $stock_price;
                // return $price;
            }
            // $price = $productstock->price; 
             // $price = $stock_price; 
        }
        // echo $price;
        //info($price);

        return array('price' => single_price($price*$request->quantity), 'quantity' => $quantity, 'digital' => $product->digital);
    }

    public function sellerpolicy(){
        return view("frontend.policies.sellerpolicy");
    }

    public function returnpolicy(){
        return view("frontend.policies.returnpolicy");
    }

    public function supportpolicy(){
        return view("frontend.policies.supportpolicy");
    }

    public function terms(){
        return view("frontend.policies.terms");
    }

    public function privacypolicy(){
        return view("frontend.policies.privacypolicy");
    }

    public function contactUs(){
        return view("frontend.policies.contactus");
    }

    public function aboutUs(){
        return view("frontend.policies.aboutus");
    }



    public function get_pick_ip_points(Request $request)
    {
        $pick_up_points = PickupPoint::all();
        return view('frontend.partials.pick_up_points', compact('pick_up_points'));
    }

    public function get_category_items(Request $request){
        $category = Category::findOrFail($request->id);
        return view('frontend.partials.category_elements', compact('category'));
    }

    public function premium_package_index()
    {
        $customer_packages = CustomerPackage::all();
        return view('frontend.customer_packages_lists', compact('customer_packages'));
    }

    public function seller_digital_product_list(Request $request)
    {
        $products = Product::where('user_id', Auth::user()->id)->where('digital', 1)->orderBy('created_at', 'desc')->paginate(10);
        return view('frontend.seller.digitalproducts.products', compact('products'));
    }
    public function show_digital_product_upload_form(Request $request)
    {
        if(\App\Addon::where('unique_identifier', 'seller_subscription')->first() != null && \App\Addon::where('unique_identifier', 'seller_subscription')->first()->activated){
            if(Auth::user()->seller->remaining_digital_uploads > 0){
                $business_settings = BusinessSetting::where('type', 'digital_product_upload')->first();
                $categories = Category::where('digital', 1)->get();
                return view('frontend.seller.digitalproducts.product_upload', compact('categories'));
            }
            else {
                flash(translate('Upload limit has been reached. Please upgrade your package.'))->warning();
                return back();
            }
        }

        $business_settings = BusinessSetting::where('type', 'digital_product_upload')->first();
        $categories = Category::where('digital', 1)->get();
        return view('frontend.seller.digitalproducts.product_upload', compact('categories'));
    }

    public function show_digital_product_edit_form(Request $request, $id)
    {
        $categories = Category::where('digital', 1)->get();
        $product = Product::find(decrypt($id));
        return view('frontend.seller.digitalproducts.product_edit', compact('categories', 'product'));
    }


    // Ajax call
    public function new_verify(Request $request)
    {
        $email = $request->email;
        if(isUnique($email) == '0') {
            $response['status'] = 2;
            $response['message'] = 'Email already exists!';
            return json_encode($response);
        }

        $response = $this->send_email_change_verification_mail($request, $email);
        return json_encode($response);
    }


    // Form request
    public function update_email(Request $request)
    {
        $email = $request->email;
        if(isUnique($email)) {
            $this->send_email_change_verification_mail($request, $email);
            flash(translate('A verification mail has been sent to the mail you provided us with.'))->success();
            return back();
        }

        flash(translate('Email already exists!'))->warning();
        return back();
    }

    public function send_email_change_verification_mail($request, $email)
    {
        $response['status'] = 0;
        $response['message'] = 'Unknown';

        $verification_code = Str::random(32);

        $array['subject'] = 'Email Verification';
        $array['from'] = env('MAIL_USERNAME');
        $array['content'] = 'Verify your account';
        $array['link'] = route('email_change.callback').'?new_email_verificiation_code='.$verification_code.'&email='.$email;
        $array['sender'] = Auth::user()->name;
        $array['details'] = "Email Second";

        $user = Auth::user();
        $user->new_email_verificiation_code = $verification_code;
        $user->save();

        try {
            Mail::to($email)->queue(new SecondEmailVerifyMailManager($array));

            $response['status'] = 1;
            $response['message'] = translate("Your verification mail has been Sent to your email.");

        } catch (\Exception $e) {
            // return $e->getMessage();
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function email_change_callback(Request $request){
        if($request->has('new_email_verificiation_code') && $request->has('email')) {
            $verification_code_of_url_param =  $request->input('new_email_verificiation_code');
            $user = User::where('new_email_verificiation_code', $verification_code_of_url_param)->first();

            if($user != null) {

                $user->email = $request->input('email');
                $user->new_email_verificiation_code = null;
                $user->save();

                auth()->login($user, true);

                flash(translate('Email Changed successfully'))->success();
                return redirect()->route('dashboard');
            }
        }

        flash(translate('Email was not verified. Please resend your mail!'))->error();
        return redirect()->route('dashboard');

    }

    public function apply_partner_coupon_code(Request $request)
    {
        $coupon = PeerPartner::where(['code' => $request->code, 'verification_status' => 1, 'peertype_approval' => 0])->first();

        if ($coupon != null) {

            // $shortId = "";
            // if(!empty(Cookie::get('pincode')))
            // { 
                 $request->session()->put('partner_id', $coupon->user_id);
                 $request->session()->put('referal_discount', $coupon->discount);
                 $request->session()->put('referal_code', $coupon->code);

                 //28-09-2021
                 if($request->used_referral_code == 1 && $request->code == 'ROZANA7'){
                    $request->session()->put('used_referral_code', 1);
                 }else{
                    $request->session()->put('used_referral_code', 0);
                 }

                 //13-10-2021

                 if (Auth::user() != null){
                    $ids = Auth::user()->id;
                    DB::table('users')
                    ->where('id', $ids)
                    ->update(['used_referral_code' => $coupon->code]);

                    $code_array = array();
                    $code_array['partner_id'] = $coupon->user_id;
                    $code_array['referal_discount'] = $coupon->discount;
                    $code_array['referal_code'] = $coupon->code;

                    $code = implode(',',$code_array);

                    setcookie('last_used_code',$code,time()+60*60*24*30,'/');
                }else{

                    $code_array = array();
                    $code_array['partner_id'] = $coupon->user_id;
                    $code_array['referal_discount'] = $coupon->discount;
                    $code_array['referal_code'] = $coupon->code;

                    $code = implode(',',$code_array);

                    setcookie('last_used_code',$code,time()+60*60*24*30,'/');

                } 

                 flash(translate('Referral code has been applied'))->success();
                 return back();
            // }else{
            //     flash(translate('Please select your location to avail referral offers'))->warning();
            //     return back();
            // }     
          }else{
             flash(translate('Invalid coupon!'))->warning();
             return back();
          }
    }

    public function remove_partner_coupon_code(Request $request)
    {
        $request->session()->forget('partner_id');
        $request->session()->forget('referal_discount');
        $request->session()->forget('referal_code');

        //28-09-2021
        $request->session()->put('used_referral_code', 0);

        //13-10-2021
        if(Cookie::has('last_used_code')){
            Cookie::queue(Cookie::forget('last_used_code'));
        }
        
        flash(translate('Referral code has been removed'))->success();
        return back();
    }


    public function area_searching(Request $request){
        
    }


    public function setLocation(Request $request){
        //print_r(session()->all());
        // setcookie('pincode',$request->postal,time()+3600);
        // setcookie('state',$request->state,time()+3600);
        // setcookie('city',$request->city,time()+3600);
        // setcookie('country',$request->country,time()+3600);
        // $state_id = State::where('name',$request->state)->first()->id;
        // $city_id = City::where('name',$request->city)->first()->id;
        // setcookie('state_id',$state_id,time()+3600);
        // setcookie('city_id',$city_id,time()+3600);
        //dd($request->all());
        // {!! Session::get('pincode') !!},{!! Session::get('state') !!} 
        return back();
    }

    public function store_search($query){
        $data['search'] = $query;
        $data['customer_ip'] = $this->get_client_ip();
        if(Auth::check())
        {
            $data['customer_id'] = Auth::user()->id;
        }
        return DB::table('search_history')->insert($data);
    }

    function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    //12-10-2021
    public function apply_partner_coupon_code_without_login(Request $request){
            $coupon = PeerPartner::where(['code' => $request->code, 'verification_status' => 1, 'peertype_approval' => 0])->first();

        if ($coupon != null) {

                 $request->session()->put('partner_id', $coupon->user_id);
                 $request->session()->put('referal_discount', $coupon->discount);
                 $request->session()->put('referal_code', $coupon->code);

                 if($request->used_referral_code == 1 && $request->code == 'ROZANA7'){
                    $request->session()->put('used_referral_code', 1);
                 }else{
                    $request->session()->put('used_referral_code', 0);
                 }


                 flash(translate('Referral code has been applied'))->success();
                 return back();
    
          }else{
             flash(translate('Invalid coupon!'))->warning();
             return back();
          }
    }

    public function check_pinall(Request $request)
    {
        $pin_code = $request->pin_code;
        $zone = Area::where('pincode', $pin_code)->first('zone');       
        
        if($zone != null){
            return $zone->zone;
        }else{
            return 0;
        }
    }

    public function shopNow(){
        return view('frontend.shop_now');
    }
}
