<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\PeerPartner;
use App\User;
use Hash;
use DB;
use App\BusinessSetting;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Support\Str;

class PeerPartnerController extends Controller
{
    public function index(Request $request){

        //dd($request->all());

        $sort_search = null;
        $approved = null;
        $peer_partner = PeerPartner::orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $user_ids = User::where('user_type', 'partner')->where(function($user) use ($sort_search){
                $user->where('name', 'like', '%'.$sort_search.'%')->orWhere('email', 'like', '%'.$sort_search.'%');
            })->pluck('id')->toArray();
            $peer_partner = $peer_partner->where(function($peer_partner) use ($user_ids){
                $peer_partner->whereIn('user_id', $user_ids);
            });
        }
        if ($request->approved_status != null) {
            $approved = $request->approved_status;
            $peer_partner = $peer_partner->where('verification_status', $approved);
        }
        $peer_partner = $peer_partner->paginate(10);

        return view('frontend.peer_partner.index', compact('peer_partner'));
    }

    public function create(Request $request){

    	if(Auth::check() && Auth::user()->user_type == 'admin'){
            flash(translate('Admin can not be a peer partner'))->error();
            return back();
        }elseif(Auth::check() && Auth::user()->peer_partner == 0){
            return view('frontend.peer_partner.create');
        }elseif(Auth::check() && Auth::user()->peer_partner == 1){

            $peer_partner = PeerPartner::where('user_id', Auth::user()->id)->first();
            if(!empty($peer_partner)){
                if($peer_partner->verification_status == 1){
                     flash(translate('You have already a peer partner account'))->warning();
                     return back();
                }else{
                     flash(translate('Peer partner request is on process'))->warning();
                     return back();
                }
            } flash(translate('Something went wrong'))->error();
            return back();
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function store(Request $request){

    	$user = null;

    	DB::beginTransaction();

    	try {
    		
    		if(!Auth::check()){
            if(User::where('email', $request->email)->first() != null){
                flash(translate('Email already exists!'))->error();
                return back();
            }
            if($request->password == $request->password_confirmation){
                $user = new User;
                $user->name = $request->name;
                $user->email = $request->email;
                $user->phone = $request->phone;
                $user->address = $request->address;
                $user->user_type = "partner";
                $user->peer_partner = 1;
                $user->password = Hash::make($request->password);
                $user->save();
            }
            else{
                flash(translate('Sorry! Password did not match.'))->error();
                return back();
            }
        }
        else{
            $user = Auth::user();
            $user->user_type = "customer";
            $user->peer_partner = 1;
            $user->save();
        }

        if(PeerPartner::where('user_id', $user->id)->first() == null){

            $peer_partner = new PeerPartner;
            $peer_partner->user_id = $user->id;
            $peer_partner->verification_status = 0;

            if($request->fb_account == 1){
                $peer_partner->facebook = 1;
                $peer_partner->facebook_page = $request->fb_page_name;
                $peer_partner->fb_follower;
            }

            if($request->instagram_account == 1){
                $peer_partner->instagram = 1;
                $peer_partner->instagram_page = $request->instagram_page_name;
                $peer_partner->instagram_follower = $request->instagram_follower;
            }

            if($request->linkedin_account == 1){
                $peer_partner->linkedin = 1;
                $peer_partner->linkedin_page = $request->linkedin_profile_name;
                $peer_partner->linkedin_follower = $request->linkedin_follower;
            }

            if($peer_partner->save()){
                auth()->login($user, false);
                if(BusinessSetting::where('type', 'email_verification')->first()->value != 1){
                    $user->email_verified_at = date('Y-m-d H:m:s');
                    $user->save();
                }
                else {
                    $user->notify(new EmailVerificationNotification());
                }

                DB::commit();

                if(Auth::check() && Auth::user()->user_type == 'admin'){
                    flash(translate('Peer Partner account been created successfully!'))->success();
                    return redirect()->route('peer_partner.index');
                }else{
                    flash(translate('Peer Partner account request been send successfully!'))->success();
                    return redirect()->route('dashboard');
                }
            }
            else{
                $peer_partner->delete();
                $peer_partner->user_type == 'customer';
                $user->peer_partner = 0;
                $peer_partner->save();
               	DB::commit();
            }
        }

    	} catch (Exception $e) {
    		 DB::rollback();
    		 flash(translate('Sorry! Something went wrong.'))->error();
        	 return back();
    	}
    }

    public function updateApproved(Request $request)
    {
        DB::beginTransaction();

        try {
            $partner = PeerPartner::findOrFail($request->id);
            if($request->status != 0){
              
                $partner->verification_status = $request->status;

                do {
                   $referral_code = Str::random(9);
                } while ( DB::table(app()->make(\App\PeerPartner::class)->getTable())->where('code', $referral_code)->exists());

                $partner->code = $referral_code;
                $partner->discount = 10;
                $partner->commission = 5;
            }else{
                $partner->verification_status = $request->status;
                $partner->save();
            }
            $partner->save();
            DB::commit();
            return 1;

        } catch (Exception $e) {
            DB::rollback();
            return 0;
        }
    }

    public function login($id)
    {
        $partner = PeerPartner::findOrFail(decrypt($id));
        $partner  = $partner->user;
        auth()->login($partner, true);
        return redirect()->route('dashboard');
    }


    public function ban($id) {

        $partner = PeerPartner::findOrFail($id);
        if($partner->user->banned == 1) {
            $partner->user->banned = 0;
        } else {
            $partner->user->banned = 1;
        }

        $partner->user->save();
        return back();
    }

    public function destroy($id)
    {
        $partner = PeerPartner::findOrFail($id);
        User::destroy($partner->user->id);
        if(PeerPartner::destroy($id)){
            flash(translate('Peer Partner has been deleted successfully'))->success();
            return redirect()->route('peer_partner.index');
        }
        else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function profile_modal(Request $request){

        $partner = PeerPartner::findOrFail($request->id);
        return view('frontend.peer_partner.profile_modal', compact('partner'));
    }

}
