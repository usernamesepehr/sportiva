<?php

use App\Http\Controllers\authcontroller;
use App\Http\Controllers\commentcontroller;
use App\Http\Controllers\likecontroller;
use App\Http\Controllers\productcontroller;
use App\Http\Controllers\searchcontroller;
use App\Models\product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::controller(authcontroller::class)->group(function() {
    Route::post('/register', [authcontroller::class, 'register'])->middleware('throttle:register');
    Route::post('/login', [authcontroller::class, 'login'])->name('login')->middleware('throttle:register');
    Route::post('/logout', [authcontroller::class, 'logout'])->middleware('auth:api');
});

Route::controller(productcontroller::class)->group(function() {
    Route::get('/product', [productcontroller::class, 'index']);
    Route::get('/product/{id}', [productcontroller::class, 'getProduct']);
    Route::get('/product/top-sales', [productcontroller::class, 'mostSales']);
    Route::get('/product/popular', [productcontroller::class, 'popular']);
});

Route::controller(likecontroller::class)->group(function() {
    Route::get('/like/{user_id}', [likecontroller::class, 'userLikes']);
    Route::post('/like', [likecontroller::class, 'like'])->middleware('auth:api');
});

Route::controller(commentcontroller::class)->group(function() {
    Route::get('/comments/{id}', [commentcontroller::class, 'getComments']);
    Route::middleware('auth:api')->group(function() {
        Route::post('/comments/check-owner', [commentcontroller::class, 'isCommentOwner']);
        Route::put('/comments', [commentcontroller::class, 'editComment']);
        Route::post('/comments', [commentcontroller::class, 'putComment']);
        Route::delete('/comments', [commentcontroller::class, 'deleteComment']);
    });
});

Route::get('/search', searchcontroller::class);
