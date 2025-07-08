<?php

namespace App\Http\Controllers;

use App\Models\product;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class searchcontroller extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/search",
     *     tags={"Search"},
     *     summary="Search confirmed products by name",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Name of the product to search",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of confirmed products",
     *         @OA\JsonContent(
     *             @OA\Property(property="products", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="discount", type="number"),
     *                 @OA\Property(property="finaleprice", type="number"),
     *                 @OA\Property(property="color", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="photo", type="string", format="url"),
     *                 @OA\Property(property="quantity", type="integer")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=404, description="No confirmed products found")
     * )
     */
    public function confirmed(Request $request)
    {
        $products = product::where('name', 'LIKE', '%'.$request->name.'%')
                           ->where('confirmed', 1)
                           ->get();

        if ($products->isEmpty()) {
            return response()->json([], 404);
        }                   

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
                'photo' => asset('storage/'. $product->photo),
                'quantity' => $product->quantity,
        ];
        
        }
        return response()->json([
         'products' =>  $response
        ], 200);              
    }
    /**
     * @OA\Get(
     *     path="/api/search/not-confirmed",
     *     tags={"Search"},
     *     summary="Search not confirmed products by name",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Name of the product to search",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of not confirmed products",
     *        
     *     ),
     *     @OA\Response(response=404, description="No not confirmed products found")
     * )
     */
    public function not_confirmed(Request $request)
    {
        $products = product::where('name', 'LIKE', '%'.$request->name.'%')
                           ->where('confirmed', 0)
                           ->get();

        if ($products->isEmpty()) {
            return response()->json([], 404);
        }                   

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
                'photo' => asset('storage/'. $product->photo),
                'quantity' => $product->quantity,
        ];
        
        }
        return response()->json([
         'products' =>  $response
        ], 200);              
    
    }
     /**
     * @OA\Get(
     *     path="/api/search/all",
     *     tags={"Search"},
     *     summary="Search all products by name",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Name of the product to search",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of all products",
     *         
     *     ),
     *     @OA\Response(response=404, description="No products found")
     * )
     */
    public function all_products(Request $request){
        $products = product::where('name', 'LIKE', '%'.$request->name.'%')
                           ->get();

        if ($products->isEmpty()) {
            return response()->json([], 404);
        }                   

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
                'photo' => asset('storage/'. $product->photo),
                'quantity' => $product->quantity,
        ];
        
        }
        return response()->json([
         'products' =>  $response
        ], 200);              
    
    }
    /**
     * @OA\Get(
     *     path="/api/search/users",
     *     tags={"Search"},
     *     summary="Search users by username",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Username to search",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             @OA\Property(property="users", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="role", type="string"),
     *                 @OA\Property(property="meli", type="string"),
     *                 @OA\Property(property="company", type="string"),
     *                 @OA\Property(property="company_address", type="string")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=404, description="No users found")
     * )
     */
    public function user(Request $request)
    {
        $users = User::where('username', 'LIKE', '%'.$request->name.'%')
                           ->get();

        if ($users->isEmpty()){
            return response()->json([], 404);
        }
        $roleMap = [
            1 => 'کاربر',
            2 => 'تولیدکننده',
            3 => 'صاحب سایت'
          ];
          $response = [];
          foreach ($users as $user){
            $response[] = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $roleMap[$user->role],
                'meli' => $user->melli,
                'company' => $user->company,
                'company_address' => $user->comany_address
            ];
          }
        return response()->json([
            'users' => $response
        ], 200);
    }
    /**
     * @OA\Get(
     *     path="/api/search/creators",
     *     tags={"Search"},
     *     summary="Search creators by username",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Username of the creator",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of creators",
     *         @OA\JsonContent(
     *             @OA\Property(property="creators", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="role", type="string"),
     *                 @OA\Property(property="meli", type="string"),
     *                 @OA\Property(property="company", type="string"),
     *                 @OA\Property(property="company_address", type="string")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=404, description="No creators found")
     * )
     */
    public function creators(Request $request)
    {
        $creators = User::where('username', 'LIKE', '%'.$request->name.'%')
                           ->where('role', 2)
                           ->get();

        if ($creators->isEmpty()){
            return response()->json([], 404);
        }
        $response = [];
        foreach ($creators as $creator){
          $response[] = [
              'id' => $creator->id,
              'email' => $creator->email,
              'phone' => $creator->phone,
              'role' => 'تولید کننده',
              'meli' => $creator->melli,
              'company' => $creator->company,
              'company_address' => $creator->comany_address
          ];
        }
        return response()->json([
            'creators' => $response
        ], 200);
    }
}
