<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::put('users/{id}', [AuthController::class, 'updateUser']);
    Route::delete('users/{id}', [AuthController::class, 'delete']);
    Route::get('/user', [AuthController::class, 'getUser']);

    //wallet

    Route::post('wallet/credit', [WalletController::class, 'credit']);
    Route::post('wallet/debit', [WalletController::class, 'debit']);
    Route::post('wallet/paystack', [WalletController::class, 'payWithPaystack']);
    Route::post('wallet/split-payment', [WalletController::class, 'splitPayment']);
    Route::get('transactions', [TransactionController::class, 'index']);
});
