<?php

namespace App\Http\Controllers\Api\v5;
use Illuminate\Http\Request;

use App\Http\Resources\v5\BannerCollection;
// use App\Http\Resources\RecurringCollection;
use App\Http\Resources\v5\StateCollection;
use App\Http\Resources\v5\CityCollection;
use App\Http\Resources\v5\AreaCollection;
use App\Http\Resources\v5\BlockCollection;
use App\Http\Resources\v5\PincodeCollection;
use App\Models\Banner;
use App\MasterBanner;
use App\Http\Resources\v5\SliderCollection;
use App\Models\Slider;
use App\Http\Resources\v5\ProductCollection;
use App\Http\Resources\v5\ProductDetailCollection;
use App\Http\Resources\v5\SearchProductCollection;
use App\Http\Resources\v5\FlashDealCollection;
use App\Http\Resources\v5\HomepageCollection;
use App\Http\Resources\v5\OrderCollection;
use App\Http\Resources\v5\UserCollection;
use App\Traits\LocationTrait;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Color;
use DB;
use App\Attribute;
use App\PeerPartner;
use App\PeerSetting;
use App\ProductStock;
use App\Models\Cart;
use App\Http\Resources\v5\CategoryCollection;
use App\Models\BusinessSetting;
use App\Wishlist;
use App\State;
use App\City;
use App\Area;
use App\Block;
use App\Order;
use App\User;
use App\OrderReferalCommision;
use Carbon\Carbon;

class PeerController extends Controller
{
    public function customerDetail($id){
        $users = User::where('id',$id)->first();
        $detail = array([
            'id' => (integer) $users->id,
            'name' => ucwords($users->name),
            'type' => $users->user_type,
            'email' => $users->email,
            'avatar' => is_null($users->avatar)?substr($users->name, 0,1):$users->avatar,
            'avatar_original' => $users->avatar_original,
            'address' => empty($this->getAddress($users->id))?"":$this->getAddress($users->id)->address,
            'city' => empty($this->getAddress($users->id))?"":$this->getAddress($users->id)->city,
            'country' => empty($this->getAddress($users->id))?"":$this->getAddress($users->id)->country,
            'postal_code' => empty($this->getAddress($users->id))?"":$this->getAddress($users->id)->postal_code,
            'tag' => empty($this->getAddress($users->id))?"":ucfirst($this->getAddress($users->id)->tag),
            'phone' => $users->phone,
            'enrollment_date' => date('d/M/Y',strtotime($users->created_at)),
            'block' => empty($this->getBlock($users->block_id))?"":$this->getBlock($users->block_id)->block_name,
            'district' => empty($this->getBlock($users->block_id))?"":$this->getBlock($users->block_id)->city_name,
            'state' => empty($this->getBlock($users->block_id))?"":$this->getBlock($users->block_id)->state_name,
        ]);

        $order = DB::select("SELECT COUNT(*) as total_order, SUM(grand_total) as total_order_amount, SUM(referal_discount) as total_saving , 
            (SELECT count(*) FROM orders a where a.order_status = 'deliverd' and (a.user_id = $id or a.order_for  = $id)) as delivered_order,
            (SELECT count(*) FROM orders b where b.order_status = 'in_transit' and (b.user_id = $id or b.order_for  = $id)) as in_transit,
            (SELECT count(*) FROM orders c where c.order_status = 'cancel' and (c.user_id = $id or c.order_for  = $id)) as cancel
            from orders where user_id = $id or order_for = $id");

        $data = [];

        $data['total_order'] = $order[0]->total_order;
        $data['total_order_amount'] = is_null($order[0]->total_order_amount)?0:round($order[0]->total_order_amount,2);
        $data['total_saving'] = is_null($order[0]->total_saving)?0:round($order[0]->total_saving,2);
        $data['delivered_order'] = $order[0]->delivered_order;
        $data['in_transit'] = $order[0]->in_transit;
        $data['cancel'] = $order[0]->cancel;


        $list = order::where('user_id',$id)->orWhere('order_for',$id)
                ->leftjoin('order_referal_commision','orders.id','=','order_referal_commision.order_id')
                ->select('orders.*','order_referal_commision.referal_commision_discount')
                ->limit(20)
                ->get();
        $orderList = new OrderCollection($list);

        return response()->json([
            'success'=>true,
            'status' => 200,
            'customerDetail' => $detail,
            'orderDetail' => $data,
            'orderList' => $orderList,
        ]);
    }
    public function getAddress($user_id){
        $defaultAddress = \App\Address::where('user_id',$user_id)->where('set_default','1')->first();
        if(is_null($defaultAddress)){
            $address = \App\Address::where('user_id',$user_id)->first();
        }else{
            $address = $defaultAddress;
        }
        
        if(!is_null($address)){
            return $address;
        }else{

        }
        $address = "";
        return $address;
    }

    public function getBlock($id){

        $data = DB::table('blocks')
                ->leftjoin('cities','blocks.district_id','=','cities.id')
                ->leftjoin('states','cities.state_id','=','states.id')
                ->where('blocks.id','=',$id)  
                ->select('blocks.name as block_name','cities.name as city_name','states.name as state_name')
                ->first();
        return $data;

    }

    public function Peerlist(){


         $callcenterall = DB::table('peer_partners')->paginate(10);

                return $callcenterall;


    }
    
}   
