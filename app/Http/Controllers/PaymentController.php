<?php

// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Unicodeveloper\Paystack\Facades\Paystack;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    /**
     * Redirect the User to Paystack Payment Page
     * @param Request $request
     * @return Url
     */
    public function redirectToGateway(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'amount' => 'required|integer',
        ]);

        try {
            return Paystack::getAuthorizationUrl()->redirectNow();
        } catch (\Exception $e) {
            return back()->with('error', 'The Paystack token has expired. Please try again.');
        }
    }

    /**
     * Poll the payment status from Paystack.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function pollPaymentStatus($reference)
    {
        $response = Http::withToken(config('paystack.secretKey'))
            ->get(config('paystack.paymentUrl') . '/transaction/verify/' . $reference);
        
        if ($response->successful() && $response['data']['status'] === 'success') {
            session(['paymentDetails' => $response['data']]);
            return redirect()->route('payment.success');
        }

        return view('payment.poll', ['reference' => $reference]);
    }

    /**
     * Display the payment success page.
     * @return \Illuminate\Http\Response
     */
    public function showSuccessPage()
    {
        $paymentDetails = session('paymentDetails');
        return view('payment.success', ['paymentDetails' => $paymentDetails]);
    }
}
