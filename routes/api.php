<?php

use App\Http\Controllers\admincontroller;
use App\Http\Controllers\authcontroller;
use App\Http\Controllers\cartcontroller;
use App\Http\Controllers\categorycontroller;
use App\Http\Controllers\commentcontroller;
use App\Http\Controllers\likecontroller;
use App\Http\Controllers\productcontroller;
use App\Http\Controllers\searchcontroller;
use App\Http\Controllers\usercontroller;
use App\Models\product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::controller(authcontroller::class)->group(function() {
    Route::post('/register', [authcontroller::class, 'register'])->middleware('throttle:register');
    Route::post('/login', [authcontroller::class, 'login'])->name('login')->middleware('throttle:register');
    Route::post('/logout', [authcontroller::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [authcontroller::class, 'refresh']);
    Route::post('/user/update', [authcontroller::class, 'edit_info'])->middleware('auth:api');
    Route::get('/user/get_info', [authcontroller::class, 'get_info'])->middleware('auth:api');
});


Route::controller(productcontroller::class)->group(function() {
    Route::get('/product/not-confirmed', [productcontroller::class, 'not_confirmed'])->middleware('role:owner');
    Route::get('/product', [productcontroller::class, 'index']);
    Route::get('/product/{id}', [productcontroller::class, 'getProduct']);
    Route::get('/product/top-sales', [productcontroller::class, 'mostSales']);
    Route::get('/product/popular', [productcontroller::class, 'popular']);
    Route::post('/product/create', [productcontroller::class, 'create_product'])->middleware('role:creator|owner');
    Route::delete('/product/delete', [productcontroller::class, 'delete_product'])->middleware('role:creator|owner');
    Route::post('/product-update', [productcontroller::class, 'update_product'])->middleware('role:owner|creator');
    Route::post('/confirm-product', [productcontroller::class, 'confirm_product'])->middleware('role:owner');
});

Route::controller(likecontroller::class)->group(function() {
    Route::get('/likes', [likecontroller::class, 'userLikes'])->middleware('auth:api');
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

Route::controller(searchcontroller::class)->group(function() {
   Route::get('/search', [searchcontroller::class, 'confirmed']);
   Route::get('/search/not-confirmed', [searchcontroller::class, 'not_confirmed'])->middleware('role:owner');
   Route::get('/search/all', [searchcontroller::class, 'all_products'])->middleware('role:owner');
   Route::get('/search/users', [searchcontroller::class, 'user'])->middleware('role:owner');
   Route::get('/search/creators', [searchcontroller::class, 'creators'])->middleware('role:owner');
});



Route::controller(admincontroller::class)->group(function () {
    Route::get('/is-owner', [admincontroller::class, 'is_owner'])->middleware('role:owner');
    Route::get('/is-creator', [admincontroller::class, 'is_creator'])->middleware('role:craetor');
    Route::post('/apply', [admincontroller::class, 'apply'])->middleware('auth:api');
    Route::get('/apply/all', [admincontroller::class, 'get_applys'])->middleware('role:owner', 'throttle:apply');
    Route::delete('/apply/fail', [admincontroller::class, 'fail_apply'])->middleware('role:owner', 'throttle:apply');
    Route::post('/apply/verify', [admincontroller::class, 'verify_apply'])->middleware('role:owner', 'throttle:apply');
});

Route::controller(usercontroller::class)->group(function() {
    Route::delete('/user/delete/{id}', [usercontroller::class, 'delete_user'])->middleware('role:owner');
    Route::get('/users', [usercontroller::class, 'user_list']);
});


Route::controller(categorycontroller::class)->group(function() {
    Route::get('/category/list', [categorycontroller::class, 'cartegory_list'])->middleware('role:owner');
    Route::post('/category/create', [categorycontroller::class, 'create_category'])->middleware('role:owner');
    Route::delete('/category/delete/{id}', [categorycontroller::class, 'delete_category'])->middleware('role:owner');
});

Route::controller(cartcontroller::class)->group(function() {
    Route::post('/cart/create', [cartcontroller::class, 'create'])->middleware('auth:api', 'throttle:cart');
    Route::get('/cart/list', [cartcontroller::class, 'cart_list'])->middleware('auth:api', 'throttle:cart');
    Route::get('/cart/{id}', [cartcontroller::class, 'get_cart'])->middleware('auth:api', 'throttle:cart');
    Route::delete('/cart/delete/{id}', [cartcontroller::class, 'delete'])->middleware('auth:api', 'throttle:cart');
    Route::put('/cart/update', [cartcontroller::class, 'update'])->middleware('auth:api', 'throttle:cart');
});