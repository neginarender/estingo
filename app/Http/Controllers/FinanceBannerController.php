<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\FinanceBanner;

class FinanceBannerController extends Controller
{
       /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banners = FinanceBanner::all();
        return view('finance-banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('finance-banners.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->hasFile('photo')){
            $banner = new FinanceBanner;
            $banner->photo = $request->photo->store('uploads/banners');
            $banner->url = $request->url;
            $banner->position = 0;
            $banner->save();
            flash(translate('Banner has been inserted successfully'))->success();
        }
        return redirect()->route('home_settings.index');
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
        $banner = FinanceBanner::findOrFail($id);
        return view('finance-banners.edit', compact('banner'));
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
        $banner = FinanceBanner::find($id);
        $banner->photo = $request->previous_photo;
        if($request->hasFile('photo')){
            $banner->photo = $request->photo->store('uploads/banners');
        }
        $banner->url = $request->url;

        $banner->save();
        flash(translate('Banner has been updated successfully'))->success();
        return redirect()->route('home_settings.index');
    }


    public function update_status(Request $request)
    {
        $banner = FinanceBanner::find($request->id);
        $banner->published = $request->status;
        if($request->status == 1){
            if(count(FinanceBanner::where('published', 1)->where('position', $banner->position)->get()) < 4)
            {
                if($banner->save()){
                    return '1';
                }
                else {
                    return '0';
                }
            }
        }
        else{
            if($banner->save()){
                return '1';
            }
            else {
                return '0';
            }
        }

        return '0';
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $banner = FinanceBanner::findOrFail($id);
        if(FinanceBanner::destroy($id)){
            //unlink($banner->photo);
            flash(translate('Banner has been deleted successfully'))->success();
        }
        else{
            flash(translate('Something went wrong'))->error();
        }
        return redirect()->route('home_settings.index');
    }
}
