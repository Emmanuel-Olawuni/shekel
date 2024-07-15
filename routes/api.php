<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchasedItemsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use OpenApi\Annotations as OA;


Route::post('register', [AuthController::class, 'register']);

Route::post('login', [AuthController::class, 'login']);


Route::middleware('auth:api')->group(function () {
    Route::post('user/update', [AuthController::class, 'updateUser']);
    Route::post('user/delete', [AuthController::class, 'delete']);
    Route::get('user', [AuthController::class, 'getUser']);

   
    Route::post('wallet/credit', [WalletController::class, 'credit']);

   
    Route::post('wallet/debit', [WalletController::class, 'debit']);
    Route::post('wallet/purchase ', [WalletController::class, 'purchase']);
    Route::post('paystack/callback', [WalletController::class, 'callback'])->name('paystack.callback');


    Route::get('transactions', [TransactionController::class, 'index']);

   
    Route::post('discounts', [DiscountController::class, 'create']);

   
    Route::post('discounts/apply', [DiscountController::class, 'apply']);

  
    Route::get('products', [ProductController::class, 'index']);

  
    Route::post('products', [ProductController::class, 'store']);

    Route::get('products/{id}', [ProductController::class, 'show']);

    
    Route::get('purchaseditems', [PurchasedItemsController::class, 'index']);

 
    Route::post('purchaseditems', [PurchasedItemsController::class, 'store']);

});
