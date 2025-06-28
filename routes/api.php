<?php

use App\Http\Controllers\authcontroller;
use App\Http\Controllers\productcontroller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::controller(authcontroller::class)->group(function() {
    Route::post('/register', [authcontroller::class, 'register'])->middleware('throttle:register');
    Route::post('/login', [authcontroller::class, 'login'])->name('login')->middleware('throttle:register');
    Route::post('/logout', [authcontroller::class, 'logout'])->middleware('auth:api');
});

Route::post('/product', [productcontroller::class, 'index'])->middleware('role:creator|owner');