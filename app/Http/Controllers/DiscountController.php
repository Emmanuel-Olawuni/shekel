<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:discounts',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if (!$validator->fails()) {

            $discount = Discount::create($request->all());
            return response()->json(['message' => 'Discount created successfully', 'discount' => $discount], 201);
        }

        return response()->json(['error' => 'Invalid Input. Unable to create Discount '], 401);
    }

    public function apply(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required|string|exists:discounts,code',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $discount = Discount::where('code', $validatedData['code'])->first();

        if ($discount->start_date > now() || $discount->end_date < now()) {
            return response()->json(['error' => 'Discount code is not valid at this time'], 400);
        }

        $discountedAmount = $validatedData['amount'];
        switch ($discount->type) {
            case 'percentage':
                $discountedAmount -= $validatedData['amount'] * ($discount->value / 100);
                break;
            case 'fixed':
                $discountedAmount -= $discount->value;
                break;

            default:
                $discountedAmount = max($discountedAmount, 0);
                break;
        }

        return response()->json(['original_amount' => $validatedData['amount'], 'discounted_amount' => $discountedAmount], 201);
    }
}
