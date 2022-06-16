<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Distributor;
use App\Cluster;
use App\ShortingHub;
use Auth;
use DB;
use App\PeerSetting;

class DistributorController extends Controller
{
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->user_type == 'admin'){
            $distributors = Distributor::get();
        }
        elseif(auth()->user()->staff->role->name == "Cluster Hub"){
            $distributors = Distributor::where('cluster_hub_id', auth()->user()->id)->get();
        }
        elseif(auth()->user()->staff->role->name == "Sorting Hub"){
            $distributors = Distributor::where('sorting_hub_id', auth()->user()->id)->get();
        }
        return view('distributors.index', compact('distributors'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('distributors.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Distributor::where('phone', $request->phone)->first() == null){
             $distributor = new Distributor;
             $distributor->cluster_hub_id = $request->cluster_hub;
             $distributor->sorting_hub_id = $request->sorting_hub_id;
             $distributor->name = $request->name;
             $distributor->phone = $request->phone;
             $distributor->pincode = json_encode(explode(',',$request->pincodes));
             //$distributor->pincode = json_encode($request->pincode);
             $distributor->address = $request->address;
             $data = array();
             if($request->hasFile('adhar_card')){
                $distributor->adhar_card = $request->adhar_card->store('uploads/documents');
             } 
             if($request->hasFile('pan_card')){
                $distributor->pan_card = $request->pan_card->store('uploads/documents');
             }  
             if($distributor->save()){
                    flash(translate('Distributor has been inserted successfully'))->success();
                    return redirect()->route('distributor.index');
                }else{
                    flash(translate('Somthing went wrong'))->error();
                    return redirect()->back();
                }
            }

        flash(translate('Phone already used'))->error();
        return back();
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
    public function edit($id)
    {
        $distributor = Distributor::find($id);
        return view('distributors.edit', compact('distributor'));
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
         $distributor = Distributor::find($id);

         $distributor->cluster_hub_id = $request->cluster_hub;
         $distributor->sorting_hub_id = $request->sorting_hub_id;
         $distributor->name = $request->name;
         $distributor->phone = $request->phone;
         $distributor->pincode = json_encode(explode(',',$request->pincodes));
         //$distributor->pincode = json_encode($request->pincode);
         $distributor->address = $request->address;
          if($request->hasFile('adhar_card')){
                $distributor->adhar_card = $request->adhar_card->store('uploads/documents');
             } 
             if($request->hasFile('pan_card')){
                $distributor->pan_card = $request->pan_card->store('uploads/documents');
             }  
         if($distributor->save()){
                flash(translate('Distributor has been update successfully'))->success();
                return redirect()->route('distributor.index');
            }else{
                flash(translate('Somthing went wrong'))->error();
                return redirect()->back();
            }

        flash(translate('Phone already used'))->error();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $distributor = Distributor::find($id);
        if(!empty($distributor)){
            if($distributor->delete()){
                 flash(translate('Distributor has been delete successfully'))->success();
                 return redirect()->route('distributor.index');
            }else{
                  flash(translate('Somthing went wrong'))->error();
                  return redirect()->back();
            }
        }
    }

    public function changeDistributorStatus(REQUEST $request){

        $changeStatus = Distributor::findOrFail($request->id);
        $changeStatus->status = $request->status;
        if($changeStatus->save()){
            return 1;

        }else{
            return 0;
        }
        

    }

    public function clone_distributor(){
        $distributors = Distributor::where('sorting_hub_id','!=',Auth::user()->id)->get();
        return view('distributors.clone',compact('distributors'));
    }


    public function create_clone_distributor(Request $request){

        $distributorId = $request->distributor;
        $distributor = Distributor::findOrFail($distributorId);

        $productId = [];
            try{
                DB::beginTransaction();
                    $dClone = new Distributor;
                    $dClone->cluster_hub_id = $request->cluster_hub;
                    $dClone->sorting_hub_id = $request->sorting_hub_id;
                    $dClone->name = $distributor->name;
                    $dClone->phone = $distributor->phone;
                    $dClone->address = $distributor->address;
                    $dClone->pincode = json_encode(array());
                    $dClone->adhar_card = $distributor->adhar_card;
                    $dClone->pan_card = $distributor->pan_card;

                    if($dClone->save()){
                        $products = \App\MappingProduct::whereRaw('json_contains(distributors, \'['.$distributorId.']\')')->get();

                        $sortingHubId = \App\Distributor::where('id',$distributorId)->first()->sorting_hub_id;

                        foreach ($products as $key => $product) {
                            //sorting hub id and product id is exits
                            $checkProduct = \App\MappingProduct::where('sorting_hub_id',$request->sorting_hub_id)->where('product_id',$product->product_id)->first();
                            $distributorIds = [];
                            if(!is_null($checkProduct)){
                                if(!is_null($checkProduct->distributors)){
                                    
                                    $distributorIds = json_decode($checkProduct->distributors,true);
                                }else{
                                    
                                    $distributorIds[] = $checkProduct->distributor_id;
                                
                                }
                                
                                $distributorIds[] = (int) $dClone->id;
                                // update distributor
                                \App\MappingProduct::where(['sorting_hub_id'=>$request->sorting_hub_id,'product_id'=>$product->product_id])->update(['distributors'=>json_encode($distributorIds)]);

                            }
                            else{
                                // create new entry
                                
                                $pClone = new \App\MappingProduct;
                                $pClone->sorting_hub_id = $request->sorting_hub_id;
                                $pClone->distributor_id = $dClone->id;
                                $pClone->distributors = json_encode([$dClone->id]);
                                $pClone->product_id = $product->product_id;
                                $pClone->published = $product->published;
                                $pClone->qty = $product->qty;
                                $pClone->purchased_price = $product->purchased_price;
                                $pClone->selling_price = $product->selling_price;
                                $pClone->flash_deal = $product->flash_deal;
                                $pClone->save();

                            }

                            $productId[] = '"'.$product->product_id.'"';
                                        
                        }

                        //Peer Setting entry for discount

                        $sort = '["'.$sortingHubId.'"]';

                        foreach($productId as $key => $product_id){

                            $peerSettings = \App\PeerSetting::where(['sorting_hub_id'=>$sort, 'product_id' => $product_id])->latest()->first();

                            if($peerSettings != null){
                                $peerClone = new \App\PeerSetting;
                                $peerClone->sorting_hub_id = '["'.$request->sorting_hub_id.'"]';
                                $peerClone->category_id = $peerSettings['category_id'];
                                $peerClone->sub_category_id = $peerSettings['sub_category_id'];
                                $peerClone->product_id = $peerSettings['product_id'];
                                $peerClone->discount = $peerSettings['discount'];
                                $peerClone->peer_discount = $peerSettings['peer_discount'];
                                $peerClone->customer_discount = $peerSettings['customer_discount'];
                                $peerClone->company_margin = $peerSettings['company_margin'];
                                $peerClone->customer_off = $peerSettings['customer_off'];
                                $peerClone->peer_commission = $peerSettings['peer_commission'];
                                $peerClone->master_commission = $peerSettings['master_commission'];
                                $peerClone->rozana_margin = $peerSettings['rozana_margin'];
                                $peerClone->margin = $peerSettings['margin'];
                                $peerClone->status = $peerSettings['status'];
                                
                                $peerClone->save();
                            }
                        }

                    }

                    DB::commit();

                flash(translate('Distributor has been cloned successfully'))->success();
                return redirect()->route('distributor.index');

            }catch(\Exception $e){
                info($e);
                DB::rollback();
                flash(translate('Somthing went wrong'))->error();
                return redirect()->back();
            }
      
    }

    public function getDistributorsOrderProducts(Request $request){
        
        $sorting_hub_id = auth()->user()->id;
        $product_id = $request->product_id;
        $product = \App\MappingProduct::where(['product_id'=>$product_id,'sorting_hub_id'=>$sorting_hub_id])->first('distributors');
        $distributorIds = [];
        if(!empty($product)){
            $distributorIds = json_decode($product->distributors);
        }
        
        $distributors = \App\Distributor::whereIn('id',$distributorIds)->get();
        return view('orders.load_distributors',compact('distributors'));

    }
    
}
