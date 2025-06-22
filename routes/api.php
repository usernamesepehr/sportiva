<?php

use App\Http\Controllers\authcontroller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [authcontroller::class, 'register'])->middleware('throttle:register');
Route::post('/login', [authcontroller::class, 'login'])->name('login')->middleware('throttle:register');
Route::post('/logout', [authcontroller::class, 'logout'])->middleware('auth:api');