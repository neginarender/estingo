<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller {

    public function index(){

    }

    public function create(){
        
        return view('uploads.create');
    }

    public function store(Request $request){

        if($request->hasFile('photos')){
            foreach ($request->photos as $key => $photo) {
                $upload = new \App\Upload;
                $upload->title = $request->title;
                $upload->photo = $photo->store('emailer');
                $upload->save();
            }
            flash(translate('Photo uploaded successfully'))->success();
        }
        return redirect()->route('home_settings.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id){

    }

    public function update(Request $request){

    }

    public function destroy($id){
        $upload = \App\Upload::findOrFail($id);
        if(\App\Upload::destroy($id)){
            flash(translate('Banner has been deleted successfully'))->success();
        }
        else{
            flash(translate('Something went wrong'))->error();
        }
        return redirect()->route('home_settings.index');
    }

    public function delete($id){
        $upload = \App\Upload::findOrFail($id);
        if(\App\Upload::destroy($id)){
            flash(translate('Banner has been deleted successfully'))->success();
        }
        else{
            flash(translate('Something went wrong'))->error();
        }
        return redirect()->route('home_settings.index');
    }
}