<?php

namespace App\Http\Controllers;

use App\Models\product;
use Illuminate\Http\Request;

class searchcontroller extends Controller
{
    public function __invoke(Request $request)
    {
        $products = product::where('name', 'LIKE', '%'.$request->name.'%')
                           ->get();

        if ($products->isEmpty()) {
            return response()->json(404);
        }                   

        return response()->json([
            'data' => $products
        ], 200);                   
    }
}
