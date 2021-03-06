<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Resources\v3\WalletCollection;
use App\User;
use App\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function balance($id)
    {
        $user = User::find($id);
        return response()->json([
            'balance' => $user->balance
        ]);
    }

    public function walletRechargeHistory($id)
    {
        return new WalletCollection(Wallet::where('user_id', $id)->latest()->get());
    }

    public function processPayment(Request $request)
    {
        $order = new OrderController;
        $user = User::find($request->user_id);

        if ($user->balance >= $request->grand_total) {
            $user->balance -= $request->grand_total;
            $user->save();

            return $order->processOrder($request);
        }
        else {
            return response()->json([
                'success' => false,
                'message' => 'The order was not completed becuase the payment is invalid'
            ]);
        }
    }
}
