<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Attribute;
//use CoreComponentRepository;
use App\Attributeval;
use DB;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //CoreComponentRepository::instantiateShopRepository();
        $attributes = Attribute::all();
        return view('attribute.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         return view('attribute.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $attribute = new Attribute;
        $attribute->name = $request->name;
        if($attribute->save()){
            flash(translate('Attribute has been inserted successfully'))->success();
            return redirect()->route('attributes.index');
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
        $attribute = Attribute::findOrFail(decrypt($id));
        return view('attribute.edit', compact('attribute'));
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
        $attribute = Attribute::findOrFail($id);
        $attribute->name = $request->name;
        if($attribute->save()){
            flash(translate('Attribute has been updated successfully'))->success();
            return redirect()->route('attributes.index');
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
        $attribute = Attribute::findOrFail($id);
        if(Attribute::destroy($id)){
            flash(translate('Attribute has been deleted successfully'))->success();
            return redirect()->route('attributes.index');
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }


    public function attributeadd($id)
    {
        $optionattributes = DB::table('attribute_options_values')
         ->where('attribute_id',$id)
         ->get();
        $attribute = Attribute::findOrFail($id);
        return view('attribute.attributeval', compact('attribute', 'optionattributes'));
    }

    public function storeoptionval(Request $request, $id)
    {
        $Attributeval = new Attributeval;
        $Attributeval->attribute_id = $id;
        $Attributeval->attribute_option_value = $request->name;
        if($Attributeval->save()){
            flash(translate('Attribute option value has been inserted successfully'))->success();
            // return redirect()->route('attributes.index');
            return redirect()->back();
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function editoptionattribute($id)
    {
        $attribute = DB::table('attribute_options_values')
         ->where('attribute_option_value_id',$id)
         ->get();
        return view('attribute.editoptionattribute', compact('attribute'));
    }

    public function updateoptionattribute(Request $request, $id)
    {
        // $attr_id = $request->$attr_id;

         $update =  DB::table('attribute_options_values')
                ->where('attribute_option_value_id', $id)
                ->update(['attribute_option_value' => $request->name]);
        if($update == 1){
            flash(translate('Attribute option value has been updated successfully'))->success();
            return redirect()->route('attributes.index');
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

     public function distroyoptionattribute($id)
    {
        $optionval = DB::table('attribute_options_values')->where('attribute_option_value_id', $id)->delete();

        if($optionval == 1){
            flash(translate('Attribute option value has been deleted successfully'))->success();
            return redirect()->back();
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

}
