<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;


Route::post('/products/import', [ProductController::class, 'import']);

Route::get('/products', [ProductController::class, 'index']);