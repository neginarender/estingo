<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ServiceBanner;


class ServiceNotAvailable extends Controller
{
    //

    public function index()
    {
        $sliders = BannerSlider::all();
        return view('service-not-available.index', compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {	
        return view('service-not-available.create');
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
                $slider = new ServiceBanner;
                $slider->link = $request->url;
                $slider->photo = $photo->store('uploads/service_banner');
                $slider->save();
            }
            flash(translate('Service Banner has been inserted successfully'))->success();
        }
        return redirect()->route('home_settings.index');
    }
}
