<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PanierController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PublicityController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/send-verification-code', [AuthController::class, 'sendVerificationCode']);
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/update', [AuthController::class, 'update']);

    // Commands
    Route::get('/panier', [PanierController::class, 'getPanier']);
    Route::post('/panier/ajouter', [PanierController::class, 'addProduit']);
    Route::delete('/panier/supprimer', [PanierController::class, 'removeProduit']);

    // Panier
    Route::get('/sum', [PanierController::class, 'countProduits']);

    // Commandes
    Route::post('/commands', [CommandController::class, 'passerCommande']);
    Route::get('/mes-commands', [CommandController::class, 'mesCommandes']);




});
Route::get('/products/top-promos', [ProductController::class, 'topPromoProducts']);
Route::get('/products/latest', [ProductController::class, 'latestProducts']);
Route::get('/commands', [CommandController::class, 'index']);
Route::put('/commands/{id}', [CommandController::class, 'updateOrderStatus']);
Route::delete('/commands/{id}', [CommandController::class, 'destroy']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/category/{categoryId}', [ProductController::class, 'showByCategory']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Favorites
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::get('/favorites/user/{userId}', [FavoriteController::class, 'index']);
    Route::get('/favorites/{id}', [FavoriteController::class, 'show']);
    Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy']);

Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories', [CategoryController::class, 'index']);

Route::post('/publicities', [PublicityController::class, 'store']);

Route::get('/publicities', [PublicityController::class, 'index']);





