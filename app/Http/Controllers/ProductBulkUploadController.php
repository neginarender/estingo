<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Category;
use App\SubCategory;
use App\SubSubCategory;
use App\Brand;
use App\User;
use Auth;
use App\ProductsImport;
use App\ProductsExport;
use App\BrandExport;
use App\SubCategoryExport;
use App\SubSubCategoryExport;
use App\CategoryExport;
use PDF;
use Excel;
use Illuminate\Support\Str;
use DB;
use App\ProductsmapImport;
use App\OtpImport;
use App\LoginDetail;

class ProductBulkUploadController extends Controller
{
    public function index()
    {
        if (Auth::user()->user_type == 'seller') {
            return view('frontend.seller.product_bulk_upload.index');
        }
        elseif (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            return view('bulk_upload.index');
        }
    }

    public function export(){
        // ini_set('max_execution_time', -1);
        ini_set('memory_limit', '-1');
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function pdf_download_category()
    {
        // $categories = Category::all();
        // $pdf = PDF::setOptions([
        //                 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
        //                 'logOutputFile' => storage_path('logs/log.htm'),
        //                 'tempDir' => storage_path('logs/')
        //             ])->loadView('downloads.category', compact('categories'));

        // return $pdf->download('category.pdf');
        return Excel::download(new CategoryExport,'category.xlsx');
    }

    public function pdf_download_sub_category()
    {
        // $sub_categories = Subcategory::all();
        // $pdf = PDF::setOptions([
        //                 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
        //                 'logOutputFile' => storage_path('logs/log.htm'),
        //                 'tempDir' => storage_path('logs/')
        //             ])->loadView('downloads.sub_category', compact('sub_categories'));

        // return $pdf->download('sub_category.pdf');
        return Excel::download(new SubCategoryExport,'sub_category.xlsx');
    }

    public function pdf_download_sub_sub_category()
    {
        // $sub_sub_categories = SubSubCategory::all();
        // $pdf = PDF::setOptions([
        //                 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
        //                 'logOutputFile' => storage_path('logs/log.htm'),
        //                 'tempDir' => storage_path('logs/')
        //             ])->loadView('downloads.sub_sub_category', compact('sub_sub_categories'));

        // return $pdf->download('sub_sub_category.pdf');
        return Excel::download(new SubSubCategoryExport,'sub_sub_category.xlsx');
    }

    public function pdf_download_brand()
    {
        //$brands = Brand::all();
        return Excel::download(new BrandExport,'brands.xlsx');
        // $pdf = PDF::setOptions([
        //                 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
        //                 'logOutputFile' => storage_path('logs/log.htm'),
        //                 'tempDir' => storage_path('logs/')
        //             ])->loadView('downloads.brand', compact('brands'));
        // return $pdf->download('brands.pdf');
    }

    public function pdf_download_seller()
    {
        $users = User::where('user_type','seller')->get();
        $pdf = PDF::setOptions([
                        'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
                        'logOutputFile' => storage_path('logs/log.htm'),
                        'tempDir' => storage_path('logs/')
                    ])->loadView('downloads.user', compact('users'));

        return $pdf->download('user.pdf');

    }

    public function bulk_upload(Request $request)
    {
        // $product = new Product;
        // $product = $product->find($row['id']);
        // if ($product){
        //     echo "string"; die;
        // }else{
        //     echo "dg"; die;
        // }
        // echo 'sdsafsf'; die;
        // echo '<pre>';
        // print_r($row['name']);
        // die;
        // if($request->hasFile('bulk_file')){
        //    $upload = Excel::import(new ProductsImport, request()->file('bulk_file'));
        //    dd($upload);
        // }
        // flash(translate('Products exported successfully'))->success();
        // return back();

        $data = Excel::toArray(new ProductsImport, request()->file('bulk_file')); 

        $return_data = collect(head($data))
            ->each(function ($row, $key) {
                // dd($row);
             $check_product = Product::where('name', $row['name'])->whereNotIn('id', [$row['id']])->get();
             if(count($check_product)==0){ 
                $product = new Product;
                $product = $product->find($row['id']);
                        if ($product){
                            // echo "update"; die;
                             DB::table('products')
                            ->where('id', $row['id'])
                            ->update([
                             'tax'      => $row['tax'],
                             'tax_type' => $row['tax_type']
                          ]);
                        }else{
                            // echo "insert"; die;
                            $upload = Excel::import(new ProductsImport, request()->file('bulk_file'));
                        }
                }else{
                     flash(translate('Product name already exist'))->error();
                    return back();
                }        
                
               
            });
        flash(translate('Products exported successfully'))->success();
        return back();

    }
    //10dec 2021
    public function productmap_index()
    {
        return view('bulk_upload.bulkupload_index');
    }

    public function productmap_bulk_upload(Request $request)
    {
       // dd($request->all());
        // $data = Excel::toArray(new ProductsmapImport, request()->file('bulk_file'));  
       

       $upload = Excel::import(new ProductsmapImport, request()->file('bulk_file'));  
       $login_detail = new LoginDetail;
       $login_detail->mobile = $request->take_mobile;
       $login_detail->description = $request->take_description; 
       $login_detail->save();

       $otp = 0;
        OtpImport::where('mobile', $request->take_mobile)
            ->update([
             'otp'    => $otp,
             'status' => $otp
        ]);      
        flash(translate('Products exported successfully'))->success();
        return back();

    }

    public function set_productimport_otp(Request $request){
       $mobile = $request->mobile;
       $activenum = OtpImport::where('mobile', $mobile)->first();    
       if(!empty($activenum)){
            $otp = rand(10,10000);
            OtpImport::where('mobile', $mobile)
                ->update([
                 'otp'    => $otp,
                 'status' => 1
            ]);

            $to = $mobile;
            $from = "RZANA";
            $tid  = "1707164586764849522"; 

            $msg = "Dear user, your OTP for Rozana Admin Related Changes is ".$otp.". Team Rozana";
            mobilnxtSendSMS($to,$from,$msg,$tid);

            return 1;
       }else{
            return 0;
       }
    }

    public function get_productimport_otp(Request $request){
       $mobile = $request->mobile;
       $get_otp = $request->otp;

       $activeotp = OtpImport::where('mobile', $mobile)->where('otp', $get_otp)->where('status', 1)->first();   
       if(!empty($activeotp)){ 
            return 1;
       }else{
            return 0;
       }
    }

}
