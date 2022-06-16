<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SortingHubManager;
use App\Staff;
use Auth;
use App\Cluster;
use App\User;
use App\ShortingHub;
use Session;
use DB;
use Mail;
use Hash;

class SortingHubManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(auth()->user()->staff->role->name == "Sorting Hub"){
            $sorting_hub_id = (auth()->user()->staff->role->name == "Sorting Hub Manager") ? auth()->user()->sortinghubmanager->sorting_hub_id: Auth::user()->id;
            $sorting_hub_manager = SortingHubManager::where('sorting_hub_id',$sorting_hub_id)->get();

        }
        return view('sorting_hub.list_sorting_hub_manager', compact(['sorting_hub_manager']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sorting_hub_name = ShortingHub::where('user_id',auth()->user()->id)->first();
        // dd($sorting_hub_name);
        return view('sorting_hub.create_sorting_hub_manager',compact(['sorting_hub_name']));
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
            $user->name = $request->name;
            $user->email = $request->email;
            $user->user_type = 'staff';
            $user->password = Hash::make('123456');

            if($user->save()){
                $sortingmanager = new SortingHubManager;
                $sortingmanager->user_id = $user->id;
                $sortingmanager->cluster_hub_id = $request->cluster_hub_id;
                $sortingmanager->sorting_hub_id = $request->sorting_hub_id;
                $sortingmanager->phone = $request->phone;
                // $deliveryboy->area_id = $request->area_id;

                if($sortingmanager->save()){
                        $staff = new Staff;
                        $staff->user_id = $user->id;
                        $staff->role_id = 8;

                        if($staff->save()){
                        DB::commit();
                            flash(translate('Sorting Manager has been inserted successfully'))->success();
                            return redirect()->route('sortingmanager.index');
                        }else{
                            flash(translate('Somthing went wrong'))->error();
                            return redirect()->back();
                        }

                    }
            }
        }catch(Exception $e){
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
    {
        $sorting_hub_name = ShortingHub::where('user_id',auth()->user()->id)->first();
        $sortingmanager = SortingHubManager::find($id);
        return view('sorting_hub.edit_sorting_hub_manager', compact(['sortingmanager','sorting_hub_name']));
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
        $sortingmanager = SortingHubManager::find(decrypt($id));


         $sortingmanager->user->name = $request->name;
         $sortingmanager->phone = $request->phone;
         $sortingmanager->area_id = $request->area_id;
         $sortingmanager->user->save();

         if($sortingmanager->save()){
                flash(translate('Sorting Manager has been update successfully'))->success();
                return redirect()->route('sortingmanager.index');
            }else{
                flash(translate('Somthing went wrong'))->error();
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
        $sortingmanager = SortingHubManager::find($id);
        Staff::where('user_id', $sortingmanager->user_id)->delete();
        User::destroy($sortingmanager->user_id);
        if(!empty($sortingmanager)){
            if($sortingmanager->delete()){
                 flash(translate('Sorting Manager has been delete successfully'))->success();
                 return redirect()->route('sortingmanager.index');
            }else{
                  flash(translate('Somthing went wrong'))->error();
                  return redirect()->back();
            }
        }
    }

    public function login($id)
    {
        $sortingmanager = SortingHubManager::findOrFail(decrypt($id));

        $user  = $sortingmanager->user;

        auth()->login($user, true);

        return redirect()->route('admin.dashboard');
    }
}
