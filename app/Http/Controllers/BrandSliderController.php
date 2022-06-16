<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\HomeCategory;
use App\Product;
use App\BrandSlider;
use App\Language;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class BrandSliderController extends Controller
{
    //
    public function index(Request $request)
    {
        $sort_search =null;
        $categories = BrandSlider::orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $categories = $categories->where('name', 'like', '%'.$sort_search.'%');
        }
        $categories = $categories->paginate(15);
        return view('brand-sliders.index', compact('categories', 'sort_search'));
    }

    public function create()
    {
        return view('brand-sliders.create');
    }

    public function store(Request $request)
    {

        $brandslider = new BrandSlider;
        $brandslider->name = $request->name;

        if($request->hasFile('banner')){
                $path = 'uploads/brand_banner/banner/';
                if(!file_exists($path)){
                    File::makeDirectory($path, $mode = 0777, true, true);
                    $brandslider->banner = $request->file('banner')->store('/uploads/brand_banner/banner');
                }else{
                    $brandslider->banner = $request->file('banner')->store('/uploads/brand_banner/banner');
                }

            
        }
        if($request->hasFile('icon')){

            $path = 'uploads/brand_banner/logo/';
            if(!file_exists($path)){
                File::makeDirectory($path, $mode = 0777, true, true);
                $brandslider->icon = $request->file('icon')->store('/uploads/brand_banner/icon');
            }else{
                $brandslider->icon = $request->file('banner')->store('/uploads/brand_banner/icon');
            }
        }

        if($brandslider->save()){
            flash(translate('Brand Slider  has been inserted successfully'))->success();
            return redirect()->route('brandslider.index');
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }


    public function destroy($id)
    {

        if(BrandSlider::destroy($id)){
            flash(translate('Brand Slider has been deleted successfully'))->success();
            return redirect()->route('brandslider.index');
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }


    public function updateFeatured(Request $request)
    {
        $brandslider = BrandSlider::findOrFail($request->id);
        $brandslider->featured = $request->status;
        if($brandslider->save()){
            return 1;
        }
        return 0;
    }

    public function updateStatus(Request $request)
    {
        $brandslider = BrandSlider::findOrFail($request->id);
        $brandslider->status = $request->status;
        if($brandslider->save()){
            return 1;
        }
        return 0;
    }

    public function edit($id)
    {
        $category = BrandSlider::findOrFail(decrypt($id));
        return view('brand-sliders.edit', compact('category'));
    }


    public function update(Request $request, $id)
    {
        $brandslider = BrandSlider::findOrFail($id);


        $brandslider->name = $request->name;

        if($request->hasFile('banner')){
            $brandslider->banner = $request->file('banner')->store('uploads/brand_banner/banner');
        }
        if($request->hasFile('icon')){
            $brandslider->icon = $request->file('icon')->store('uploads/brand_banner/icon');
        }

        if($brandslider->save()){
            flash(translate('Brand Slider has been updated successfully'))->success();
            return redirect()->route('brandslider.index');
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }
}
