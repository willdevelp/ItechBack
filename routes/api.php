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

    // Commandes
    Route::post('/commands', [CommandController::class, 'passerCommande']);
    Route::get('/mes-commands', [CommandController::class, 'mesCommandes']);


});

Route::post('/products', [ProductController::class, 'store']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/category/{categoryId}', [ProductController::class, 'showByCategory']);
Route::put('/products/{id}', [ProductController::class, 'update']);

    // Favorites
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::get('/favorites/user/{userId}', [FavoriteController::class, 'index']);
    // Route::get('/favorites/{id}', [FavoriteController::class, 'show']);
    Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy']);

Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories', [CategoryController::class, 'index']);






