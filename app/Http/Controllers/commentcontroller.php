<?php

namespace App\Http\Controllers;

use App\Models\comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Comments",
 *     description="مدیریت نظرات کاربران بر روی محصولات"
 * )
 */

class commentcontroller extends Controller
{
     /**
     * @OA\Get(
     *     path="/comments/{product_id}",
     *     summary="دریافت لیست نظرات محصول",
     *     description="کامنت‌های مربوط به یک محصول خاص را دریافت می‌کند.",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         description="شناسه محصول",
     *         @OA\Schema(type="integer", example=42)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="لیست نظرات",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     )
     * )
     */

    public function getComments($id)
    {
        $comments = comment::where('product_id', $id)->get();

        return response()->json([
            'data' => $comments
        ], 200);
    }
    /**
     * @OA\Post(
     *     path="/comments/check-owner",
     *     summary="بررسی مالکیت کامنت",
     *     description="تعیین می‌کند که آیا کامنت متعلق به کاربر احراز هویت‌شده است یا نه.",
     *     tags={"Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(response=200, description="کاربر صاحب کامنت است"),
     *     @OA\Response(response=403, description="عدم دسترسی")
     * )
     */
    public function isCommentOwner(Request $request)
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $user_id = $payload->get('id');

        $comment = comment::find($request->id);
        if ($comment->user_id == $user_id){
            return response()->json([], 200);
        }else{
            return response()->json([], 403);
        }
    }
    /**
     * @OA\Post(
     *     path="/comments",
     *     summary="ثبت کامنت جدید",
     *     description="ارسال نظر جدید برای یک محصول توسط کاربر لاگین‌شده.",
     *     tags={"Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "content"},
     *             @OA\Property(property="product_id", type="integer", example=12),
     *             @OA\Property(property="content", type="string", example="عالی بود!")
     *         )
     *     ),
     *     @OA\Response(response=201, description="نظر با موفقیت ثبت شد"),
     *     @OA\Response(response=422, description="خطای اعتبارسنجی")
     * )
     */
    public function putComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'content' => ['required', 'string'] 
        ], [
           'content.required' => 'کامنت حتما باید دارای محتوا باشد',
           'content.string' => 'کامنت باید دارای محتوا رشته ای باشد'
        ]);

        if ($validator->fails()){
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }

        $payload = JWTAuth::parseToken()->getPayload();
        $userId = $payload->get('id');

        comment::create([
            'user_id' => $userId,
            'product_id' => $request->product_id,
            'content' => $request->content
        ]);

        return response()->json([], 201);
    }
    /**
 * @OA\Delete(
 *     path="/comments",
 *     summary="حذف نظر",
 *     description="کامنت مشخص‌شده را حذف می‌کند.",
 *     tags={"Comments"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=10)
 *         )
 *     ),
 *     @OA\Response(response=200, description="کامنت با موفقیت حذف شد"),
 *     @OA\Response(response=404, description="کامنت پیدا نشد")
 * )
 */
    public function deleteComment(Request $request)
    {
        comment::findOrFail($request->id)->delete();
        return response()->json([], 200);
    }
    /**
 * @OA\Put(
 *     path="/comments",
 *     summary="ویرایش نظر",
 *     description="ویرایش محتوای یک کامنت ثبت‌شده توسط همان کاربر.",
 *     tags={"Comments"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"id", "content"},
 *             @OA\Property(property="id", type="integer", example=10),
 *             @OA\Property(property="content", type="string", example="این محصول خوب بود ولی بسته‌بندی ضعیف بود.")
 *         )
 *     ),
 *     @OA\Response(response=200, description="کامنت با موفقیت ویرایش شد"),
 *     @OA\Response(response=422, description="خطای اعتبارسنجی")
 * )
 */
    public function editComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => ['required', 'string'] 
         ], [
            'content.required' => 'کامنت حتما باید دارای محتوا باشد',
            'content.string' => 'کامنت باید دارای محتوا رشته ای باشد'
         ]);
         
         $payload = JWTAuth::parseToken()->getPayload();
         $userId = $payload->get('id');

         $comment = comment::findOrFail($request->id);

         $comment->content = $request->content;
         $comment->save();

    }
}
