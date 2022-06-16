<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDF;
use App\Product;
use App\ProductStock;
use App\Distributor;
use App\MappingProduct;
use App\Category;
use Auth;
use DB;
use App\PeerSetting;
use Illuminate\Support\Facades\Cache;

class ProductMapController extends Controller
{

	  public function index(){
	  }

    public function create(Request $request){

        if(Auth::user()->user_type == 'admin'){
            $distributors = Distributor::get();
        }
        elseif(auth()->user()->staff->role->name == "Cluster Hub"){
            $distributors = Distributor::where('cluster_hub_id', auth()->user()->id)->get();
        }
        elseif(auth()->user()->staff->role->name == "Sorting Hub"){
            $distributors = Distributor::where('sorting_hub_id', auth()->user()->id)->get();
        }

        //$mappingids = MappingProduct::where('sorting_hub_id', auth()->user()->id)->pluck('product_id')->toArray();

        $products = Product::select('products.id', 'products.name')->get();

        return view('product_map.create_mapping', compact('distributors', 'products'));
    }

    // public function store(Request $request){

    //     try {
    //         foreach($request->products as $key => $product) {
    //             $checkProduct = MappingProduct::where('sorting_hub_id',$request->sorting_hub_id)->where('product_id',$product)->first();
               
    //             $distributorIds = [];
    //             if(!is_null($checkProduct)){
    //                 if(!is_null($checkProduct->distributors)){
                        
    //                     $distributorIds = json_decode($checkProduct->distributors,true);
    //                 }else{
                        
    //                     $distributorIds[] = $checkProduct->distributor_id;
                       
    //                 }
                    
    //                 $distributorIds[] = (int)$request->distributor_id;
    //                 // update distributor
    //                 MappingProduct::where(['sorting_hub_id'=>$request->sorting_hub_id,'product_id'=>$product])->update(['distributors'=>$distributorIds]);

    //             } else{
    //                 // create new entry
    //                 $mapping_product = new MappingProduct;
    //                 $mapping_product->sorting_hub_id = $request->sorting_hub_id;
    //                 $mapping_product->product_id = $product;
    //                 $mapping_product->distributor_id = $request->distributor_id;
    //                 $mapping_product->distributors = json_encode($request->distributor_id);
    //                 if($mapping_product->save()){
    //                     continue;
    //                 }else{
    //                     break;
    //                 }
    //             }  
                
    //         }
            
    //         flash(translate('Product Mapping added successfully'))->success();
    //         return redirect()->route('mapped.product.list');
    //     } catch (Exception $e) {
    //         flash(translate('Something went wrong'))->error();
    //         return redirect()->back();
    //     }
    // }

    public function store(Request $request){
        try {
            foreach($request->products as $key => $product) {   
                $mapping_product = new MappingProduct;
                $mapping_product->sorting_hub_id = $request->sorting_hub_id;
                $mapping_product->distributors = json_encode($request->distributor_id,JSON_NUMERIC_CHECK);
                $mapping_product->product_id = $product;
                $mapping_product->category_id = $request->category_id;
                if($mapping_product->save()){   
                    continue;
                }else{
                    break;
                }
            }
            flash(translate('Product Mapping added successfully'))->success();
            return redirect()->route('mapped.product.list');
        } catch (Exception $e) {
            flash(translate('Something went wrong'))->error();
            return redirect()->back();
        }
    }


