<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MasterBanner;

class MasterBannerController extends Controller
{
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banners = MasterBanner::all();
        return view('master-banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('master-banners.create', compact('position'));
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
            $banner = new MasterBanner;
            $banner->photo = $request->photo->store('uploads/master/banners');
            $banner->link = $request->url;
            $banner->link_type = $request->link_type;
            $banner->save();
            flash(translate('Banner has been inserted successfully'))->success();
        }
        return redirect()->route('home_settings.index');
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        $banner = MasterBanner::findOrFail($id);
        return view('master-banners.edit', compact('banner'));
    }


    public function update(Request $request, $id)
    {
        $banner = MasterBanner::find($id);
        $banner->photo = $request->previous_photo;
        if($request->hasFile('photo')){
            $banner->photo = $request->photo->store('uploads/master/banners');
        }
        $banner->link = $request->url;
        $banner->link_type = $request->link_type;
        $banner->save();
        flash(translate('Banner has been updated successfully'))->success();
        return redirect()->route('home_settings.index');
    }


    public function update_status(Request $request)
    {
        $banner = MasterBanner::find($request->id);
        $banner->published = $request->status;
        if($request->status == 1){
            if(count(MasterBanner::where('published', 1)->get()) < 3)
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


    public function destroy($id)
    {
        $banner = MasterBanner::findOrFail($id);
        if(MasterBanner::destroy($id)){
            //unlink($banner->photo);
            flash(translate('Banner has been deleted successfully'))->success();
        }
        else{
            flash(translate('Something went wrong'))->error();
        }
        return redirect()->route('home_settings.index');
    }
}
