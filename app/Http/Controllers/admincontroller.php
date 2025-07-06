<?php

namespace App\Http\Controllers;

use App\Models\apply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class admincontroller extends Controller
{
      public function is_owner()
      {
        return response()->json([], 200);
      }
      public function is_creator()
      {
        return response()->json([], 200);
      }
      public function apply(Request $request)
      {
        $validator = Validator::make($request->all(), [
           'company' => ['required', 'string', 'max:255'],
           'address' => ['required', 'string', 'min:10', 'max:500'],
           'meli' => ['required', 'integer', 'digits:10']
        ], [
           'company.required' => 'وارد کردن نام کسب کار الزامی است',
           'company.string' => 'نام کسب کار باید از نوع رشته ای باشد',
           'company.max' => 'نام کسب کار حداکثر باید 255 حرف باشد',
           'address.required' => 'وارد کردن ادرس الزامی است',
           'address.string' => 'ادرس باید از نوع رشته ای باشد',
           'address.min' => 'ادرس حداقل باید 10 حرف باشد',
           'address.max' => 'ادرس حداکثر باید 500 حرف باشد',
           'meli.required' => 'وارد کردن کد ملی الزامی است',
           'meli.integer' => 'کد ملی باید از نوع عدد باشد',
           'meli.digits' => 'کد ملی باید 10 رقمی باشد'
        ]);

        if ($validator->fails()){
          return response()->json([
            'message' => $validator->errors()
          ], 422);
        }

        $payload = JWTAuth::parseToken()->getPayload();
        $user_id = $payload->get('id');

        apply::create([
          'user_id' => $user_id,
          'company' => $request->company,
          'address' => $request->address,
          'meli' => $request->meli
        ]);
      }
      public function get_applys()
      {
        $applys = apply::get();
        return response()->json([
         'data' => $applys
        ], 200);
      }
      public function fail_apply(Request $request)
      {
        $apply = apply::findOrFail($request->id);

        $apply->delete();
      }
      public function verify_apply(Request $request)
      {
        $apply = apply::findOrFail($request->id);
        $user = User::findOrFail($apply->user_id);
        $user->role = 2;
        $user->company = $apply->company;
        $user->company_address = $apply->address;
        $user->melli = $apply->meli;
        $user->save();
        $apply->delete();
        return response()->json([], 200);
      }
}
