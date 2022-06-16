<?php
namespace App\Http\Controllers\Api\v4;
use Illuminate\Http\Request;
use DB;
Class AppController extends Controller{

    public function checkUpdates(Request $request){
        $version = $request->app_version;
        $version_code = $request->app_versionCode;
        $platform = $request->platform;
        $update = false;
        // $update = true;
        $check  = DB::table('app_versions')->where(['platform'=>$platform])->first();
        if($platform == 'ios' || $platform == 'IOS' || $platform == 'iOS'){
            if(!is_null($check)){
                // app up to date 
                if($version > $check->current_version){
                    //update version code 
                    // DB::table('app_versions')->where(['platform'=>$platform])->update(['version_code'=>$version_code,'current_version'=>$version]);

                    return response()->json([
                        'success'=>true,
                        'message'=>'Your app is up to date',
                        'update_type'=>0
                    ]);
                }
                else if($version == $check->current_version){
                    return response()->json([
                        'success'=>true,
                        'message'=>'Your app is up to date',
                        'update_type'=>0
                    ]);
                } 
                else{
                    $app  = DB::table('app_versions')->where(['platform'=>$platform])->first();
                    return response()->json([
                        'success'=>true,
                        'message'=>'Update available',
                        'update_type'=>$app->update_type
                    ]);
                }
            }
        }else{
            if(!is_null($check)){
                // app up to date 
                if($version_code>$check->version_code){
                    //update version code 
                    // DB::table('app_versions')->where(['platform'=>$platform])->update(['version_code'=>$version_code,'current_version'=>$version]);

                    return response()->json([
                        'success'=>true,
                        'message'=>'Your app is up to date',
                        'update_type'=>0
                    ]);
                }
                else if($version_code == $check->version_code){
                    return response()->json([
                        'success'=>true,
                        'message'=>'Your app is up to date',
                        'update_type'=>0
                    ]);
                } 
                else{
                    $app  = DB::table('app_versions')->where(['platform'=>$platform])->first();
                    return response()->json([
                        'success'=>true,
                        'message'=>'Update available',
                        'update_type'=>$app->update_type
                    ]);
                }
            }
        }
       
    }
}