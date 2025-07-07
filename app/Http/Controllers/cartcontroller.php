<?php

namespace App\Http\Controllers;

use App\Models\cart;
use App\Models\product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Annotations as OA;



class cartcontroller extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/cart/list",
     *     summary="Get list of carts for authenticated user",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *        
     *     )
     * )
     */
    public function cart_list()
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $user_id = $payload->get('id');
        $carts = cart::where('user_id', $user_id)->get();
        return response()->json([
            'carts' => $carts
        ], 200);
    }
     /**
     * @OA\Get(
     *     path="/api/cart/{id}",
     *     summary="Get specific cart item by ID",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Cart ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart item found",
     *     ),
     *     @OA\Response(response=404, description="Cart not found")
     * )
     */
    public function get_cart($id)
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $user_id = $payload->get('id');
        $cart = cart::where('id', $id)->where('user_id', $user_id)->with('user')->with('product')->get();
        return response()->json([
            'data' => $cart
        ], 200);

    }
    /**
     * @OA\Post(
     *     path="/api/cart/create",
     *     summary="Add new cart item",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id","quantity"},
     *             @OA\Property(property="id", type="integer", description="Product ID"),
     *             @OA\Property(property="quantity", type="integer", description="Quantity of product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cart item created successfully",
     *     ),
     *     @OA\Response(response=422, description="Validation error or insufficient product quantity")
     * )
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
          'quantity' => ['required', 'integer']
        ], [
           'quantity.required' => 'وارد کردن مقدار الزامی است.',
           'quantity.integer' => 'مقدار باید یک عدد صحیح باشد.',
        ]);
        if ($validator->fails()){
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $payload = JWTAuth::parseToken()->getPayload();
        $user_id = $payload->get('id');
        $product = product::findOrFail($request->id);
        if ($product->quantity < $request->quantity){
            return response()->json([
                'message' => 'متاسفانه موجودی محصول کافی نمیباشد'
            ], 422);
        }
        cart::create([
            'user_id' => $user_id,
            'product_id' => $request->id,
            'price' => $product->finaleprice,
            'quantity' => $request->quantity
        ]);
    }
    /**
     * @OA\Delete(
     *     path="/api/cart/delete/{id}",
     *     summary="Delete a cart item",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Cart item ID to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Cart item deleted"),
     *     @OA\Response(response=404, description="Cart item not found")
     * )
     */
    public function delete($id)
    {
        $cart = cart::findOrFail($id);
        $cart->delete();
    }
    /**
     * @OA\Put(
     *     path="/api/cart/update",
     *     summary="Update quantity of a cart item",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id","quantity"},
     *             @OA\Property(property="id", type="integer", description="Cart ID"),
     *             @OA\Property(property="quantity", type="integer", description="New quantity")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart item updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error or insufficient product quantity")
     * )
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => ['required', 'integer']
          ], [
             'quantity.required' => 'وارد کردن مقدار الزامی است.',
             'quantity.integer' => 'مقدار باید یک عدد صحیح باشد.',
          ]);
          if ($validator->fails()){
              return response()->json([
                  'errors' => $validator->errors()
              ], 422);
          }
        $cart = cart::findOrFail($request->id);
        $product = product::findOrFail($cart->product_id);
        if ($request->quantity > $product->quantity){
            return response()->json([
                'message' => 'متاسفانه موجودی محصول کافی نمیباشد'
            ], 422);
        }
        $cart->update(['quantity' => $request->quantity]);
    }
}
