<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yansongda\Pay\Pay;

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

    public function splitPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'paystack_amount' => 'required|numeric|min:0.01',
        ]);
        if (!$validator->fails()) {

            $wallet = Auth::user()->wallet;
            $walletAmount = $wallet->balance;
            $totalAmount = $request['amount'];
            $paystackAmount = $request['paystack_amount'];

            if ($walletAmount + $paystackAmount < $totalAmount) {
                return response()->json(['error' => 'Insufficient funds in wallet and Paystack combined'], 400);
            }

            $walletDebitAmount = $totalAmount - $paystackAmount;

            if ($walletDebitAmount > $walletAmount) {
                $walletDebitAmount = $walletAmount;
            }

            $wallet->balance -= $walletDebitAmount;
            $wallet->save();

            Transaction::create([
                'user_id' => Auth::id(),
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $walletDebitAmount,
            ]);
        }


        if ($paystackAmount > 0) {
            $order = [
                'out_trade_no' => uniqid(),
                'total_amount' => $paystackAmount,
                'subject' => 'Split Payment Top-up',
            ];

            $paystack = Pay::paystack([
                'public_key' => config('services.paystack.public'),
                'secret_key' => config('services.paystack.secret'),
            ]);

            try {
                $result = $paystack->pay($order);
                return  response()->json(['message' => 'Payment via Paystack Successful'], 201);

                // Handle successful payment via Paystack
            } catch (\Exception $e) {
                // Log::error('Payment failed: ' . $e->getMessage());
                return response()->json(['error' => 'Payment via Paystack failed'], 500);
            }
        }

        return response()->json(['message' => 'Split payment successful', 'wallet_balance' => $wallet->balance]);
    }

    public function payWithPaystack(Request $request)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $order = [
            'out_trade_no' => uniqid(),
            'total_amount' => $validatedData['amount'],
            'subject' => 'Wallet Top-up',
        ];

        $paystack = Pay::paystack([
            'public_key' => config('services.paystack.public'),
            'secret_key' => config('services.paystack.secret'),
        ]);

        try {
            $result = $paystack->pay($order);

            // Update wallet balance
            $wallet = Auth::user()->wallet;
            $wallet->balance += $validatedData['amount'];
            $wallet->save();

            return response()->json(['message' => 'Payment successful', 'balance' => $wallet->balance]);
        } catch (\Exception $e) {
            // Log::error('Payment failed: ' . $e->getMessage());
            return response()->json(['error' => 'Payment failed'], 500);
        }
    }
}
