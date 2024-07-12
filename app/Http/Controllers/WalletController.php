<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;

class WalletController extends Controller
{
    public function credit(Request $request)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $wallet = Auth::user()->wallet;
        $wallet->balance += $validatedData['amount'];
        $wallet->save();

        // Log transaction
        Transaction::create([
            'user_id' => Auth::id(),
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'amount' => $validatedData['amount'],
        ]);

        return response()->json(['message' => 'Wallet credited successfully', 'balance' => $wallet->balance]);
    }

    public function debit(Request $request)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $wallet = Auth::user()->wallet;

        if ($wallet->balance < $validatedData['amount']) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        $wallet->balance -= $validatedData['amount'];
        $wallet->save();

        // Log transaction
        Transaction::create([
            'user_id' => Auth::id(),
            'wallet_id' => $wallet->id,
            'type' => 'debit',
            'amount' => $validatedData['amount'],
        ]);

        return response()->json(['message' => 'Wallet debited successfully', 'balance' => $wallet->balance]);
    }

    public function splitPayment(Request $request)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'paystack_amount' => 'required|numeric|min:0.01',
        ]);

        $wallet = Auth::user()->wallet;
        $walletAmount = $wallet->balance;
        $totalAmount = $validatedData['amount'];
        $paystackAmount = $validatedData['paystack_amount'];

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
                // Handle successful payment via Paystack
            } catch (\Exception $e) {
                Log::error('Payment failed: ' . $e->getMessage());
                return response()->json(['error' => 'Payment via Paystack failed'], 500);
            }
        }

        return response()->json(['message' => 'Split payment successful', 'wallet_balance' => $wallet->balance]);
    } 
}
