<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::where('user_id', Auth::id());

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->input('start_date'), $request->input('end_date')]);
        }

        if ($request->has('min_amount') && $request->has('max_amount')) {
            $query->whereBetween('amount', [$request->input('min_amount'), $request->input('max_amount')]);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        return response()->json($transactions);
    }
}