    public function mapping_list(Request $request){
        $sort_search = $request->search;
        $distributors = [];
        $mapped_product =  DB::table('mapping_product'); 

        if(Auth::user()->user_type == 'admin'){
            $mapped_product = $mapped_product;
            $count_product =  DB::table('mapping_product')->get();
            $count_published =  DB::table('mapping_product')->where('published', 1)->get();
            $count_unpublished =  DB::table('mapping_product')->where('published', 0)->get();  
        }
        elseif(auth()->user()->staff->role->name == "Cluster Hub"){
            $mapped_product = Distributor::where('cluster_hub_id', auth()->user()->id);
            $count_product = Distributor::where('cluster_hub_id', auth()->user()->id)->get();
            $count_published = 0;
            $count_unpublished = 0;
        }
        elseif(auth()->user()->staff->role->name == "Sorting Hub"){
            $sorting_hub_id = auth()->user()->id;
            $distributors = Distributor::where('sorting_hub_id', $sorting_hub_id)->get();
            //$distributorids = Distributor::where('sorting_hub_id', $sorting_hub_id)->pluck('id')->toArray();
             //$mapped_product = MappingProduct::with('productstock','distributor','product')->whereIn('distributor_id', $distributorids);
             $mapped_product = MappingProduct::with('productstock','distributor','product')->where('sorting_hub_id', $sorting_hub_id);
             $count_product = MappingProduct::with('productstock','distributor','product')->where('sorting_hub_id', $sorting_hub_id)->get();
             $count_published = MappingProduct::with('productstock','distributor','product')->where('sorting_hub_id', $sorting_hub_id)->where('published', 1)->get();
              $count_unpublished = MappingProduct::with('productstock','distributor','product')->where('sorting_hub_id', $sorting_hub_id)->where('published', 0)->get();
             // echo '<pre>';
             // print_r($mapped_product); die;
             if ($request->has('search')){
                $sort_search = $request->search;
                // dd($sort_search);
                // DB::enableQueryLog();
                $mapped_product = $mapped_product->join('products', 'mapping_product.product_id', '=', 'products.id')->join('product_stocks', 'mapping_product.product_id', '=', 'product_stocks.product_id')->select('products.*','mapping_product.*','products.id as pid')->where('products.name', 'like', '%'.$sort_search.'%')->orWhere('product_stocks.sku', 'like', '%'.$sort_search.'%')->where('sorting_hub_id', $sorting_hub_id)->groupBy('products.id');
                // dd(DB::getQueryLog());
                
             } 
        }
        if ($request->has('search')){
            $mapped_product = $mapped_product->paginate(100)->appends(request()->query());
        }else{
            $mapped_product = $mapped_product->paginate(50);
        }    
       // $mapped_product = $mapped_product->paginate(50);     
      return view('product_map.mapping_list', compact('mapped_product','count_product','count_published','count_unpublished','distributors','sort_search'));
    }

    public function mapping_edit(Request $request){
      $distributors = Distributor::whereRaw('json_contains(sorting_hub_id, \'["' . auth()->user()->id . '"]\')')->get();
      $map_product = MappingProduct::with('product')->find(base64_decode($request->id));
      return view('product_map.edit_mapping', compact('map_product', 'distributors'));
    }

    public function mapping_update(Request $request){

          try {
                $mapping_product = MappingProduct::where(['id' => $request->mapping_id, 'sorting_hub_id' => auth()->user()->id])->first();
                $mapping_product->distributor_id = json_encode($request->distributor_id);
                $mapping_product->save();
            flash(translate('Product Mapping update successfully'))->success();
            return redirect()->route('mapped.product.list');
        } catch (Exception $e) {
            flash(translate('Something went wrong'))->error();
            return redirect()->back();
        }
    }

    public function get_product_by_category(Request $request){

      if(auth()->user()->user_type == "staff" && auth()->user()->staff->role->name == "Sorting Hub"){
        //$added_products = MappingProduct::where('distributor_id', $request->distributor_id)->pluck('product_id')->toArray();
        $added_products = MappingProduct::where('sorting_hub_id', auth()->user()->id)->pluck('product_id')->toArray();
      }else{
        $added_products = array();
      }
      
      $products = array();
      if(!empty($request->subcategory_id)){
        // $products = Product::whereIn('subcategory_id', $request->subcategory_id)->whereNotIn('id', $added_products)->get();
            if(!empty($added_products)){
                $products = Product::whereIn('subcategory_id', $request->subcategory_id)->whereNotIn('id', $added_products)->get();

            }else{
                $products = Product::whereIn('subcategory_id', $request->subcategory_id)->get();
            }
        
      }
      
      return view('partials.product_base_mapping', compact('products'));
    }

    public function mapping_trash(Request $request, $id){
        $map_product = MappingProduct::where('id', $id)->first();
        if(!empty($map_product)){
            if($map_product->delete()){
                flash(translate('Mapped Product delete successfully'))->success();
                return redirect()->route('mapped.product.list');
            }else{
                flash(translate('Something went wrong'))->error();
                return redirect()->back();
            }
        }
    }


    public function multiple_mapping_trash(Request $request){

        if(MappingProduct::destroy($request->selectedID)){
            return 1;
            }else{
                return 0;
            }
        
    }

