<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchasedItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PurchasedItemsController extends Controller
{
    public function index()
    {
        $purchasedItems = PurchasedItems::where('user_id', Auth::id())->with('product')->get();
        return response()->json($purchasedItems);
    }

    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        if (!$validatedData->fails()) {
            $product = Product::findOrFail($request->product_id);
            $totalPrice = $product->price * $request->quantity;

            $purchasedItem = PurchasedItems::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'price' => $totalPrice,
                'quantity' => $request->quantity,
            ]);

            return response()->json(['message' => 'Product purchased successfully', 'purchased_item' => $purchasedItem], 201);
        }
        return response()->json(['error' => 'Unable to purchased product'], 400);
    }
}
