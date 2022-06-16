<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DeviceManagement;
use DB;
class OnesignalController extends Controller
{
    //

    protected static $app_id = "";
    protected static $api_key = "";

    public function getPlayerIds(REQUEST $request){
        $player_id = array();
        $message = "";
        $status = true;
        DB::beginTransaction();
        try{
            if(!empty($request->user_id) || !empty($request->device_id)){
                if(!empty($request->user_id)){
                    $user = DeviceManagement::where('user_id',$request->user_id)->whereJsonContains('onesignal_player_id',[$request->platform=>$request->onesignal_player_id])->first();
                    if(!empty($user)){
                        $message = "Player id has already been registered.";
                        return response()->json([
                            "status" => $status,
                            "message" => $message
                        ]);

                    }else{
                        $user = DeviceManagement::whereJsonContains('onesignal_player_id',[$request->platform=>$request->onesignal_player_id])->first();
                        if(!empty($user)){
                            $user->device_id = $request->device_id;
                            $user->user_id = $request->user_id;
                        }else{
                            $user = DeviceManagement::where('user_id',$request->user_id)->first();
                            if(!empty($user)){
                                $player_id[$request->platform] = $request->onesignal_player_id;
                                $user->onesignal_player_id = json_encode($player_id);
                            }else{
                                $user = new DeviceManagement;
                                $player_id[$request->platform] = $request->onesignal_player_id;
                                $user->onesignal_player_id = json_encode($player_id);
                                $user->device_id = $request->device_id;
                                $user->user_id = $request->user_id;

                            }
                            

                        }

                    }
                }else{
                    $user = DeviceManagement::where('device_id',$request->device_id)->whereJsonContains('onesignal_player_id',[$request->platform=>$request->onesignal_player_id])->first();
                    if(!empty($user)){
                        $message = "Player id has already been registered.";
                        return response()->json([
                            "status" => $status,
                            "message" => $message
                        ]);

                    }else{
                            $user = DeviceManagement::where('device_id',$request->device_id)->first();
                            if(!empty($user)){
                                $player_id[$request->platform] = $request->onesignal_player_id;
                                $user->onesignal_player_id = json_encode($player_id);
                            }else{
                                $user = DeviceManagement::whereJsonContains('onesignal_player_id',[$request->platform=>$request->onesignal_player_id])->first();
                                if(!empty($user)){
                                    $user->device_id = $request->device_id;
                                }else{
                                    $user = new DeviceManagement;
                                    $player_id[$request->platform] = $request->onesignal_player_id;
                                    $user->onesignal_player_id = json_encode($player_id);
                                    $user->device_id = $request->device_id;

                                }
                                

                            }
                            

                    }

                }
    
            }else{
                $user = DeviceManagement::whereJsonContains('onesignal_player_id',[$request->platform=>$request->onesignal_player_id])->first();
                if(!empty($user)){
                    $message = "Player id has already been registered.";
                    return response()->json([
                        "status" => $status,
                        "message" => $message
                    ]);
                }else{
                    $user = new DeviceManagement;
                    $player_id[$request->platform] = $request->onesignal_player_id;
                    $user->onesignal_player_id = json_encode($player_id);

                }
                
            }
            $user->platform = $request->platform;

            if($user->save()){
                $message = "Execute successfully.";
                $status = true;
                DB::commit();

            }
            

        }catch(\Exception $e){
            DB::rollback();
            $status = false;
            $message = $e->getMessage();
        }
        return response()->json([
            "status" => $status,
            "message" => $message
        ]);
        

    }


    public function getOneSignalKey(){
        $cred = array();
        $cred['appId'] = self::$app_id;
        $cred['apiKey'] = self::$api_key;

        return $cred;

    }


    public function sendNotification(REQUEST $request){
        dd($request->all());

    }



}
