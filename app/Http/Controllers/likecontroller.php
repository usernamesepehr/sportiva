<?php

namespace App\Http\Controllers;

use App\Models\like;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Likes",
 *     description="مدیریت لایک‌های کاربران روی محصولات"
 * )
 */

class likecontroller extends Controller
{

   /**
     * @OA\Get(
     *     path="/likes/{user_id}",
     *     summary="لیست محصولات لایک شده توسط کاربر",
     *     description="دریافت محصولاتی که توسط یک کاربر خاص لایک شده‌اند.",
     *     tags={"Likes"},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         description="شناسه کاربر",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="لیست محصولات لایک شده",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="کاربر یافت نشد"
     *     )
     * )
     */
    public function userLikes($user_id)
    {
       $user = User::findOrFail($user_id);
       $products = $user->likes()->with('product')->get()->pluck('product');
       $products = $products->values();

       return response()->json([
        'products' => $products
       ], 200);
    }

    /**
     * @OA\Post(
     *     path="/like",
     *     summary="لایک یا آنلایک کردن یک محصول",
     *     description="اگر محصول قبلاً لایک شده باشد، آن را آنلایک می‌کند و بالعکس.",
     *     tags={"Likes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=123)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="لایک با موفقیت انجام شد"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="لایک قبلی حذف شد (آنلایک)"
     *     )
     * )
     */

    public function like(Request $request)
    {
       $payload = JWTAuth::parseToken()->getPayload();
       $userId = $payload->get('id');
       $like =  like::where('user_id', $userId)->where('product_id', $request->product_id)->first();

       if($like){
        $like->delete();

        return response()->json([], 200);
       }else{
        like::create([
            'user_id' => $userId,
            'product_id' => $request->product_id
         ]);

        return response()->json([], 201); 
       }
    }

    
}
