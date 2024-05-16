<?php

namespace App\Http\Controllers;

use App\Models\Gateway;
use App\Models\Transaction;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function index(){
        
        $gateways = Gateway::first();
        $transactions = Transaction::latest()->get();
        return view('dashboard', compact('gateways', 'transactions'));
    }

    public function update(Request $request){
        $gateways = Gateway::first();
        if($gateways){
            $gateways->update([
                'paystack' => $request->paystack ==1 ? 1 : 0,
                'monnify' => $request->monnify ==1 ? 1 : 0,
            ]);
        }

        return back();
    }
}
