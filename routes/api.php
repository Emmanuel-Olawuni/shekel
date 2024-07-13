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

    // Wallet
    /**
     * @OA\Post(
     *     path="/api/wallet/credit",
     *     summary="Credit wallet",
     *     tags={"Wallet"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Provide amount to credit",
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", example=100),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wallet credited successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Wallet credited successfully"),
     *             @OA\Property(property="wallet_balance", type="number", example=500),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     * )
     */
    Route::post('wallet/credit', [WalletController::class, 'credit']);

    /**
     * @OA\Post(
     *     path="/api/wallet/debit",
     *     summary="Debit wallet",
     *     tags={"Wallet"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Provide amount to debit",
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", example=50),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wallet debited successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Wallet debited successfully"),
     *             @OA\Property(property="wallet_balance", type="number", example=450),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     * )
     */
    Route::post('wallet/debit', [WalletController::class, 'debit']);

    // Transaction
    /**
     * @OA\Get(
     *     path="/api/transactions",
     *     summary="List transactions",
     *     tags={"Transaction"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="List of transactions",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Transaction"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     * )
     */
    Route::get('transactions', [TransactionController::class, 'index']);

    // Discount
    /**
     * @OA\Post(
     *     path="/api/discounts",
     *     summary="Create discount",
     *     tags={"Discount"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Provide discount details",
     *         @OA\JsonContent(
     *             required={"code","discount_amount"},
     *             @OA\Property(property="code", type="string", example="SUMMER20"),
     *             @OA\Property(property="discount_amount", type="number", example=20),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Discount created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Discount created successfully"),
     *             @OA\Property(property="discount_id", type="integer", example=1),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     * )
     */
    Route::post('discounts', [DiscountController::class, 'create']);

    /**
     * @OA\Post(
     *     path="/api/discounts/apply",
     *     summary="Apply discount",
     *     tags={"Discount"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Provide discount code and apply to item",
     *         @OA\JsonContent(
     *             required={"code","item_id"},
     *             @OA\Property(property="code", type="string", example="SUMMER20"),
     *             @OA\Property(property="item_id", type="integer", example=123),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Discount applied successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Discount applied successfully"),
     *             @OA\Property(property="discounted_price", type="number", example=80),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     * )
     */
    Route::post('discounts/apply', [DiscountController::class, 'apply']);

    // Products
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="List products",
     *     tags={"Product"},
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Product"),
     *         ),
     *     ),
     * )
     */
    Route::get('products', [ProductController::class, 'index']);

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create product",
     *     tags={"Product"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Provide product details",
     *         @OA\JsonContent(
     *             required={"name","price"},
     *             @OA\Property(property="name", type="string", example="Product Name"),
     *             @OA\Property(property="price", type="number", example=100),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product created successfully"),
     *             @OA\Property(property="product_id", type="integer", example=1),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     * )
     */
    Route::post('products', [ProductController::class, 'store']);

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get product details",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details",
     *         @OA\JsonContent(ref="#/components/schemas/Product"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *     ),
     * )
     */
    Route::get('products/{id}', [ProductController::class, 'show']);

    // Purchased Items
    /**
     * @OA\Get(
     *     path="/api/purchaseditems",
     *     summary="List purchased items",
     *     tags={"Purchased Items"},
     *     @OA\Response(
     *         response=200,
     *         description="List of purchased items",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/PurchasedItem"),
     *         ),
     *     ),
     * )
     */
    Route::get('purchaseditems', [PurchasedItemsController::class, 'index']);

    /**
     * @OA\Post(
     *     path="/api/purchaseditems",
     *     summary="Purchase item",
     *     tags={"Purchased Items"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Provide item details",
     *         @OA\JsonContent(
     *             required={"product_id","quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item purchased successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Item purchased successfully"),
     *             @OA\Property(property="purchase_id", type="integer", example=1),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     * )
     */
    Route::post('purchaseditems', [PurchasedItemsController::class, 'store']);

});
