<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DeliverySlot;
use App\Category;
use DB;
use App\ShortingHub;
use Auth;

class DeliverySlotController extends Controller
{
    //

    public function index(){
        if(Auth::user()->user_type == "admin"){
        $delievrySlot = DeliverySlot::select('id','category_id','category_name','cut_off','delivery_time','delivery_shift','shorting_hub_id')->get();
        }else{
            $delievrySlot = DeliverySlot::where('shorting_hub_id',Auth::user()->sortinghub->id)->select('id','category_id','category_name','cut_off','delivery_time','delivery_shift','shorting_hub_id')->get();
        }
        return view('delivery_slot.index',compact('delievrySlot'));

    }

    public function deleteSlot($id){
        $slotid = decrypt($id);
        try{
            $delete = DeliverySlot::where('id',$slotid)->delete();
            if($delete){
                flash(translate('Slot has been delete successfully.'))->success();
                return back();
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }
        

    }


    public function createDeliverySlot(){
        $category = Category::where('status',1)->select('id','name')->get();
        $shorting_hub = ShortingHub::where('status',1)->get();
        return view('delivery_slot.create_slot',compact('category','shorting_hub'));

    }


    public function storeSlot(REQUEST $request){
       
        DB::beginTransaction();
        try{
            $shorting_hub_id = "";
            if(Auth::user()->user_type == "admin"){
                $shorting_hub_id = $request->shorting_hub;
            }else{
                $shorting_hub_id = Auth::user()->sortinghub->id;
            }
            $categoryName = explode('_',$request->category_name);
            // $delivery_time = date( 'G:i', strtotime($request->delivery_time_start)).'-'.date( 'G:i', strtotime($request->delivery_time_end));
            $delivery_time = date( 'H:i', strtotime($request->delivery_time_start)).'-'.date( 'H:i', strtotime($request->delivery_time_end));
            $deliverySlot = new DeliverySlot;
            $deliverySlot->category_name = $categoryName[0];
            $deliverySlot->type = $categoryName[1];
            $deliverySlot->category_id = implode(',',$request->category_detail);
            $deliverySlot->cut_off = date('G:i',strtotime($request->cut_off));
            $deliverySlot->delivery_shift = $request->delivery_shift;
            $deliverySlot->delivery_time = $delivery_time;
            $deliverySlot->shorting_hub_id = $shorting_hub_id;
            $deliverySlot->status = 1;
            $deliverySlot->added_by = 1;
            $deliverySlot->save();
            DB::commit();
            flash(translate('Slot has been created successfully.'))->success();
            return redirect()->route('deliveryslot.index');
            
        }catch(\Exception $e){
            DB::rollback();
            return $e->getMessage();
        }


    }

    public function editSlot($id){
        $slotid = decrypt($id);
        $slotDetail = DeliverySlot::where('id',$slotid)->first();
        $shorting_hub = ShortingHub::where('status',1)->get();
        $category = Category::where('status',1)->select('id','name')->get();
        return view('delivery_slot.edit_slot',compact('slotDetail','category','shorting_hub'));

    }


    public function updateSlot(REQUEST $request){
        DB::beginTransaction();
        try{
            $deliverySlot = DeliverySlot::where('id',$request->delivery_slot_id)->first();
            $delivery_time = date( 'H:i', strtotime($request->delivery_time_start)).'-'.date( 'H:i', strtotime($request->delivery_time_end));
            $deliverySlot->cut_off = date('G:i',strtotime($request->cut_off));
            $deliverySlot->delivery_shift = $request->delivery_shift;
            $deliverySlot->delivery_time = $delivery_time;
            $deliverySlot->save();
            DB::commit();
            flash(translate('Slot has been updated successfully.'))->success();
            return back();
        }catch(\Exception $e){
            DB::rollback();
            return $e->getMessage();
        }
        

    }
}
