<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\Opening;
use App\JobLocation;

class OpeningController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $openings = Opening::where('status',1)->orderBy('created_at','DESC');
        $openings =$openings->paginate(10);
        return view('opening.index', compact('openings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $jobLocations = JobLocation::where('status','1')->orderBy('city','ASC')->get();
        return view('opening.create',compact('jobLocations'));
    }


    public function delete_opening($id){

        $open = Opening::findOrFail($id);
        if($open){
            $update = DB::table('openings')
            ->where('id', $id)
            ->update([
                'status'     => 0,
                'updated_by' =>Auth::user()->id,

            ]);

            flash(translate('Vaccancy has been deleted successfully'))->success();
            return redirect()->back();
        }
        return back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $opening = new Opening;
        $opening->designation = $request->designation;
        $opening->role = $request->role;
        $opening->num_position = $request->num_position;
        if(is_array($request->location)){
            $opening->location = implode(',',$request->location);
        }
        // $opening->location = $request->location;
        $opening->salary = $request->salary;
        $opening->education_req = $request->education_req;
        $opening->experience_req = $request->experience_req;

        $opening->save();
        flash(translate('Vaccancy has been inserted successfully'))->success();
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Addon  $addon
     * @return \Illuminate\Http\Response
     */
    public function show(Addon $addon)
    {
        //
    }

    public function list()
    {
        //return view('backend.'.Auth::user()->role.'.addon.list')->render();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Addon  $addon
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $open = Opening::findOrFail($id);
        if($open){
            $openings = Opening::where('id',$id)->first();
            $jobLocations = JobLocation::where('status','1')->orderBy('city','ASC')->get();
            return view('opening.edit', compact('openings','jobLocations'));
        }
        return redirect()->back();

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Addon  $addon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $open = Opening::findOrFail($id);
        if(is_array($request->location)){
            $location = implode(',',$request->location);
        }

        if($open){
            $update = DB::table('openings')
            ->where('id', $id)
            ->update([
                'designation' => $request->designation,
                'role' => $request->role,
                'num_position' => $request->num_position,
                'location' => $location,
                'salary' => $request->salary,
                'education_req' => $request->education_req,
                'experience_req' => $request->experience_req,
                'status'     => 1,
                'updated_by' =>Auth::user()->id,

            ]);

            flash(translate('Vaccancy has been updated successfully'))->success();
            return redirect()->route('opening.index');
        }
        return redirect()->back();
    }

 }
