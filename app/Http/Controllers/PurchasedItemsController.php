<?php

namespace App\Http\Controllers;

use App\Models\PurchasedItems;
use Illuminate\Support\Facades\Auth;

class PurchasedItemsController extends Controller
{
    public function index()
    {
        $purchasedItems = PurchasedItems::where('user_id', Auth::id())->with('product')->get();
        return response()->json($purchasedItems);
    }

}
