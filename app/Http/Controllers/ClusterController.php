<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cluster;
use DB;
use App\User;
use App\Staff;
use Hash;
class ClusterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clusters = Cluster::paginate(10);
        return view('cluster_hub.index', compact('clusters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('cluster_hub.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(User::where('email', $request->email)->first() != null){
            flash(translate('Email already exists!'))->error();
            return back();
        }

        DB::beginTransaction();
        try{
            
            $user = new User;
            $user->name = $request->cluster_name;
            $user->email = $request->email;
            $user->user_type = 'staff';
            $user->password = Hash::make('123456');

            if($user->save()){
                $cluster = new Cluster;
                $cluster->user_id = $user->id;
                // $cluster->region_id = $request->region_id;
                $cluster->state_id = json_encode($request->state_id);
                $cluster->cities = json_encode($request->city_ids);
                $maptype = str_replace('_', ' ', $request->mapping_type);
                if($cluster->save()){

                    $staff = new Staff;
                    $staff->user_id = $user->id;
                    $staff->role_id = 4;

                    if($staff->save()) {
                        $array['subject'] = $maptype.' Account Regsitration';
                        $array['user_id'] = $user->email;
                        $array['password'] = '123456';
                        $array['account_type'] = $maptype;

                        //Mail::to($request->email)->queue(new MappingMailManager($array));

                        DB::commit();
                        flash(translate($maptype.' has been created successfully'))->success();
                        return redirect()->route('clusterhub.index');
                    }
                }
            }
        }catch(Exception $e) {
            DB::rollBack();
            flash(translate('Something went wrong'))->error();
            return redirect()->back();
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
    {   $cluster = Cluster::findOrFail(decrypt($id));
        $state_ids = json_decode($cluster->state_id,true);//
        return view('cluster_hub.edit', compact('cluster'),compact('state_ids'));
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
        $cluster = Cluster::findOrFail(decrypt($id));
        //$cluster->user->name = $request->cluster_name;
        $cluster->user()->update(array('name'=>$request->cluster_name));
        DB::beginTransaction();
        try{
            
            $cluster->region_id = $request->region_id;
            $cluster->state_id = json_encode($request->state_id);
            $cluster->cities = json_encode($request->city_ids);
            $cluster->save();
            DB::commit();
            flash(translate('updated successfully'))->success();
            return redirect()->route('clusterhub.index');
        }catch(Exception $e) {
            DB::rollBack();
            flash(translate('Something went wrong'))->error();
            return redirect()->back();
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
        $cluster = Cluster::findOrFail($id);
        DB::beginTransaction();
        try{
            Staff::where('user_id', $cluster->user_id)->delete();
            User::destroy($cluster->user->id);
            if(Cluster::destroy($id)){
                DB::commit();
                flash(translate('Cluster has been deleted successfully'))->success();
                return redirect()->route('clusterhub.index');
            }
        }catch(Exception $e) {
            DB::rollBack();
            flash(translate('Something went wrong'))->error();
            return redirect()->back();
        }
    }

    public function approve_cluster(Request $request)
    {
        $cluster = Cluster::findOrFail($request->id);
        $cluster->status = $request->status;
        if($cluster->save()){
            return 1;
        }
        return 0;
    }

     public function login($id)
    {
        $cluster = Cluster::findOrFail(decrypt($id));

        $user  = $cluster->user;

        auth()->login($user, true);

        return redirect()->route('admin.dashboard');
    }

    public function load_city(Request $request)
    {
        $state_ids = $request->state_id;
        $cluster = Cluster::where('id',$request->cluster_id)->first();
        $cities = json_decode($cluster->cities,true);
        return view('cluster_hub.load_city',compact('state_ids','cities'));
    }


}
