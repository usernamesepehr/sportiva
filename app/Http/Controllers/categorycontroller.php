<?php

namespace App\Http\Controllers;

use App\Models\category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class categorycontroller extends Controller
{
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
    public function delete_category($id)
    {
        $category = category::findOrFail($id);
        $category->delete();
    }
    public function cartegory_list()
    {
        $categories = category::get();
        return response()->json([
            'categories' => $categories
        ], 200);
    }
}
