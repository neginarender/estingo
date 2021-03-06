<?php

namespace App\Traits;

use Illuminate\Http\Request;

use Razorpay\Api\Api;
trait WalletTrait {
    
    // for Razorpay wallet
    // step 1 
    public function createCustomer(){
        $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));
        $customer = $api->customer->create(array('name' => 'Hasan', 'email' => 'hasan.18pixels@gmail.com'));
        dd($customer);

    }

    // step 2 
    public function createOrderForWallet(){
        // create razorpay payment and use this payment id to create wallet
    }

    // step 3 

    public function createCustomerWallet(){
        $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));
        //transfer amount 100 to a customer cust_HWOjVtX6gYbbJa
        // if customer wallet does not exist wallet will be created automatically
        $paymentId = "pay_I5bdl4MePxzwY6";
        $rzp = $api->payment->fetch($paymentId);
        $transfer  = $rzp->transfer(array('transfers' => [ ['account' => 'cust_I5bl6MC7vPEQE9', 'amount' => 100, 'currency' => 'INR']])); // Create transfer
        dd($transfer);

        
    }

    //RazorpayX Payout api's

    public function apiRequest($method,$endpoint,$requestData){
        try{
        $client = new \GuzzleHttp\Client([
            // 'auth' => ['rzp_test_w0nJ0fzAp2DmTg', '7aG0NFEkKT3IaqIt0910zSb9'],
            'auth' => ['rzp_test_RgMcHuKWdJ6WKE', 'P0gmA7s41qlQsntri2fBeJHe'],
            'request.options' => array(
                'exceptions' => false,
              )
        ]);

        $response = $client->request($method, $endpoint, ['query' => $requestData]);
        if($response->getReasonPhrase()){
            return response()->json([
                'success'=>true,
                'response'=>json_decode((string)$response->getBody())
            ]);
        }

    }  catch (\GuzzleHttp\Exception\ClientException $e) {
      
    } catch(\GuzzleHttp\Exception\ServerException $e){
        
    } catch(\GuzzleHttp\Exception\RequestException $e){
        
    } catch(\GuzzleHttp\Exception\BadResponseException $e){
        
    } catch(\Exception $e){
        
    }
        info($e);
        return response()->json([
            'success'=>false,
            'response'=>json_decode((string)$e->getResponse()->getBody())->error->description
        ]);

    }

    /* Contact section start */
    
    public function createContact($id){
        $endpoint = "https://api.razorpay.com/v1/contacts";
       
        $partner = \App\PeerPartner::findOrFail($id);
        //dd($partner);
        $requestData = [
            "name"=>$partner->name,
            "email"=>$partner->email,
            "contact"=>$partner->phone,
            "type"=>"customer",
            "reference_id"=>"Peer Partner Code ".$partner->code,
            "notes"=>[
              "notes_key_1"=>"Rozana peer partner",
              "notes_key_2"=>"Rozana peer partner"
            ]
        ];

        $res = $this->apiRequest('POST',$endpoint,$requestData);
        //log
        info($res);
        $res = $res->getData();
        if($res->success){
            $contact = $res->response;
            $contact_id = $contact->id;
            // $partner->razorpay_contact_id = $contact_id;
            // $partner->save();
            $partner->user()->update([
                'rzp_contact_id'=>$contact_id
            ]);

            flash('Contact added successfully')->success();
            return back();

        }

        flash('Something went wrong')->error();
        return back();

    }

    public function createContactOnApprove($id){

        $endpoint = "https://api.razorpay.com/v1/contacts";
        $partner = \App\PeerPartner::findOrFail($id);
        //dd($partner);
        $requestData = [
            "name"=>$partner->name,
            "email"=>$partner->email,
            "contact"=>$partner->phone,
            "type"=>"customer",
            "reference_id"=>"Peer Partner Code ".$partner->code,
            "notes"=>[
              "notes_key_1"=>"Rozana peer partner",
              "notes_key_2"=>"Rozana peer partner"
            ]
        ];

        $res = $this->apiRequest('POST',$endpoint,$requestData);
        //log
        info($res);
        $res = $res->getData();
        if($res->success){
            $contact = $res->response;
            $contact_id = $contact->id;
            // $partner->razorpay_contact_id = $contact_id;
            // $partner->save();
            $partner->user()->update([
                'rzp_contact_id'=>$contact_id
            ]);

            return $res;

        }
       return $res;

    }

    public function createContactOnFundAdd($id,$name,$phone){

        $endpoint = "https://api.razorpay.com/v1/contacts";
        $user = \App\User::findOrFail($id);
        //dd($partner);
        $requestData = [
            "name"=>$name,
            //"email"=>$partner->email,
            "contact"=>$phone,
            "type"=>"customer",
            "reference_id"=>"Rozana customer on fund create",
            "notes"=>[
              "notes_key_1"=>"Rozana customer on fund create",
              "notes_key_2"=>"Rozana customer on fund create"
            ]
        ];

        $res = $this->apiRequest('POST',$endpoint,$requestData);
        
        //log
        info($res);
        $res = $res->getData();
        if($res->success){
            $contact = $res->response;
            $contact_id = $contact->id;
            $user->rzp_contact_id = $contact_id;
            $user->save();
            

            return $res;

        }
       return $res;

    }

    /* Create Contact section end */

    /* Fund Account Section start*/
    public function createFundAccount($requestData){

        $endpoint = "https://api.razorpay.com/v1/fund_accounts";
       
       return $this->apiRequest('POST',$endpoint,$requestData);

    }

    public function updateFundAccountStatus($id){
        $bank = \App\BankAccount::findOrFail($id);
        $fund_account_id = $bank->fund_account_id;
        $endpoint = "https://api.razorpay.com/v1/fund_accounts/".$fund_account_id;
        $requestData = ['active'=>false]; // true for activate and false for deactivate
        
       return $this->apiRequest('POST',$endpoint,$requestData);
    }

    function fetchAllFundAccounts(){
        $endpoint = "https://api.razorpay.com/v1/fund_accounts";

    }

    function getFundAccountDetails(){
        $endpoint = "https://api.razorpay.com/v1/fund_accounts/fa_00000000000001";
    }

    /* Fund Account section end */

    /* Payout section start */

    public function createPartnerPayout($id){
       
        $endpoint = "https://api.razorpay.com/v1/payouts";
        $wallet = \App\WalletPayout::findorFail($id);
        $tds = ($wallet->amount*$wallet->tds)/100;
        $final_amount = $wallet->amount-$tds;
        if(!is_null($wallet->account_number)){
            $requestData = [
                "account_number"=>'2323230031364031',
                "fund_account_id"=>$wallet->fund_account_number,
                "amount"=>round($final_amount,2)*100,
                "currency"=>"INR",
                "mode"=>"IMPS",
                "purpose"=>"refund",
                "queue_if_low_balance"=>true,
                "reference_id"=>"Payout Request ID ".$wallet->id,
                "narration"=>"Peer Partner Fund Transfer",
                "notes"=>[
                  "notes_key_1"=>"Rozana, Peer Partner Fund Transfer",
                  "notes_key_2"=>"Rozana, Peer Partner Fund Transfer"
                ]
            ];
        }else{
            $requestData = [
                "account_number"=>'2323230031364031',
                "fund_account_id"=>$wallet->fund_account_number,
                "amount"=>round($final_amount,2)*100,
                "currency"=>"INR",
                "mode"=>"UPI",
                "purpose"=>"refund",
                "queue_if_low_balance"=>true,
                "reference_id"=> "test",
                "narration"=>"test",
                "notes"=>[
                  "notes_key_1"=>"Rozana, Peer Partner Fund Transfer",
                  "notes_key_2"=>"Rozana, Peer Partner Fund Transfer"
                ]
            ];
        }
        

        return $this->apiRequest("POST",$endpoint,$requestData);

        
    }

    /* Payout section end */
}