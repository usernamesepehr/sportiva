<?php

namespace App\Http\Controllers;

use App\Models\cart;
use App\Models\product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class cartcontroller extends Controller
{
    public function cart_list()
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $user_id = $payload->get('id');
        $carts = cart::where('user_id', $user_id)->get();
        return response()->json([
            'carts' => $carts
        ], 200);
    }
    public function get_cart($id)
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $user_id = $payload->get('id');
        $cart = cart::where('id', $id)->where('user_id', $user_id)->with('user')->with('product')->get();
        return response()->json([
            'data' => $cart
        ], 200);

    }
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
    public function delete($id)
    {
        $cart = cart::findOrFail($id);
        $cart->delete();
    }
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
