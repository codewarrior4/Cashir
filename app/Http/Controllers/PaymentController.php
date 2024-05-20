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
        Session::put('paymentDetails', $request->all());
        $amountInKobo = $request->amount * 100; // Convert amount to kobo

        $request->merge(['amount' => $amountInKobo]); // Update request amount

        $curl = curl_init();

        $email = $request->email;
        $amount = $amountInKobo;
        $callback_url = route('payment.callback'); // Your custom callback route

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'amount' => $amount,
                'email' => $email,
                'callback_url' => $callback_url,
            ]),
            CURLOPT_HTTPHEADER => [
                "authorization: Bearer " . config('services.paystack.secret_key'), // Paystack secret key
                "content-type: application/json",
                "cache-control: no-cache"
            ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
            return back()->with('error', 'Curl returned error: ' . $err);
        }

        $tranx = json_decode($response, true);
        
        if (!$tranx['status']) {
            return back()->with('error', 'API returned error: ' . $tranx['message']);
        }

        return redirect($tranx['data']['authorization_url']);
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
        $requestDetails = Session::get('paymentDetails');
        
        if ($paymentDetails['status'] == 'success') {
            Transaction::create([
                'title' => $requestDetails['title'],
                'reference' => $paymentDetails['data']['reference'],
                'amount' => $requestDetails['amount'],
                'status' => 'Completed',
                'payment_method' => 'Paystack',
                'created_at' => $requestDetails['date'],
            ]);
            return redirect()->route('dashboard')->with('success','Payment successful.');
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
            } elseif ($status === 'PENDING') {
                $status = 'Pending';
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
