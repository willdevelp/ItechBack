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
use App\Http\Controllers\AdminController;
use App\Http\Middleware\IsAdmin;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/send-verification-code', [AuthController::class, 'sendVerificationCode']);
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
Route::middleware('auth:sanctum')->group(function () {
    //user
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/update', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users', [AuthController::class, 'getAllUsers']);

    // Panier
    Route::get('/sum', [PanierController::class, 'countProduits']);
    Route::get('/panier', [PanierController::class, 'getPanier']);
    Route::post('/panier/ajouter', [PanierController::class, 'addProduit']);
    Route::delete('/panier/supprimer', [PanierController::class, 'removeProduit']);
    Route::put('/panier/mise-a-jour', [PanierController::class, 'updateProduit']);

    // Commandes
    Route::post('/commands', [CommandController::class, 'passerCommande']);
    Route::get('/mes-commands', [CommandController::class, 'mesCommandes']);

    // Favorites
    // Route::post('/favorites', [FavoriteController::class, 'store']);
    // Route::get('/favorites/user/{userId}', [FavoriteController::class, 'index']);
    // Route::get('/favorites/{id}', [FavoriteController::class, 'show']);
    // Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy']);
});

// Produits
Route::get('/products/top-promos', [ProductController::class, 'topPromoProducts']);
Route::get('/products/latest', [ProductController::class, 'latestProducts']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/{name}', [ProductController::class, 'show']);
Route::get('/products/category/{categoryName}', [ProductController::class, 'showByCategory']);
Route::get('/best-sellers', [CommandController::class, 'bestSellers']);

//Categories
Route::get('/categories', [CategoryController::class, 'index']);

//Publicities
Route::get('/publicities', [PublicityController::class, 'index']);

//Admin
Route::middleware([IsAdmin::class])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'index']);
    Route::get('/users/{id}', [AdminController::class, 'show']);
    Route::get('/admin/commands', [CommandController::class, 'index']);
    Route::put('/admin/commands/{id}', [CommandController::class, 'updateOrderStatus']);
    Route::delete('/admin/commands/{id}', [CommandController::class, 'destroy']);
    Route::get('/admin/products', [ProductController::class, 'index']);
    Route::put('/admin/products/{id}', [ProductController::class, 'update']);
    Route::delete('/admin/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/admin/products', [ProductController::class, 'store']);
    Route::get('/admin/categories', [CategoryController::class, 'index']);
    Route::post('/admin/categories', [CategoryController::class, 'store']);
    Route::delete('/admin/categories/{id}', [CategoryController::class, 'destroy']);
    Route::get('/admin/allpublicities', [PublicityController::class, 'index']);
    Route::post('/admin/publicities', [PublicityController::class, 'store']);
    Route::delete('/admin/publicities/{id}', [PublicityController::class, 'destroy']);
});
