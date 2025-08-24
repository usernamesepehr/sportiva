<?php

namespace App\Http\Controllers;

use App\Models\category;
use App\Models\category_product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class categorycontroller extends Controller
{
     /**
     * @OA\Post(
     *     path="/api/category/create",
     *     summary="Create a new category",
     *     tags={"Category"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="New Category")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function create_category(Request $request)
    {
      $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3', 'max:255', 'unique:categories,name']
      ], [
            'name.required' => 'وارد کردن نام الزامی است.',
            'name.string'   => 'نام باید یک رشته متنی باشد.',
            'name.min'      => 'نام باید حداقل ۳ کاراکتر داشته باشد.',
            'name.max'      => 'نام نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'name.unique' => 'کتگوری مورد نظر قبلا ثبت شده است'
      ]);

      if ($validator->fails()){
        return response()->json([
            'errors' => $validator->errors()
        ], 422);
      }

      category::create(['name' => $request->name]);
    }
    /**
     * @OA\Delete(
     *     path="/api/category/delete/{id}",
     *     summary="Delete a category",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function delete_category($id)
    {
        $category = category::findOrFail($id);
        $category->delete();
    }
     /**
     * @OA\Get(
     *     path="/api/category/list",
     *     summary="Get list of all categories",
     *     tags={"Category"},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="categories", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ))
     *         )
     *     )
     * )
     */
    public function cartegory_list()
    {
        $categories = category::get();
        $categories->transform(function($category) {
            $category->count = category_product::where('category_id', $category->id)->count();
            return  $category;
        });
        return response()->json([
            'categories' => $categories
        ], 200);
    }
    public function get_products($id)
    {
        $products = category_product::where('category_id', $id)->with('product')->get();
        return response()->json(['products' => $products]);
    }
}
