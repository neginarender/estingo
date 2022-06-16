<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SubCategory;
use App\SubSubCategory;
use App\Category;
use App\Product;
use App\Language;
use Illuminate\Support\Str;
use App\State;
use DB;
use App\City;
use App\MappingProduct;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search =null;
        $subcategories = SubCategory::orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $subcategories = $subcategories->where('name', 'like', '%'.$sort_search.'%');
        }
        $subcategories = $subcategories->paginate(15);
        return view('subcategories.index', compact('subcategories', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        return view('subcategories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $subcategory = new SubCategory;
        $subcategory->name = $request->name;
        $subcategory->category_id = $request->category_id;
        $subcategory->meta_title = $request->meta_title;
        $subcategory->tags = implode('|',$request->tags);
        $subcategory->meta_description = $request->meta_description;
        if ($request->slug != null) {
            $subcategory->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        }
        else {
            $subcategory->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name)).'-'.Str::random(5);
        }

        $data = openJSONFile('en');
        $data[$subcategory->name] = $subcategory->name;
        //saveJSONFile('en', $data);

        if($subcategory->save()){
            flash(translate('Subcategory has been inserted successfully'))->success();
            return redirect()->route('subcategories.index');
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
        $subcategory = SubCategory::findOrFail(decrypt($id));
        $categories = Category::all();
        $tags = json_decode($subcategory->tags);
        return view('subcategories.edit', compact('categories', 'subcategory','tags'));
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
        $subcategory = SubCategory::findOrFail($id);

        foreach (Language::all() as $key => $language) {
            $data = openJSONFile($language->code);
            unset($data[$subcategory->name]);
            $data[$request->name] = "";
            //saveJSONFile($language->code, $data);
        }

        $subcategory->name = $request->name;
        $subcategory->category_id = $request->category_id;
        $subcategory->meta_title = $request->meta_title;
        $subcategory->tags = implode('|',$request->tags);
        $subcategory->meta_description = $request->meta_description;
        if ($request->slug != null) {
            $subcategory->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        }
        else {
            $subcategory->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name)).'-'.Str::random(5);
        }

        if($subcategory->save()){
            flash(translate('Subcategory has been updated successfully'))->success();
            return redirect()->route('subcategories.index');
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
        $subcategory = SubCategory::findOrFail($id);
        foreach ($subcategory->subsubcategories as $key => $subsubcategory) {
            $subsubcategory->delete();
        }
        Product::where('subcategory_id', $subcategory->id)->delete();
        if(SubCategory::destroy($id)){
            foreach (Language::all() as $key => $language) {
                $data = openJSONFile($language->code);
                unset($data[$subcategory->name]);
                //saveJSONFile($language->code, $data);
            }
            flash(translate('Subcategory has been deleted successfully'))->success();
            return redirect()->route('subcategories.index');
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }


    public function get_subcategories_by_category(Request $request)
    {
        $subcategories = SubCategory::where('category_id', $request->category_id)->get();
        return $subcategories;
    }

    //17may 2021
    public function get_categories_by_hub(Request $request)
    {
      $id = $request->hub_id;
      $added_products = MappingProduct::where('sorting_hub_id', $id)->pluck('product_id')->toArray();
      $products = Product::whereIn('id', $added_products)->select('category_id')->groupBy('category_id')->get();
      $category = array();
      foreach($products as $key => $row){
        
         array_push($category,Category::where('id', $row->category_id)->select('id','name')->first());
         
      }
        // echo '<pre>';
        // print_r($products);
        // die;
       return $category;
    }

    public function get_state_by_country_id(Request $request)
    {   

        $country = db::table('countries')->where('name' , $request->country_id)->first();

        $state = State::where('country_id', $country->id)->get();
      
        return $state;
    }

    public function get_city_by_state(Request $request)
    {  
        $state = State::where('name', $request->state)->first();

        $city = City::where('state_id', $state->id)->take('2000')->get();
      
        return $city;
    }

    public function citySearch(Request $request)
    {
        $search = $request->get('term');
        $state = $request->get('state');
        if(!empty($state)){    
          $state = State::where('status', 1)->where('name', $state)->first();
          $result = City::where('status', 1)->where('state_id', $state->id)->where('name', 'LIKE', '%'. $search. '%')->orderBy('name')->get();
          }else{  
          $result = City::where('status', 1)->where('name', 'LIKE', '%'. $search. '%')->orderBy('name')->get();
      }          
        return response()->json($result);
    }
}
