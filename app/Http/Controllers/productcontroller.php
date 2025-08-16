<?php

namespace App\Http\Controllers;

use App\Models\category_product;
use App\Models\product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Tymon\JWTAuth\Facades\JWTAuth;

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
     *     path="/product",
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
        $products = product::where('confirmed', 1)->get();
        $response = [];
        foreach ($products as $product){
            $response[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'discount' => $product->discount,
                'finaleprice' => $product->finaleprice,
                'color' => $product->color,
                'description' => $product->description,
                'photo' => $product->photo,
                'quantity' => $product->quantity,
        ];
        
        }
        return response()->json([
         'products' =>  $response
        ], 200);
         
    }

    /**
     * @OA\Get(
     *     path="/product/{id}",
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
        $product = product::where('id', $id)->where('confirmed', 1)->firstOrFail();
        // $categorys = $product->category_product()->category()->get();
        $categorys = $product->category_products()->with('category')->get()->pluck('category');
        $categorys = $categorys->values();

        $likes = $product->likes()->get()->count();

        $comments = $product->comments()->with('user')->get()->map(function ($comment) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'user_name' => $comment->user->name ?? 'Unknown',
                'created_at' => $comment->created_at,
            ];
    
        });

        return response()->json([
            'name' => $product->name,
            'price' => $product->price,
            'discount' => $product->discount,
            'finaleprice' => $product->finaleprice,
            'color' => $product->color,
            'description' => $product->description,
            'photo' => $product->photo,
            'quantity' => $product->quantity,
            'categorys' => $categorys,
            'likes' => $likes,
            'comments' => $comments,
            'created_at' => $product->created_at
        ], 200);
    }

     /**
     * @OA\Get(
     *     path="/product/top-sales",
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
        path="/product/popular",
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
    /**
 * @OA\Post(
 *     path="/api/product/create",
 *     summary="Create a new product",
 *     description="Creates a product with image upload and category linking.",
 *     tags={"Product"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"name", "price", "discount", "finaleprice", "color", "photo", "description", "quantity", "categories"},
 *                 @OA\Property(property="name", type="string", maxLength=255),
 *                 @OA\Property(property="price", type="integer", example=100000),
 *                 @OA\Property(property="discount", type="integer", example=10),
 *                 @OA\Property(property="finaleprice", type="integer", example=90000),
 *                 @OA\Property(property="color", type="string", maxLength=255),
 *                 @OA\Property(property="photo", type="file", format="binary"),
 *                 @OA\Property(property="description", type="string", maxLength=500),
 *                 @OA\Property(property="quantity", type="integer", example=5),
 *                 @OA\Property(property="categories", type="array", @OA\Items(type="integer"))
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Product created successfully"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     )
 * )
 */

    public function create_product(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:255', 'string'],
            'price' => ['required', 'integer', 'min_digits:5'],
            'discount' => ['required', 'integer', 'max_digits:3'],
            'finaleprice' => ['required', 'integer', 'min_digits:5'],
            'color' => ['required', 'string', 'max:255'],
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'description' => ['required', 'string', 'max:500'],
            'quantity' => ['required', 'integer', 'min:1'],
            'categories' => ['required', 'array', 'exists:categories,id']
        ], [
            'name.required' => 'وارد کردن نام محصول الزامی است.',
            'name.max' => 'نام محصول نمی‌تواند بیشتر از ۲۵۵ حرف باشد.',
            'name.string' => 'نام محصول باید به صورت رشته باشد.',
            'price.required' => 'وارد کردن قیمت محصول الزامی است.',
            'price.integer' => 'قیمت محصول باید عدد صحیح باشد.',
            'price.min_digits' => 'قیمت محصول باید حداقل ۵ رقمی باشد.',
            'discount.required' => 'وارد کردن درصد تخفیف الزامی است.',
            'discount.integer' => 'درصد تخفیف باید عدد صحیح باشد.',
            'discount.max_digits' => 'حداکثر مقدار مجاز برای تخفیف 100 است.',
            'finaleprice.required' => 'وارد کردن قیمت نهایی الزامی است.',
            'finaleprice.integer' => 'قیمت نهایی باید عدد صحیح باشد.',
            'finaleprice.min_digits' => 'قیمت نهایی نمی‌تواند کمتر از عددی ۵ رقمی باشد.',
            'color.required' => 'وارد کردن رنگ محصول الزامی است.',
            'color.string' => 'رنگ محصول باید به صورت رشته باشد.',
            'color.max' => 'رنگ محصول نمی‌تواند بیشتر از ۲۵۵ حرف باشد.',
            'photo.required' => 'بارگذاری تصویر محصول الزامی است.',
            'photo.image' => 'فایل بارگذاری شده باید تصویر باشد.',
            'photo.mimes' => 'تصویر محصول باید با فرمت jpg یا jpeg یا png باشد.',
            'photo.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.',
            'description.required' => 'وارد کردن توضیحات محصول الزامی است.',
            'description.string' => 'توضیحات محصول باید به صورت رشته باشد.',
            'description.max' => 'توضیحات محصول نمی‌تواند بیشتر از ۵۰۰ حرف باشد.',
            'quantity.required' => 'وارد کردن موجودی محصول الزامی است.',
            'quantity.integer' => 'مقدار موجودی باید عدد صحیح باشد.',
            'quantity.min' => 'حداقل موجودی باید ۱ عدد باشد.',
            'categories.required' => 'فیلد دسته‌بندی الزامی است.',
            'categories.integer' => 'مقدار دسته‌بندی باید یک عدد صحیح باشد.',
            'categories.exists' => 'دسته‌بندی انتخاب‌شده معتبر نیست.'
        ]);

        if ($validator->fails()){
            return response()->json([
               'errors'=> $validator->errors()
            ], 422);
        }

        $payload = JWTAuth::parseToken()->getPayload();
        $user_id = $payload->get('id');
        $user_role = $payload->get('role');
        
        $photoPath = $request->photo->store('products', 'public');

        $product = product::create([
            'user_id' => $user_id,
            'name' => $request->name,
            'price' => $request->price,
            'discount' => $request->discount,
            'finaleprice' => $request->finaleprice,
            'color' => $request->color,
            'photo' => asset('storage/' . $photoPath),
            'description' => $request->description,
            'quantity' => $request->quantity
        ]);

        if ($user_role == 3){
            $product->confirmed = true;
            $product->save();
        }
        
        if (count($request->categories) > 1){
            foreach ($request->categories as $category){
                category_product::create([
                    'product_id' => $product->id,
                    'category_id' => $category
                ]);
            }
        }else {
           category_product::create([
               'product_id' => $product->id,
               'category_id' => $request->categories[0]
           ]);
        }

        return response()->json([], 201);
    }
    /**
 * @OA\Delete(
 *     path="/api/product/delete",
 *     summary="Delete a product",
 *     description="Deletes a product if authorized.",
 *     tags={"Product"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"id"},
 *             @OA\Property(property="id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found"
 *     )
 * )
 */

    public function delete_product(Request $request){
        $payload = JWTAuth::parseToken()->getPayload();
        $user_id = $payload->get('id');
        $user_role = $payload->get('role');
        $product = product::findOrFail($request->id);
        $path = 'products/' . $product->photo;
        if ($user_role == 3){
            $product->delete();
            Storage::disk('public')->delete($path);
        }elseif ($user_role == 2) {
            if ($product->user_id == $user_id){
               $product->delete();
               Storage::disk('public')->delete($path);
            }else {
                return response()->json([], 403);
            }
        }
    }
    /**
 * @OA\Post(
 *     path="/api/product-update",
 *     summary="Update a product",
 *     description="Updates the product details, including optional photo upload.",
 *     tags={"Product"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"id"},
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", maxLength=255),
 *                 @OA\Property(property="price", type="integer"),
 *                 @OA\Property(property="discount", type="integer"),
 *                 @OA\Property(property="finaleprice", type="integer"),
 *                 @OA\Property(property="color", type="string"),
 *                 @OA\Property(property="photo", type="file", format="binary"),
 *                 @OA\Property(property="description", type="string"),
 *                 @OA\Property(property="quantity", type="integer"),
 *                 @OA\Property(property="categories", type="integer")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product updated successfully"
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */

    public function update_product(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'max:255', 'string'],
            'price' => ['sometimes', 'integer', 'min_digits:5'],
            'discount' => ['sometimes', 'integer', 'max_digits:3'],
            'finaleprice' => ['sometimes', 'integer', 'min_digits:5'],
            'color' => ['sometimes', 'string', 'max:255'],
            'photo' => ['sometimes', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'description' => ['sometimes', 'string', 'max:500'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'categories' => ['sometimes', 'integer', 'exists:category,id']
        ], [
            'name.max' => 'نام محصول نمی‌تواند بیشتر از ۲۵۵ حرف باشد.',
            'name.string' => 'نام محصول باید به صورت رشته باشد.',
            'price.integer' => 'قیمت محصول باید عدد صحیح باشد.',
            'price.min_digits' => 'قیمت محصول باید حداقل ۵ رقمی باشد.',
            'discount.integer' => 'درصد تخفیف باید عدد صحیح باشد.',
            'discount.max_digits' => 'حداکثر مقدار مجاز برای تخفیف 100 است.',
            'finaleprice.integer' => 'قیمت نهایی باید عدد صحیح باشد.',
            'finaleprice.min_digits' => 'قیمت نهایی نمی‌تواند کمتر از عددی ۵ رقمی باشد.',
            'color.string' => 'رنگ محصول باید به صورت رشته باشد.',
            'color.max' => 'رنگ محصول نمی‌تواند بیشتر از ۲۵۵ حرف باشد.',
            'photo.image' => 'فایل بارگذاری شده باید تصویر باشد.',
            'photo.mimes' => 'تصویر محصول باید با فرمت jpg یا jpeg یا png باشد.',
            'photo.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.',
            'description.string' => 'توضیحات محصول باید به صورت رشته باشد.',
            'description.max' => 'توضیحات محصول نمی‌تواند بیشتر از ۵۰۰ حرف باشد.',
            'quantity.integer' => 'مقدار موجودی باید عدد صحیح باشد.',
            'quantity.min' => 'حداقل موجودی باید ۱ عدد باشد.',
            'categories.integer' => 'مقدار دسته‌بندی باید یک عدد صحیح باشد.',
            'categories.exists' => 'دسته‌بندی انتخاب‌شده معتبر نیست.',

        ]);
        if ($validator->fails()){
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $payload = JWTAuth::parseToken()->getPayload();
        $user_id = $payload->get('id');
        $user_role = $payload->get('role');
        $product = product::findOrFail($request->id);
        if ($user_role == 2 ){
            if ($product->user_id != $user_id){
                return response()->json([], 403);
            }
        }
        if ($request->photo){
           $path = '/products'. "/" . $product->photo;
           Storage::disk('public')->delete($path);
           $photoPath = $request->photo->store('products', 'public');
           $product->photo = asset('storage/' . $photoPath);
           $product->save();
        }
        $product->update($request->except('photo')); 
    }
    /**
 * @OA\Post(
 *     path="/api/confirm-product",
 *     summary="Confirm a product",
 *     description="Admin confirms a product so it becomes visible.",
 *     tags={"Product"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"id"},
 *             @OA\Property(property="id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product confirmed successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found"
 *     )
 * )
 */

    public function confirm_product(Request $request)
    {
       $product = product::findOrFail($request->id);
       $product->confirmed = true;
       $product->save();
    }
    /**
 * @OA\Get(
 *     path="/api/product/not-confirmed",
 *     summary="Get unconfirmed products",
 *     description="Returns a list of products that are not confirmed.",
 *     tags={"Product"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Unconfirmed products list",
 *         @OA\JsonContent(
 *             @OA\Property(property="products", type="array", @OA\Items(
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="price", type="integer"),
 *                 @OA\Property(property="discount", type="integer"),
 *                 @OA\Property(property="finaleprice", type="integer"),
 *                 @OA\Property(property="color", type="string"),
 *                 @OA\Property(property="description", type="string"),
 *                 @OA\Property(property="photo", type="string", format="url"),
 *                 @OA\Property(property="quantity", type="integer")
 *             ))
 *         )
 *     )
 * )
 */

    public function not_confirmed()
    {
        $products = product::where('confirmed', 0)->get();
        // dd($products);
        $response = [];
        foreach ($products as $product){
            $response[] = [
                'name' => $product->name,
                'price' => $product->price,
                'discount' => $product->discount,
                'finaleprice' => $product->finaleprice,
                'color' => $product->color,
                'description' => $product->description,
                'photo' => $product->photo,
                'quantity' => $product->quantity,
        ];
        
        }
        return response()->json([
         'products' =>  $response
        ], 200);
         
    }
    

}

