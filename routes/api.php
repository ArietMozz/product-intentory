<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

Route::apiResource('products', ProductController::class)->middleware('auth:sanctum');

// Add and remove categories from products
Route::post('/products/{product}/categories', [ProductController::class, 'addCategories'])->middleware('auth:sanctum');
Route::delete('/products/{product}/categories/{category}', [ProductController::class, 'removeCategory'])->middleware('auth:sanctum');

Route::apiResource('categories', CategoryController::class)->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
