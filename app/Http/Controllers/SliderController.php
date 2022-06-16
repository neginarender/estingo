<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Slider;
use App\SortingHubSlider;
use Auth;
class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sliders = Slider::all();
        return view('sliders.index', compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('sliders.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $slider = new Slider;
        if($request->hasFile('mobile_photos')){
            $slider->mobile_photo = $request->mobile_photos->store('uploads/sliders');
       
        }

        if($request->hasFile('photos')){
            foreach ($request->photos as $key => $photo) {
                $slider->link = $request->url;
                $slider->link_type = $request->link_type;
                $slider->photo = $photo->store('uploads/sliders');
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
        if(!in_array(Auth::user()->user_type, ['admin'])){
            $slider = SortingHubSlider::find($id);

        }else{
            $slider = Slider::find($id);

        }
        
        return view('sliders.edit')->withSlider($slider);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update_slider(Request $request,$id)
    {
        if(!in_array(Auth::user()->user_type, ['admin'])){
            $slider = SortingHubSlider::find($id);
            if($request->hasFile('mobile_photo')){
                $slider->mobile_photo = $request->mobile_photo->store('uploads/sliders');
        }

        }else{
            $slider = Slider::find($id);

        }
        
        $slider->link = $request->url;
        $slider->link_type = $request->link_type;
        if($request->hasFile('photos')){
            foreach ($request->photos as $key => $photo) {
                
                $slider->photo = $photo->store('uploads/sliders');
                
            }

        }

        if($slider->save()){
            flash(translate('Slider has been updated successfully'))->success();
            if(!in_array(Auth::user()->user_type, ['admin'])){
                return redirect()->back();

            }else{
                return redirect()->route('home_settings.index');
    
            }
            
            
            

        }


    }

    public function update(Request $request, $id)
    {
        if(!in_array(Auth::user()->user_type, ['admin'])){
            $slider = SortingHubSlider::find($id);

        }else{
            $slider = Slider::find($id);
        }
        $slider->published = $request->status;
        if($slider->save()){
            return '1';
        }
        else {
            return '0';
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
        if(!in_array(Auth::user()->user_type, ['admin'])){
            $slider = SortingHubSlider::find($id);

        }else{
            $slider = Slider::findOrFail($id);

        }
        if($slider->delete()){
            //unlink($slider->photo);
            flash(translate('Slider has been deleted successfully'))->success();
        }
        else{
            flash(translate('Something went wrong'))->error();
        }

        if(!in_array(Auth::user()->user_type, ['admin'])){
            return redirect()->back();

        }else{
            return redirect()->route('home_settings.index');

        }
        
    }
}
