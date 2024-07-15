<?php

namespace App\Http\Controllers;

use App\Models\PurchasedItems;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Paystack;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    public function credit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
        ]);

        if (!$validator->fails()) {
            $wallet = Auth::user()->wallet;
            $wallet->balance += $request['amount'];
            $wallet->save();
            Transaction::create([
                'user_id' => Auth::id(),
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => $request['amount'],
            ]);
            return response()->json(['message' => 'Wallet credited successfully', 'balance' => $wallet->balance], 201);
        };

        return response()->json([
            'error' => "Unable to credit wallet"
        ], 400);
    }

    public function debit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
        ]);

        if (!$validator->fails()) {

            $wallet = Auth::user()->wallet;

            if ($wallet->balance < $request['amount']) {
                return response()->json(['error' => 'Insufficient balance'], 400);
            }

            $wallet->balance -= $request['amount'];
            $wallet->save();

            Transaction::create([
                'user_id' => Auth::id(),
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $request['amount'],
            ]);
            return response()->json(['message' => 'Wallet debited successfully', 'balance' => $wallet->balance], 201);
        };

        return response()->json([
            'error' => "Unable to debit wallet"
        ], 400);
    }


    public function purchase(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'price' => 'required|numeric|min:0.01',
            'quantity' => 'required|numeric',
            'payment_method' => 'required|string|in:wallet,paystack,stripe',
            'payment_reference' => 'nullable|string',
        ]);

        $user = auth()->user();
        $amount = $validatedData['price'] * $validatedData['quantity'];

        try {
            if ($validatedData['payment_method'] == 'wallet') {
                $response = $this->handleWalletPayment($user, $amount);

                if ($response instanceof JsonResponse) {
                    return $response;
                }
            } elseif ($validatedData['payment_method'] == 'paystack') {
                $redirectUrl = $this->handlePaystackPayment($user, $amount);
                return response()->json([
                    'message' => 'Redirect to Paystack for payment',
                    'redirect_url' => $redirectUrl,
                ], 200);
            } elseif ($validatedData['payment_method'] == 'stripe') {
                $this->handleStripePayment($validatedData['payment_reference'], $amount);
                return response()->json([
                    'message' => 'Redirect to Stripe for payment',
                ], 200);
            }

            PurchasedItems::create([
                'user_id' => $user->id,
                'product_id' => $validatedData['product_id'],
                'price' => $validatedData['price'],
                'payment_method' => $validatedData['payment_method'],
                'quantity' => $validatedData['quantity'],
            ]);

            return response()->json([
                'message' => 'Purchase successful',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Purchase failed, please try again'], 500);
        }
    }


    private function handleWalletPayment($user, $amount)
    {
        $wallet = $user->wallet;

        if ($wallet->balance < $amount) {
            return response()->json([
                'error' => 'Insufficient balance'
            ]);
        }

        $wallet->balance -= $amount;
        try {
            // Save the updated wallet balance
            $wallet->save();
        } catch (\Exception $e) {
            // Handle database save error
            return response()->json(['error' => 'Failed to deduct from wallet balance'], 500);
        }
    }


    private function handlePaystackPayment($user, $amount)
    {
        $paystackData = [
            'amount' => $amount * 100, // Paystack requires amount in kobo
            'email' => $user->email,
            'currency' => 'NGN',
            'reference' => paystack()->genTranxRef(),
            'callback_url' => route('paystack.callback'), // Replace with your callback URL route name
        ];

        try {
            $redirectResponse = paystack()->getAuthorizationUrl($paystackData)->redirectNow();

            // Return the redirect URL directly as a string
            return $redirectResponse->getTargetUrl();
        } catch (\Exception $e) {
            throw new \Exception('Paystack payment failed: ' . $e->getMessage());
        }
    }


    private function handleStripePayment($paymentIntentId, $amount)
    {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            if ($paymentIntent->status != 'succeeded') {
                throw new \Exception('Stripe payment failed');
            }

            if ($paymentIntent->amount / 100 != $amount) {
                throw new \Exception('Stripe payment amount mismatch');
            }
        } catch (\Exception $e) {
            throw new \Exception('Stripe payment failed: ' . $e->getMessage());
        }
    }

    public function handleWebhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);

        if (!paystack()->validateWebhook($request->header('X-Callback-Signature'), $request->getContent())) {
            return response()->json(['error' => 'Invalid Paystack webhook signature'], 400);
        }

        switch ($payload['event']) {
            case 'charge.success':
                $this->handleSuccessfulPayment($payload);
                break;

            case 'charge.failed':
                $this->handleFailedPayment($payload);
                break;

            default:
                return response()->json([
                    'message' => 'Unable to verify payment'
                ]);
        }

        return response()->json(['message' => 'Webhook received'], 200);
    }


    private function handleSuccessfulPayment($payload)
    {


        $user = User::where('email', $payload['data']['customer']['email'])->first();

        PurchasedItems::create([
            'user_id' => $user->id,
            'product_id' => $payload['data']['metadata']['product_id'],
            'price' => $payload['data']['amount'] / 100,
            'payment_method' => 'paystack',
            'quantity' => $payload['data']['metadata']['quantity'],
        ]);
    }


    private function handleFailedPayment($payload)
    {
        return response()->json([
            'error' => 'Unable to verify payment'
        ]);
    }
}
