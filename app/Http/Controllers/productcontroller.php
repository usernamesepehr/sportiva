<?php

namespace App\Http\Controllers;

use App\Models\product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="مدیریت نمایش و داده‌های محصولات"
 * )
 */

class productcontroller extends Controller
{

    /**
     * @OA\Get(
     *     path="/products",
     *     summary="نمایش همه محصولات",
     *     description="دریافت لیست کامل تمام محصولات موجود",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="لیست محصولات با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $products = product::get();
        return response()->json([
          'data' => $products
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/products/{id}",
     *     summary="دریافت اطلاعات محصول به‌همراه لایک، نظر و دسته‌بندی‌ها",
     *     description="اطلاعات کامل یک محصول خاص به‌همراه لایک‌ها، نظرات و دسته‌بندی‌های مرتبط را باز می‌گرداند.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="شناسه محصول",
     *         @OA\Schema(type="integer", example=7)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="اطلاعات کامل محصول",
     *         @OA\JsonContent(
     *             @OA\Property(property="product", type="object"),
     *             @OA\Property(property="categorys", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="likes", type="integer", example=12),
     *             @OA\Property(property="comments", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=404, description="محصول یافت نشد")
     * )
     */
    public function getProduct($id)
    {
        $product = product::findOrFail($id);
        // $categorys = $product->category_product()->category()->get();
        $categorys = $product->category_products()->with('category')->get()->pluck('category');
        $categorys = $categorys->values();

        $likes = $product->likes()->get()->count();

        $comments = $product->comments()->get();

        return response()->json([
            'product' => $product,
            'categorys' => $categorys,
            'likes' => $likes,
            'comments' => $comments
        ], 200);
    }

     /**
     * @OA\Get(
     *     path="/products/top-sales",
     *     summary="محصولات پرفروش",
     *     description="نمایش محصولاتی که بیشترین تعداد سفارش را داشته‌اند",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="محصولات برتر از نظر فروش",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function mostSales()
    {
        $topProducts = DB::table('orders')
                        ->join('products', 'orders.product_id', '=', 'products.id')
                        ->select('products.*', DB::raw('COUNT(orders.id) as order_count'))
                        ->groupBy('products.id')
                        ->orderByDesc('order_count')
                        ->get();

        return response()->json([
             'data' => $topProducts
        ], 200);               
    }
    /*
    @OA\Get(
        path="/products/popular",
        summary="محبوب‌ترین محصولات",
        description="نمایش محصولاتی با بیشترین تعداد لایک",
        tags={"Products"},
        @OA\Response(
            response=200,
            description="محصولات محبوب بر اساس لایک کاربران",
            @OA\JsonContent(
        @OA\Property(property="data", type="array", @OA\Items(type="object"))
            )
        )
         )
        */
             
    public function popular()
    {
        $popularProducts = DB::table('likes')
                            ->join('products', 'likes.product_id', '=', 'products.id')
                            ->select('products.*', DB::raw('COUNT(likes.id) as like_count'))
                            ->groupBy('products.id')
                            ->orderByDesc('like_count')
                            ->get();

        return response()->json([
            'data' => $popularProducts
        ], 200);                    
    }
}
