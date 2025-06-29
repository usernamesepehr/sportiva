<?php

namespace App\Http\Controllers;

use App\Models\product;
use Illuminate\Http\Request;


class productcontroller extends Controller
{
    public function index(Request $request)
    {
        product::get()->all();
    }
}
