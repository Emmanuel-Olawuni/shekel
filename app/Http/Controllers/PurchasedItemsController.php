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

    
}
