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

        return response()->json(['error' => 'Invalid Input. Unable to create Discount '], 400);
    }

    public function apply(Request $request)
    {
        $validateData = Validator::make($request->all(), [
            'code' => 'required|string|exists:discounts,code',
            'amount' => 'required|numeric|min:0.01',
        ]);
        if (!$validateData->fails()) {

            $discount = Discount::where('code', $request->code)->first();

            if ($discount->start_date > now() || $discount->end_date < now()) {
                return response()->json(['error' => 'Discount code is not valid at this time'], 400);
            }

            $discountedAmount = $request->amount;
            switch ($discount->type) {
                case 'percentage':
                    $discountedAmount -= $request->amount * ($discount->value / 100);
                    break;
                case 'fixed':
                    $discountedAmount -= $discount->value;
                    break;

                default:
                    $discountedAmount = max($discountedAmount, 0);
                    break;
            }

            return response()->json(['original_amount' => $request->amount, 'discounted_amount' => $discountedAmount], 201);
        }
        return response()->json(['error'=> 'Unable to apply discount code']);
    }
}
