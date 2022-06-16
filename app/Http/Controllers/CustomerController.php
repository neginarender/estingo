<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\User;
use App\Order;
use Illuminate\Support\Str;
use DB;
use App\PeerPartner;
use App\Notifications\EmailVerificationNotification;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = null;
        $customers = Customer::orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $user_ids = User::where('user_type', 'customer')->where(function($user) use ($sort_search){
                $user->where('name', 'like', '%'.$sort_search.'%')
                ->orWhere('email', 'like', '%'.$sort_search.'%')
                ->orWhere('phone', 'like', '%'.$sort_search.'%');
            })->pluck('id');
            
            $customers = $customers->whereIn('user_id', $user_ids);
        }
        $customers = $customers->paginate(15);
        return view('customers.index', compact('customers', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Order::where('user_id', Customer::findOrFail($id)->user->id)->delete();
        User::destroy(Customer::findOrFail($id)->user->id);
        if(Customer::destroy($id)){
            flash(translate('Customer has been deleted successfully'))->success();
            return redirect()->route('customers.index');
        }

        flash(translate('Something went wrong'))->error();
        return back();
    }

    public function login($id)
    {
        $customer = Customer::findOrFail(decrypt($id));

        $user  = $customer->user;

        auth()->login($user, true);

        return redirect()->route('dashboard');
    }

    public function ban($id) {

        $customer = Customer::findOrFail($id);

        if($customer->user->banned == 1) {
            $customer->user->banned = 0;
        } else {
            $customer->user->banned = 1;
        }
        $customer->user->save();
        return back();
    }


    public function add_peer_partner($id){

        $customer = Customer::findOrFail($id);
        DB::beginTransaction();

        try {
            
            if($customer->user->peer_partner == 1) {
                $customer->user->peer_partner = 0;
                $customer->user->user_type = 'customer';

                if($customer->user->save()){
                    $peer_partner = PeerPartner::where('user_id', $customer->user->id)->delete();
                }
                
                DB::commit();
                flash(translate('Peer Partner account been removed successfully!'))->success();
                return redirect()->back();
                
            } else {

                if(PeerPartner::where('user_id', $customer->user->id)->first() == null){
                    $peer_partner = new PeerPartner;
                    $peer_partner->user_id = $customer->user->id;
                    $peer_partner->verification_status = 0;

                    do {
                        $referral_code = strtoupper('REF'.auth()->user()->id.Str::random(3));
                    } while ( DB::table(app()->make(\App\PeerPartner::class)->getTable())->where('code', $referral_code)->exists());

                    $peer_partner->code = $referral_code;
                    $peer_partner->name = $peer_partner->user->name;
                    $peer_partner->email = $peer_partner->user->email;
                    $peer_partner->phone = $peer_partner->user->phone;
                    $peer_partner->added_by = auth()->user()->id;
                    $peer_partner->discount = 10;
                    $peer_partner->commission = 5;

                    if($peer_partner->save()){

                        $customer->user->peer_partner = 1;
                        $customer->user->user_type = 'partner';
                        $customer->user->save();
                        
                        //$customer->user->notify(new EmailVerificationNotification());
                        DB::commit();

                        flash(translate('Peer Partner account been created successfully!'))->success();
                        return redirect()->back();
                    }
                }
            }

        } catch (Exception $e) {
            DB::rollback();
            flash(translate('Something went wrong!'))->error();
            return redirect()->back();
        }
    }

    public function addToCustomerIfNotExist(){
        $customers = User::where('user_type','customer')->select('id')->get();
       
        foreach($customers as $key => $customer){
            $check = Customer::where('user_id',$customer->id)->first();
            if(is_null($check)){
                $cstmr = new Customer;
                $cstmr->user_id = $customer->id;
                $cstmr->save();
            }
        }
        echo "Done";
    }
}
