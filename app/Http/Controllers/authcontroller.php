<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class authcontroller extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'min:4', 'max:16'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'min:10', 'max:15'],
            'password' => ['required', 'min:8', 'regex:/^[a-zA-z0-9]+$/'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'role' => ['required', 'integer']
        ],[
            'username.required' => 'وارد کردن نام کاربری الزامی است',
            'username.min' => ' نام کاربری حداقل باید 4 حرف باشد ',
            'username.max' => 'نام کاربری حداکثر باید 16 کرکتر باشد',
            'email.required' => 'وارد کردن ایمیل الزامی میباشد',
            'email.email' => 'ایمیل وارد شده معتبر نمیباشد',
            'email.unique' => 'ایمیل مورد نظر قبلا وارد شده است',
            'phone.required' => 'وارد کردن  شماره تلفن الزامی است',
            'phone.min' => 'شماره تلفن حداقل باید10 کرکتر باشد',
            'phone.max' => 'شماره تلفن حداکثر 15 کرکتر باشد',
            'password.required' => 'وارد کردن رمز عبور الزامی است',
            'password.min' => 'رمز عبور حداقل باید 8 کرکتر باشد',
            'password.regex' => 'رمز عبور باید فقط شامل حروف و اعداد انگلیسی باشد',
            'address.required' => 'وارد ککردن ادرس اجباری میباشد',
            'address.string' => 'ادرس وارد شده باید فقط شامل حروف باشد'
        ]);


        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'address' => $request->address,
            'role' => $request->role,
        ]);


        $customclaims = [
           'id' => $user->id,
           'role' => $user->role
        ];

        $token = JWTAuth::claims($customclaims)->fromUser($user);

        

        Auth::login($user);

        return response()->json([
            'status' => true,
            'token' => $token,
            'type' => 'bearer'
        ], 200);

    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
          'email' => ['required', 'email'],
          'password' => ['required', 'min:8', 'regex:/^[a-zA-z0-9]+$/']
        ], [
           'email.required' => 'وارد کزدن ایمیل الزامی میباشد',
           'email.email' => 'ایمیل وارد شده نامعتبر است',
           'password.required' => 'وارد کردن رمز عبور الزامی میباشد',
           'password.min' => 'رمز عبوز حداقل باید 8 کاراکتر باشد',
           'password.regex' => 'رمز عبور باید فقط شامل حروف و اعداد انگلیسی باشد'
        ]);

        if ($validator->fails())
        {
            return response()->json([
               'status' => false,
               'error' => $validator->errors()
            ], 422);
        }

        if (!auth()->attempt($validator->validated()))
        {
              return response()->json([
                 'status' => false,
              ], 401);
        }

        $user = auth()->user();

        $customClaims = [
            'id' => $user->id,
            'role' => $user->role
        ];

        $token = JWTAuth::claims($customClaims)->fromUser($user);
        // $payload = JWTAuth::setToken($token)->getPayload();

        return response()->json([
           'status' => true,
           'token' => $token,
           'type' => 'bearer'
        ], 200);
    }

    public function logout()
    {
          Auth::logout();
          return response()->json([
            'status' => 'success'
          ], 200);
         
    }
}
