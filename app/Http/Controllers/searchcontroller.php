<?php

namespace App\Http\Controllers;

use App\Models\product;
use App\Models\User;
use Illuminate\Http\Request;

class searchcontroller extends Controller
{
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
