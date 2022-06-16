<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Category;
use App\HomeCategory;
use App\Product;
use App\ProductStock;
use App\Language;
use Illuminate\Support\Str;
use PDF;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search =null;
        $categories = Category::orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $categories = $categories->where('name', 'like', '%'.$sort_search.'%');
        }
        $categories = $categories->paginate(15);
        return view('categories.index', compact('categories', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $category = new Category;
        $category->name = $request->name;
        $category->meta_title = $request->meta_title;
        $category->tags = implode('|',$request->tags);
        $category->meta_description = $request->meta_description;
        if ($request->slug != null) {
            $category->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        }
        else {
            $category->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name)).'-'.Str::random(5);
        }
        if ($request->commision_rate != null) {
            $category->commision_rate = $request->commision_rate;
        }

        $data = openJSONFile('en');
        $data[$category->name] = $category->name;
        //saveJSONFile('en', $data);

        if($request->hasFile('banner')){
            $category->banner = $request->file('banner')->store('uploads/categories/banner');
        }
        if($request->hasFile('icon')){
            $category->icon = $request->file('icon')->store('uploads/categories/icon');
        }

        $category->digital = $request->digital;
        if($category->save()){
            flash(translate('Category has been inserted successfully'))->success();
            return redirect()->route('categories.index');
        }
        else{
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
    public function edit($id)
    {
        $category = Category::findOrFail(decrypt($id));
        $tags = json_decode($category->tags);
        return view('categories.edit', compact('category','tags'));
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
        $category = Category::findOrFail($id);

        foreach (Language::all() as $key => $language) {
            $data = openJSONFile($language->code);
            unset($data[$category->name]);
            $data[$request->name] = "";
            //saveJSONFile($language->code, $data);
        }

        $category->name = $request->name;
        $category->meta_title = $request->meta_title;
        $category->tags = implode('|',$request->tags);
        $category->meta_description = $request->meta_description;
        if ($request->slug != null) {
            $category->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        }
        else {
            $category->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name)).'-'.Str::random(5);
        }

        if($request->hasFile('banner')){
            $category->banner = $request->file('banner')->store('uploads/categories/banner');
        }
        if($request->hasFile('icon')){
            $category->icon = $request->file('icon')->store('uploads/categories/icon');
        }
        if ($request->commision_rate != null) {
            $category->commision_rate = $request->commision_rate;
        }

        $category->digital = $request->digital;
        if($category->save()){
            flash(translate('Category has been updated successfully'))->success();
            return redirect()->route('categories.index');
        }
        else{
            flash(translate('Something went wrong'))->error();
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
        $category = Category::findOrFail($id);
        foreach ($category->subcategories as $key => $subcategory) {
            foreach ($subcategory->subsubcategories as $key => $subsubcategory) {
                $subsubcategory->delete();
            }
            $subcategory->delete();
        }

        Product::where('category_id', $category->id)->delete();
        HomeCategory::where('category_id', $category->id)->delete();

        if(Category::destroy($id)){
            foreach (Language::all() as $key => $language) {
                $data = openJSONFile($language->code);
                unset($data[$category->name]);
                //saveJSONFile($language->code, $data);
            }

            if($category->banner != null){
                //($category->banner);
            }
            if($category->icon != null){
                //unlink($category->icon);
            }
            flash(translate('Category has been deleted successfully'))->success();
            return redirect()->route('categories.index');
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function updateFeatured(Request $request)
    {
        $category = Category::findOrFail($request->id);
        $category->featured = $request->status;
        if($category->save()){
            return 1;
        }
        return 0;
    }

    public function updateStatus(Request $request)
    {
        $category = Category::findOrFail($request->id);
        $category->status = $request->status;
        if($category->save()){
            Product::where('category_id',$request->id)->update(array('search_status'=>$request->status));
            return 1;
        }
        return 0;
    }

    public function downloadProducts($id){
        $category_name = Category::select('name')->where(['id'=>decrypt($id)])->first();
        $products = Product::where(['category_id'=>decrypt($id)])->get();
        //$pdf = PDF::loadView('invoices/download_products', compact(['products','category_name']));
        
       // return $pdf->download('disney.pdf');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Sr No');
        $sheet->setCellValue('B1', 'Product Name');
        $sheet->setCellValue('C1', 'Quantity');
        $sheet->setCellValue('D1', 'Variant');

        foreach($products as $key => $product)
        {
        $sheet->setCellValue('A'.($key+2), $key+1);
        $sheet->setCellValue('B'.($key+2), $product->name);
        $sheet->setCellValue('C'.($key+2), $this->get_productstock_column($product->id,'qty'));
        $sheet->setCellValue('D'.($key+2), $this->get_productstock_column($product->id,'variant'));
        }
        $filename = $category_name->name.".xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);
        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
        
    }

    public function get_productstock_column($product_id,$column)
    {
        $stock = ProductStock::where('product_id',$product_id)->first();
        if($stock!=null)
        {
            return $stock->$column;
        }
        return "0";
    }
}
