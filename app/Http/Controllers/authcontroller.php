<?php




namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Annotations as OA;
use Tymon\JWTAuth\Exceptions\JWTException;

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
            'profile' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
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
            'address.string' => 'ادرس وارد شده باید فقط شامل حروف باشد',
            'profile.required' => 'لطفاً یک تصویر پروفایل انتخاب کنید.',
            'profile.image' => 'فایل انتخاب‌شده باید یک تصویر باشد.',
            'profile.mimes' => 'فرمت تصویر باید یکی از موارد jpeg، jpg یا png باشد.',
            'profile.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.'        
        ]);


        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $profilePath = $request->profile->store('profiles', 'public');

        
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'address' => $request->address,
            'profile' => asset($profilePath)
        ]);


        $customclaims = [
           'id' => $user->id,
           'role' => $user->role
        ];

        $token = JWTAuth::claims($customclaims)->fromUser($user);

        

        Auth::login($user);

        return response()->json([
            'status' => true,
            'username' => $user->username,
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
           'username' => $user->username,
           'token' => $token,
           'type' => 'bearer'
        ], 200);
    }

    /**
 * @OA\Post(
 *     path="/api/refresh",
 *     summary="رفرش کردن توکن JWT",
 *     description="این متد توکن JWT فعلی را گرفته و یک توکن جدید صادر می‌کند.",
 *     operationId="refreshToken",
 *     tags={"Authentication"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="توکن جدید با موفقیت صادر شد",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
 *             @OA\Property(property="type", type="string", example="bearer")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="توکن معتبر نبود یا منقضی شده است"
 *     )
 * )
 */

    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json([
                'status' => true,
                'token' => $newToken,
                'type' => 'bearer'
             ], 200);            
        }catch (JWTException $e) {
            return response()->json([], 401);
        }
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

    /**
 * @OA\Post(
 *     path="/user/update",
 *     summary="ویرایش اطلاعات کاربر",
 *     description="این متد اطلاعات پروفایل کاربر را ویرایش می‌کند. نیاز به ارسال توکن JWT در هدر Authorization دارد.",
 *     tags={"User"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=false,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="username", type="string", minLength=4, maxLength=16, example="myusername"),
 *                 @OA\Property(property="email", type="string", format="email", example="example@email.com"),
 *                 @OA\Property(property="phone", type="string", minLength=10, maxLength=15, example="09123456789"),
 *                 @OA\Property(property="password", type="string", minLength=8, example="Password123"),
 *                 @OA\Property(property="address", type="string", example="تهران، خیابان آزادی"),
 *                 @OA\Property(property="profile", type="string", format="binary", description="تصویر پروفایل (jpeg, jpg, png, حداکثر 2MB)")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="اطلاعات کاربر با موفقیت ویرایش شد",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="اطلاعات با موفقیت بروزرسانی شد")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="خطا در اعتبارسنجی ورودی‌ها",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="errors", type="object",
 *                 @OA\Property(property="username", type="array", @OA\Items(type="string", example="نام کاربری حداقل باید 4 حرف باشد"))
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="عدم احراز هویت یا توکن نامعتبر"
 *     )
 * )
 */


    public function edit_info(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['sometimes', 'min:4', 'max:16'],
            'email' => ['sometimes', 'email'],
            'phone' => ['sometimes', 'min:10', 'max:15'],
            'password' => ['sometimes', 'min:8', 'regex:/^[a-zA-z0-9]+$/'],
            'address' => ['sometimes', 'string', 'min:5', 'max:255'],
            'profile' => ['sometimes', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ],[
            'username.min' => ' نام کاربری حداقل باید 4 حرف باشد ',
            'username.max' => 'نام کاربری حداکثر باید 16 کرکتر باشد',
            'email.email' => 'ایمیل وارد شده معتبر نمیباشد',
            'phone.min' => 'شماره تلفن حداقل باید10 کرکتر باشد',
            'phone.max' => 'شماره تلفن حداکثر 15 کرکتر باشد',
            'password.min' => 'رمز عبور حداقل باید 8 کرکتر باشد',
            'password.regex' => 'رمز عبور باید فقط شامل حروف و اعداد انگلیسی باشد',
            'address.string' => 'ادرس وارد شده باید فقط شامل حروف باشد',
            'profile.image' => 'فایل انتخاب‌شده باید یک تصویر باشد.',
            'profile.mimes' => 'فرمت تصویر باید یکی از موارد jpeg، jpg یا png باشد.',
            'profile.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.'        
        ]);


        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = JWTAuth::parseToken()->getPayload();
        $user_id = $payload->get('id');
        $user_role = $payload->get('role');
        $user = User::findOrFail($user_id);
        if ($request->profile){
            $path = '/profiles'. "/" . $user->profile;
            Storage::disk('public')->delete($path);
            $profilePath = $request->profile->store('profiles', 'public');
            $user->profile = $profilePath;
            $user->save();
        }

        $user->update($request->except('profile', 'email', 'id', 'role', 'created_at', 'updated_at'));    
    }
    
    public function get_info()
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $user_id = $payload->get('id');
        $user = User::where('id', $user_id)->first();
        $userData = collect($user)->except(['id', 'role', 'created_at', 'updated_at']);
        return response()->json($userData);
    }
}
