<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BannerSlider;

class BannerSliderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sliders = BannerSlider::all();
        return view('banner-sliders.index', compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {	
        return view('banner-sliders.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if($request->hasFile('photos')){
            foreach ($request->photos as $key => $photo) {
                $slider = new BannerSlider;
                $slider->link = $request->url;
                $slider->photo = $photo->store('uploads/banner_sliders');
                $slider->save();
            }
            flash(translate('Slider has been inserted successfully'))->success();
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
        $banner = BannerSlider::findOrFail($id);
        return view('banner-sliders.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update_status(Request $request)
    {  
          $banner = BannerSlider::find($request->id);
            $banner->published = $request->status;
            if($request->status == 1){
               
                    if($banner->save()){
                        return '1';
                    }
                    else {
                        return '0';
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

    public function update(Request $request, $id){

        $banner = BannerSlider::find($id);
        $banner->photo = $request->previous_photo;
        if($request->hasFile('photo')){
            $banner->photo = $request->photo->store('uploads/banner_sliders');
        }
        $banner->link = $request->url;
        $banner->save();
        flash(translate('Banner has been updated successfully'))->success();
        return redirect()->route('home_settings.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $slider = BannerSlider::findOrFail($id);
        if(BannerSlider::destroy($id)){
            //unlink($slider->photo);
            flash(translate('Slider has been deleted successfully'))->success();
        }
        else{
            flash(translate('Something went wrong'))->error();
        }
        return redirect()->route('home_settings.index');
    }
}
