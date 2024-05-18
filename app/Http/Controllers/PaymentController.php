<?php

// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\GatewaySelector;
use App\Models\Transaction;
use Unicodeveloper\Paystack\Facades\Paystack;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function initializePayment(Request $request)
    {
        $gateway = GatewaySelector::getActiveGateway();

        if (!$gateway) {
            return back()->with('error', 'No active payment gateway found. Please try again later.');
        }

        $request->validate([
            'email' => 'required|email',
            'amount' => 'required|numeric',
        ]);

        if ($gateway === 'paystack') {
            return $this->initializePaystackPayment($request);
        } elseif ($gateway === 'monnify') {
            return $this->initializeMonnifyPayment($request);
        }

        return back()->with('error', 'Failed to initialize payment. Please try again.');
    }

    private function initializePaystackPayment(Request $request)
    {
        $amountInKobo = $request->amount * 100; // Convert amount to kobo
        $request->merge(['amount' => $amountInKobo]); // Update request amount

        try {
            return Paystack::getAuthorizationUrl()->redirectNow();
        } catch (\Exception $e) {
            return back()->with('error', 'The Paystack token has expired. Please try again.');
        }
    }

    private function initializeMonnifyPayment(Request $request)
    {
        $transactionReference = Str::uuid();
        $amount = $request->amount * 100; // Convert to kobo

        $response = Http::withBasicAuth(config('services.monnify.api_key'), config('services.monnify.secret_key'))
            ->post(config('services.monnify.base_url') . '/merchant/transactions/init-transaction', [
                'amount' => $amount / 100,
                'customerName' => $request->email,
                'customerEmail' => $request->email,
                'paymentReference' => $transactionReference,
                'paymentDescription' => 'Payment for goods',
                'currencyCode' => 'NGN',
                'contractCode' => config('services.monnify.contract_code'),
                'redirectUrl' => route('payment.callback'),
            ]);

            if ($response->successful()) {
                $responseBody = $response->json();
                $responseBody = array_merge($request->all(), $responseBody);

                // Store payment details in session
                Session::put('paymentDetails', $responseBody);

                return redirect($responseBody['responseBody']['checkoutUrl']);
            }

            return back()->with('error', 'Failed to initialize Monnify payment. Please try again.');
        
    }

    public function handleCallback(Request $request)
    {
        $gateway = GatewaySelector::getActiveGateway();

        if ($gateway === 'paystack') {
            return $this->handlePaystackCallback($request);
        } elseif ($gateway === 'monnify') {
            return $this->handleMonnifyCallback($request);
        }

        return back()->with('error', 'Failed to process payment. Please try again.');
    }

    private function handlePaystackCallback(Request $request)
    {
        $paymentDetails = Paystack::getPaymentData();

        if ($paymentDetails['status'] == 'success') {
            return redirect()->route('payment.success')->with('paymentDetails', $paymentDetails['data']);
        }

        return redirect()->route('payment.failed');
    }

    private function handleMonnifyCallback(Request $request)
    {
        $transactionReference = $request->query('paymentReference');
         // Retrieve payment details from session
         $paymentDetails = Session::get('paymentDetails');
        
        $response = Http::withBasicAuth(config('services.monnify.api_key'), config('services.monnify.secret_key'))
            ->get(config('services.monnify.base_url') . '/merchant/transactions/query', [
                'paymentReference' => $transactionReference,
            ]);

        if ($response->successful()) {
            $transactionDetails = $response->json()['responseBody'];
           
            $paymentDetails = Session::get('paymentDetails');

            $status = $transactionDetails['paymentStatus'];
            if ($status === 'PAID') {
                $status = 'Completed';
            } elseif ($status === 'FAILED') {
                $status = 'Failed';
            } elseif ($status === 'CANCELLED') {
                $status = 'Cancelled';
            }
            
            Transaction::create([
                'title' => $paymentDetails['title'],
                'reference' => $transactionReference,
                'amount' => $paymentDetails['amount'],
                'status' => $status,
                'payment_method' => 'Monnify',
                'created_at' => $paymentDetails['date'],
            ]);
            return redirect()->route('dashboard')->with('success', 'Payment successful.');
        }


    }

    public function showSuccessPage(Request $request)
    {
        $paymentDetails = session('paymentDetails') ?? session('transactionDetails');
        if (!$paymentDetails) {
            return redirect('/')->with('error', 'No payment details found.');
        }
        return view('payment.success', ['paymentDetails' => $paymentDetails]);
    }

    public function showFailedPage()
    {
        return view('payment.failed');
    }
}
