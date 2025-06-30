<?php




namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Laravel Authentication API",
 *     version="1.0.0",
 *     description="مستندات کامل مربوط به ثبت‌نام، ورود و خروج کاربران"
 * )
 *
 * @OA\Server(
 *     url="http://localhost/api",
 *     description="سرور لوکال توسعه"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

class authcontroller extends Controller
{

     /**
     * @OA\Post(
     *     path="/register",
     *     summary="ثبت‌نام کاربر جدید",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "email", "phone", "password", "address", "role"},
     *             @OA\Property(property="username", type="string", example="mohammad123"),
     *             @OA\Property(property="email", type="string", format="email", example="test@example.com"),
     *             @OA\Property(property="phone", type="string", example="09123456789"),
     *             @OA\Property(property="password", type="string", format="password", example="abc12345"),
     *             @OA\Property(property="address", type="string", example="Tehran, Iran"),
     *             @OA\Property(property="role", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="ثبت‌نام موفق با توکن"),
     *     @OA\Response(response=422, description="خطای اعتبارسنجی")
     * )
     */
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


    /**
     * @OA\Post(
     *     path="/login",
     *     summary="ورود کاربر",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="test@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="abc12345")
     *         )
     *     ),
     *     @OA\Response(response=200, description="ورود موفق با توکن"),
     *     @OA\Response(response=401, description="اطلاعات اشتباه"),
     *     @OA\Response(response=422, description="خطای اعتبارسنجی")
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
          'email' => ['required', 'email'],
          'password' => ['required', 'min:8', 'regex:/^[a-zA-Z0-9]+$/']
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

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="خروج از حساب",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="خروج موفق")
     * )
     */

    public function logout()
    {
          Auth::logout();
          return response()->json([
            'status' => 'success'
          ], 200);
         
    }
}