    //17 may 2021
    public function get_product_by_hub_category(Request $request){

      $id = $request->hub_id;
      $sortinghub_id = $request->hub_id;
      $added_products = MappingProduct::where('sorting_hub_id', $id)->pluck('product_id')->toArray();
      $products = Product::where('subcategory_id', $request->subcategory_id)->whereIn('id', $added_products)->get();
      
      return view('partials.product_peer_mapping', compact('products', 'sortinghub_id'));
    }

     public function get_product_by_hub_discount(Request $request){

          $id = $request->hub_id;
          $sortinghub_id = $request->hub_id;
          $peer_map = $request->peer_map;
          $customer_map = $request->customer_map;
          $company_map = $request->company_map;
          $added_products = MappingProduct::where('sorting_hub_id', $id)->pluck('product_id')->toArray();
          $products = Product::where('subcategory_id', $request->subcategory_id)->whereIn('id', $added_products)->get();
          
          return view('partials.product_discount_mapping', compact('products','peer_map','customer_map','company_map', 'sortinghub_id'));
    }


    public function changePublished(REQUEST $request){ 
        $product = MappingProduct::findOrFail($request->id);
        $product->published = $request->status;
        if($product->save()){
            return 1;

        }else{
            return 0;
        }
        

    }

    public function changeRecurring(REQUEST $request){ 
        $product = MappingProduct::findOrFail($request->id);
        $product->recurring_status = $request->status;
        if($product->save()){
            return 1;

        }else{
            return 0;
        }
        

    }

    public function changeproductsbyhub(REQUEST $request){ 
        $product = MappingProduct::findOrFail($request->id);
        $product->top_product = $request->status;
        if($product->save()){
            return 1;

        }else{
            return 0;
        }
        

    }

    public function changerecurproductsbyhub(REQUEST $request){ 
        $product = MappingProduct::findOrFail($request->id);
        $product->recurring_status = $request->status;
        if($product->save()){
            return 1;

        }else{
            return 0;
        }
        

    }


    public function updateStock(REQUEST $request){

        $product = MappingProduct::findOrFail($request->id);
        $product->qty = $request->stock;
        if($product->save()){
            return 1;

        }else{
            return 0;
        }
        

    }


