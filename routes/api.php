<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

// Products
Route::apiResource('products', ProductController::class)->middleware('auth:sanctum');

// Categories
Route::apiResource('categories', CategoryController::class)->middleware('auth:sanctum');

// Add and remove categories from products
Route::post('/products/{product}/categories', [ProductController::class, 'addCategories'])->middleware('auth:sanctum');
Route::delete('/products/{product}/categories/{category}', [ProductController::class, 'removeCategory'])->middleware('auth:sanctum');

// Orders
Route::apiResource('orders', OrderController::class)->middleware('auth:sanctum');
Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