    public function updatePurchasePrice(REQUEST $request){
        $product = MappingProduct::findOrFail($request->product_id);
        $id = $product->product_id;
        $product->purchased_price = $request->purchase_price;
        if($product->save()){
 
                $sortinghubid = Auth::user()->id;
                // DB::enableQueryLog();
                $mappedproducts = MappingProduct::where('sorting_hub_id', $sortinghubid)->where('product_id', $id)->first();
                // dd(DB::getQueryLog());

                $product_s = Product::where('id', $id)->first();

                $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $sortinghubid. '"]\')')->latest()->first(); 

                if(!empty($peer_discount_check)){
         
                    $unit_price = $mappedproducts['purchased_price'];
                    $variant_price = $mappedproducts['selling_price'];

                    $customer_discount = substr($peer_discount_check['customer_discount'], 1, -1);
                    $customercheck_price = ($variant_price*$customer_discount)/100;
                    $finalamount = $variant_price - $customercheck_price;
                    $unitprice_tax = ($unit_price*$product_s->tax)/100;
                    $variantprice_tax = ($finalamount*$product_s->tax)/100;

                    if($variantprice_tax > $unitprice_tax){
                        $taxdifference = $variantprice_tax - $unitprice_tax;
                    }else{
                        $taxdifference = 0;
                    }

                    if($finalamount > $unit_price){
                        $difference = $finalamount - $unit_price;
                        $after_tax =  $difference - $taxdifference;
                    }else{
                        $after_tax = 0;
                    }

                    if($after_tax!=0){
                        $peer_discount = substr($peer_discount_check['peer_discount'], 1, -1);
                        $company_margin = substr($peer_discount_check['company_margin'], 1, -1);

                         $peer_commission = ($after_tax*$peer_discount)/100;
                         $master_commission = ($after_tax*$company_margin)/100; 
                             $last_margin = $peer_discount + $company_margin;
                             $margin = 100 - $last_margin; 
                         $rozana_commission = ($after_tax*$margin)/100;   
                    }else{
                        $peer_commission = 0;
                        $master_commission = 0;
                        $rozana_commission = 0;
                    }

                    // echo json_encode($sortinghubid);
                    // echo '<br>';
                    // die;
                   
                        $mapping_product = new PeerSetting;
                        $mapping_product->sorting_hub_id = $peer_discount_check['sorting_hub_id'];
                        $mapping_product->category_id = $peer_discount_check['category_id'];
                        $mapping_product->sub_category_id = $peer_discount_check['sub_category_id'];
                        $mapping_product->product_id = $peer_discount_check['product_id'];
                        $mapping_product->discount = $peer_discount_check['discount'];
                        $mapping_product->peer_discount = $peer_discount_check['peer_discount'];
                        $mapping_product->customer_discount = $peer_discount_check['customer_discount'];
                        $mapping_product->company_margin = $peer_discount_check['company_margin'];

                        $mapping_product->customer_off = $customercheck_price;
                        $mapping_product->peer_commission = $peer_commission;
                        $mapping_product->master_commission = $master_commission;
                        $mapping_product->rozana_margin = $rozana_commission;
                        $mapping_product->save();
                }

            return 1;
        }else{
            return 0;
        }
        

    }

    public function updateSellingPrice(REQUEST $request){
    	
        $product = MappingProduct::findOrFail($request->product_id);
        $id = $product->product_id;
        $product->selling_price = $request->selling_price;
        if($product->save()){

            $sortinghubid = Auth::user()->id;
            $mappedproducts = MappingProduct::where('sorting_hub_id', $sortinghubid)->where('product_id', $id)->first();
            $product_s = Product::where('id', $id)->first();

                $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $sortinghubid. '"]\')')->latest()->first(); 

                if(!empty($peer_discount_check)){
         
                    $unit_price = $mappedproducts['purchased_price'];
                    $variant_price = $mappedproducts['selling_price'];

                    $customer_discount = substr($peer_discount_check['customer_discount'], 1, -1);
                    $customercheck_price = ($variant_price*$customer_discount)/100;
                    $finalamount = $variant_price - $customercheck_price;
                    $unitprice_tax = ($unit_price*$product_s->tax)/100;
                    $variantprice_tax = ($finalamount*$product_s->tax)/100;

                    if($variantprice_tax > $unitprice_tax){
                        $taxdifference = $variantprice_tax - $unitprice_tax;
                    }else{
                        $taxdifference = 0;
                    }

                    if($finalamount > $unit_price){
                        $difference = $finalamount - $unit_price;
                        $after_tax =  $difference - $taxdifference;
                    }else{
                        $after_tax = 0;
                    }

                    if($after_tax!=0){
                        $peer_discount = substr($peer_discount_check['peer_discount'], 1, -1);
                        $company_margin = substr($peer_discount_check['company_margin'], 1, -1);

                         $peer_commission = ($after_tax*$peer_discount)/100;
                         $master_commission = ($after_tax*$company_margin)/100; 
                             $last_margin = $peer_discount + $company_margin;
                             $margin = 100 - $last_margin; 
                         $rozana_commission = ($after_tax*$margin)/100;   
                    }else{
                        $peer_commission = 0;
                        $master_commission = 0;
                        $rozana_commission = 0;
                    }

                    // echo json_encode($sortinghubid);
                    // echo '<br>';
                    // die;
                   
                        $mapping_product = new PeerSetting;
                        $mapping_product->sorting_hub_id = $peer_discount_check['sorting_hub_id'];
                        $mapping_product->category_id = $peer_discount_check['category_id'];
                        $mapping_product->sub_category_id = $peer_discount_check['sub_category_id'];
                        $mapping_product->product_id = $peer_discount_check['product_id'];
                        $mapping_product->discount = $peer_discount_check['discount'];
                        $mapping_product->peer_discount = $peer_discount_check['peer_discount'];
                        $mapping_product->customer_discount = $peer_discount_check['customer_discount'];
                        $mapping_product->company_margin = $peer_discount_check['company_margin'];

                        $mapping_product->customer_off = $customercheck_price;
                        $mapping_product->peer_commission = $peer_commission;
                        $mapping_product->master_commission = $master_commission;
                        $mapping_product->rozana_margin = $rozana_commission;
                        $mapping_product->save();
                }

        	//$this->finalPrice(Auth::user()->id);
            return 1;

        }else{
            return 0;
        }
        

    }

    public function download_file_product_by_category(Request $request){
       

        $products = DB::table('products')
        ->join('mapping_product','mapping_product.product_id','=','products.id')
        ->join('product_stocks','product_stocks.product_id','=','products.id')
        ->where('mapping_product.sorting_hub_id',auth()->user()->id)
        ->where('products.category_id','=',$request->category)
        ->select('products.name','products.choice_options','mapping_product.sorting_hub_id','mapping_product.distributors','mapping_product.published','mapping_product.qty','mapping_product.purchased_price','mapping_product.selling_price','mapping_product.created_at', 'mapping_product.id', 'mapping_product.product_id','product_stocks.sku','products.tax')
        ->get();
        if($request->file=="excel"){
            return $this->mapping_product_excel_export($request,$products);
        }
        return $this->mapping_product_pdf_export($request,$products);
        
        
    }

    

    public function mapping_product_excel_export($request,$products)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Sr No');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'SKU');
        $sheet->setCellValue('D1', 'Distributor');
        $sheet->setCellValue('E1', 'Purchased Price');
        $sheet->setCellValue('F1','MRP');
        $sheet->setCellValue('G1','Added On');
        $sheet->setCellValue('H1','Published');
        $sheet->setCellValue('I1','Stock');
        $sheet->setCellValue('J1','Quantity');
        $sheet->setCellValue('K1', 'Mapping Id');
        $sheet->setCellValue('L1', 'Sorting Hub Id');
        $sheet->setCellValue('M1', 'Product Id');
        // $sheet->setCellValue('N1', 'Discount');
        $sheet->setCellValue('N1', 'Tax');
        $sheet->setCellValue('O1', 'Customer_Off_Percentage');
        $sheet->setCellValue('P1', 'Peer_Off_Percentage');
        $sheet->setCellValue('Q1', 'Master_Peer_Off_Percentage');
        $sheet->setCellValue('R1', 'Customer Off');
        $sheet->setCellValue('S1', 'Peer Margin');
        $sheet->setCellValue('T1', 'Master Peer Margin');
        $sheet->setCellValue('U1', 'Rozana Margin');
        $sheet->setCellValue('V1', 'peerdiscount_status');
        foreach($products as $key => $product)
        {
            // dd($product);
        // $distributor_name = Distributor::where('id',$product->distributor_id)->first()->name;
            // $distributor_name = Distributor::where('id',$product->distributor_id)->first();
             $ids = json_decode($product->distributors);
            $distributorName = Distributor::whereIn('id', $ids)->first();
            // dd(auth()->user()->id);
            $discount_percent = PeerSetting::whereRaw('JSON_CONTAINS(sorting_hub_id, \'["'.auth()->user()->id.'"]\')')->where('product_id','"'.$product->product_id.'"')->first('peer_discount');
            

            if($product->name != null){
                $name = $product->name;
            }else{
                $name = 'NA';
            }
            // DB::enableQueryLog();
             $peer_discount_check = PeerSetting::where('product_id', '"'.$product->product_id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $product->sorting_hub_id. '"]\')')->where('status', 1)->latest('id')->first();
             // dd(DB::getQueryLog()); 

             $customer_off_percent = substr($peer_discount_check['customer_discount'], 1, -1);
             $peer_off_percent = substr($peer_discount_check['peer_discount'], 1, -1);
             $master_off_percent = substr($peer_discount_check['company_margin'], 1, -1);
             
        $status = ($product->published==1) ? "Published":"Unpublished";
        $sheet->setCellValue('A'.($key+2), $key+1);
        // $sheet->setCellValue('B'.($key+2), $product->name);
        // $sheet->setCellValue('C'.($key+2), $distributor_name);
        $sheet->setCellValue('B'.($key+2), $name);
        $sheet->setCellValue('C'.($key+2), $product->sku);
        $sheet->setCellValue('D'.($key+2), $distributorName->name);
        $sheet->setCellValue('E'.($key+2), $product->purchased_price);
        $sheet->setCellValue('F'.($key+2), $product->selling_price);
        $sheet->setCellValue('G'.($key+2), $product->created_at);
        $sheet->setCellValue('H'.($key+2), $status);
        $sheet->setCellValue('I'.($key+2), $product->qty);
        $sheet->setCellValue('J'.($key+2),$this->getProductAttr($product));
        $sheet->setCellValue('K'.($key+2), $product->id);
        $sheet->setCellValue('L'.($key+2), $product->sorting_hub_id );
        $sheet->setCellValue('M'.($key+2), $product->product_id );  
        // $sheet->setCellValue('N'.($key+2), @$discount_percent['peer_discount']); 
        $sheet->setCellValue('N'.($key+2), $product->tax ); 
        $sheet->setCellValue('O'.($key+2), $customer_off_percent );
        $sheet->setCellValue('P'.($key+2), $peer_off_percent ); 
        $sheet->setCellValue('q'.($key+2), $master_off_percent );
        $sheet->setCellValue('R'.($key+2), $peer_discount_check['customer_off'] ); 
        $sheet->setCellValue('S'.($key+2), $peer_discount_check['peer_commission'] );
        $sheet->setCellValue('T'.($key+2), $peer_discount_check['master_commission'] ); 
        $sheet->setCellValue('U'.($key+2), $peer_discount_check['rozana_margin'] );
        $sheet->setCellValue('V'.($key+2), 0 );   

        
        }

        $category_name = Category::where('id',$request->category)->first()->name;
        $writer = new Xlsx($spreadsheet);
        
        $filename = auth()->user()->name."_".$category_name.".xlsx";
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ])->deleteFileAfterSend(true);

    }

    public function mapping_product_pdf_export($request,$products)
    {
        // ini_set('max_execution_time', 500);
        // ini_set("memory_limit", "512M");
        $category_name = Category::where('id',$request->category)->first()->name;
        
        $pdf = PDF::loadView('product_map.download_products_pdf',compact('products','category_name'));
        $filename = auth()->user()->name."_".$category_name.".pdf";
        $pdf->save(base_path().$filename);
        return $pdf->download($filename);
    }

    public function delete_multiple(Request $request)
    {
        //dd($request->check);
        $ids = explode(',',$request->check);
        if(empty($request->check))
        {
            flash("Record Not deleted")->error();
         return '0';
        }
        $delete = DB::table('mapping_product')->whereIn('id',$ids)->delete();
        if($delete)
        {
            flash("Record deleted successfully")->success();
            return '1';
        }
        flash("Record Not deleted")->error();
         return '0';
    }


    public function getProductAttr($product){

        if ($product->choice_options != null){
            foreach (json_decode($product->choice_options) as $key => $choice){
                    foreach ($choice->values as $key => $value){
                        return $value;
                    }   
            }
        }

    }


    public function finalPrice($sortId){
    	
    	$productIds = [];
        if(!empty($sortId)){
        	$productIds = MappingProduct::where(['sorting_hub_id'=>$sortId,'published'=>1])->pluck('product_id');
        	
        } 
       $products = Product::whereIn('id',$productIds)->get();

       foreach ($products as $key => $product) {
        	$price = $product->unit_price;
        	$productStock = ProductStock::where('product_id',$product->id)->first();
        	if(!is_null($productStock)){
        		$price = $productStock->price;
        	}

        	if(!empty($sortId)){
        		$mappedProduct = MappingProduct::where(['sorting_hub_id'=>$sortId,'published'=>1,'product_id'=>$product->id])->first();
        		if(!is_null($mappedProduct)){
        			if($mappedProduct->selling_price!=0){
        				$price = $mappedProduct->selling_price;
        			}
        			
        		}
        	}

        	//update Final Price
        	MappingProduct::where('id',$mappedProduct->id)->update(['final_price'=>$price]);
        } 

        return true;
    }

    public function updateDist(){
       try{
        DB::beginTransaction();
        $mapping_products = MappingProduct::all();
        foreach($mapping_products as $key => $product){
            MappingProduct::where('id',$product->id)->update(['distributors'=>[$product->distributor_id]]);
        }
        DB::commit();
        echo "Updated";

       } catch(\Exception $e){
           DB::rollback();
           dd($e);
       }
        
    }

    public function map_distributors(Request $request){
        $image = $request->image;
        $variant = $request->variant;
        $product_name = $request->name;
        $mapped_product = \App\MappingProduct::find($request->id);
        $mapped_distributors = [];
        if(!is_null($mapped_product)){
            if(!is_null($mapped_product->distributors)){
                $mapped_distributors = json_decode($mapped_product->distributors,true);
            }
           
        }

        $distributors = \App\Distributor::where('sorting_hub_id',auth()->user()->id)->orderBy('id','ASC')->get();

        return view('product_map.map_distributors',compact('mapped_distributors','distributors','image','product_name','variant'));
    }

    public function storeMappedDistributors(Request $request){
        $mapped_product = \App\MappingProduct::find($request->id);
        if(!is_null($mapped_product)){
            //update product distributors
            $mapped = \App\MappingProduct::where('id',$request->id)->update(['distributors'=>json_encode($request->distributors,JSON_NUMERIC_CHECK)]);
            if($mapped){
                flash('Distributors mapped successfully')->success();
                return back();
            }
        }
        
        flash('Something went wrong!')->error();
        return back();
    }

    public function download_file_product_by_sku(Request $request){
       
       $sort_search = $request->searchs;
       // dd($sort_search);
        $products = DB::table('products')
        ->join('mapping_product','mapping_product.product_id','=','products.id')
        ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
        ->where('mapping_product.sorting_hub_id',auth()->user()->id)
        ->where('product_stocks.sku', 'like', '%'.$sort_search.'%')
        ->select('products.name','products.choice_options','mapping_product.sorting_hub_id','mapping_product.distributors','mapping_product.published','mapping_product.qty','mapping_product.purchased_price','mapping_product.selling_price','mapping_product.created_at', 'mapping_product.id', 'mapping_product.product_id', 'product_stocks.sku','products.tax')
        ->groupBy('products.id')->get();
        // dd($products);
        if($request->file=="excel"){
            return $this->mapping_productsku_excel_export($request,$products);
        }
        return $this->mapping_productsku_excel_export($request,$products);
        
        
    }
    public function mapping_productsku_excel_export($request,$products)
    {
        // dd('dd');
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Sr No');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'SKU');
        $sheet->setCellValue('D1', 'Distributor');
        $sheet->setCellValue('E1', 'Purchased Price');
        $sheet->setCellValue('F1','MRP');
        $sheet->setCellValue('G1','Added On');
        $sheet->setCellValue('H1','Published');
        $sheet->setCellValue('I1','Stock');
        $sheet->setCellValue('J1','Quantity');
        $sheet->setCellValue('K1', 'Mapping Id');
        $sheet->setCellValue('L1', 'Sorting Hub Id');
        $sheet->setCellValue('M1', 'Product Id');
        $sheet->setCellValue('N1', 'Tax');
        $sheet->setCellValue('O1', 'Customer_Off_Percentage');
        $sheet->setCellValue('P1', 'Peer_Off_Percentage');
        $sheet->setCellValue('Q1', 'Master_Peer_Off_Percentage');
        $sheet->setCellValue('R1', 'Customer Off');
        $sheet->setCellValue('S1', 'Peer Margin');
        $sheet->setCellValue('T1', 'Master Peer Margin');
        $sheet->setCellValue('U1', 'Rozana Margin');
        $sheet->setCellValue('V1', 'peerdiscount_status');
        foreach($products as $key => $product)
        {
            // dd($product);
        // $distributor_name = Distributor::where('id',$product->distributor_id)->first()->name;

            // dd($product->distributors);
            $ids = json_decode($product->distributors);
            $distributorName = Distributor::whereIn('id', $ids)->first();


            // $distributor_name = Distributor::where('id',$product->distributor_id)->first();
            // if($distributor_name['name'] != null){
            //    $distributorName = $distributor_name->name;
            // }else{
            //     $distributorName = 'NA';
            // }

            if($product->name != null){
                $name = $product->name;
            }else{
                $name = 'NA';
            }
            // DB::enableQueryLog();
             $peer_discount_check = PeerSetting::where('product_id', '"'.$product->product_id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $product->sorting_hub_id. '"]\')')->where('status', 1)->latest('id')->first();
             // dd(DB::getQueryLog()); 

             $customer_off_percent = substr($peer_discount_check['customer_discount'], 1, -1);
             $peer_off_percent = substr($peer_discount_check['peer_discount'], 1, -1);
             $master_off_percent = substr($peer_discount_check['company_margin'], 1, -1);
                    
        $status = ($product->published==1) ? "Published":"Unpublished";
        $sheet->setCellValue('A'.($key+2), $key+1);
        // $sheet->setCellValue('B'.($key+2), $product->name);
        // $sheet->setCellValue('C'.($key+2), $distributor_name);
         $sheet->setCellValue('B'.($key+2), $name);
        $sheet->setCellValue('C'.($key+2), $product->sku);
        $sheet->setCellValue('D'.($key+2), $distributorName->name);
        $sheet->setCellValue('E'.($key+2), $product->purchased_price);
        $sheet->setCellValue('F'.($key+2), $product->selling_price);
        $sheet->setCellValue('G'.($key+2), $product->created_at);
        $sheet->setCellValue('H'.($key+2), $status);
        $sheet->setCellValue('I'.($key+2), $product->qty);
        $sheet->setCellValue('J'.($key+2),$this->getProductAttr($product));
        $sheet->setCellValue('K'.($key+2), $product->id);
        $sheet->setCellValue('L'.($key+2), $product->sorting_hub_id );
        $sheet->setCellValue('M'.($key+2), $product->product_id );     
        $sheet->setCellValue('N'.($key+2), $product->tax ); 
        $sheet->setCellValue('O'.($key+2), $customer_off_percent );
        $sheet->setCellValue('P'.($key+2), $peer_off_percent ); 
        $sheet->setCellValue('q'.($key+2), $master_off_percent );
        $sheet->setCellValue('R'.($key+2), $peer_discount_check['customer_off'] ); 
        $sheet->setCellValue('S'.($key+2), $peer_discount_check['peer_commission'] );
        $sheet->setCellValue('T'.($key+2), $peer_discount_check['master_commission'] ); 
        $sheet->setCellValue('U'.($key+2), $peer_discount_check['rozana_margin'] );
        $sheet->setCellValue('V'.($key+2), 0 );   
        }

        $category_name = 'sku_product_report';
        $writer = new Xlsx($spreadsheet);
        
        $filename = auth()->user()->name."_".$category_name.".xlsx";
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ])->deleteFileAfterSend(true);

    }

    public function categories(Request $request){
        $sorting_hub_id = Auth::user()->id;
        $categories = \App\MappedCategory::where('sorting_hub_id',$sorting_hub_id)->orderBy('id','asc')->paginate(20);
        return view('product_map.mapping_categories', compact('categories'));
    } 

    public function updateCategoryStatus(Request $request){
        try{
            DB::beginTransaction();
            $sorting_hub_id = Auth::user()->id;
            $category = \App\MappedCategory::where('sorting_hub_id',$sorting_hub_id)
            ->where('category_id',$request->id)
            ->update(['status'=>$request->status]);
            if($category){
                if($request->status==0){
                    $published=0;
                }
                else{
                    $published = 1;
                }
                MappingProduct::where('sorting_hub_id',$sorting_hub_id)->where('category_id',$request->id)->update(['published'=>$published]);
                DB::commit();
                return 1;

            }else{
                return 0;
        }
        } catch(\Exception $e){
            DB::rollback();
            return 0;
        }
        
    }

    public function mapCategories(){
        $sorting_hub_id = Auth::user()->id;
        $mappedCategories = \App\MappedCategory::where('sorting_hub_id',$sorting_hub_id)->pluck('category_id');
        
        $categories = Category::where('status',1)->whereNotIn('id',$mappedCategories)->orderBy('created_at', 'desc')->get();
        return view('product_map.new_category_mapping',compact('categories'));
    }

    public function storeCategories(Request $request){
      if(!empty($request->category_ids)){
        if(Cache::has('featured_categories'.$request->sorting_hub_id)){
            Cache::forget('featured_categories'.$request->sorting_hub_id);
          }
        foreach($request->category_ids as $key => $category){
            $map_category = new \App\MappedCategory;
            $map_category->sorting_hub_id = $request->sorting_hub_id;
            $map_category->category_id = $category;
            $map_category->status = 1;
            $map_category->save();         
            }
            flash('Category mapped successfuly')->success();
            return redirect()->route('sorting_hub.mapping_categories');
      }else{
          flash('Select category')->error();
          return back();
      }
    }

}
