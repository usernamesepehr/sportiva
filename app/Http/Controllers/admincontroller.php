<?php

namespace App\Http\Controllers;

use App\Models\apply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Annotations as OA;


/**
 * @OA\Tag(
 *     name="Admin",
 *     description="Admin related endpoints"
 * )
 *
 * @OA\Schema(
 *     schema="Apply",
 *     type="object",
 *     required={"user_id", "company", "address", "meli"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=12),
 *     @OA\Property(property="company", type="string", example="My Company"),
 *     @OA\Property(property="address", type="string", example="1234 Some Street, City"),
 *     @OA\Property(property="meli", type="string", example="1234567890")
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     required={"id", "role"},
 *     @OA\Property(property="id", type="integer", example=12),
 *     @OA\Property(property="role", type="integer", example=2),
 *     @OA\Property(property="company", type="string", example="My Company"),
 *     @OA\Property(property="company_address", type="string", example="1234 Some Street, City"),
 *     @OA\Property(property="melli", type="string", example="1234567890")
 * )
 */

class admincontroller extends Controller
{
      /**
     * @OA\Get(
     *     path="/api/is_owner",
     *     tags={"Admin"},
     *     summary="Check if user is owner",
     *     description="Returns 200 OK if the user is owner",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User is owner")
     * )
     */
      public function is_owner()
      {
        return response()->json([], 200);
      }
        /**
     * @OA\Get(
     *     path="/api/is_creator",
     *     tags={"Admin"},
     *     summary="Check if user is creator",
     *     description="Returns 200 OK if the user is creator",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User is creator")
     * )
     */
      public function is_creator()
      {
        return response()->json([], 200);
      }

      /**
     * @OA\Post(
     *     path="/api/apply",
     *     tags={"Admin"},
     *     summary="Submit an application",
     *     description="Allows a user to submit an application",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"company","address","meli"},
     *             @OA\Property(property="company", type="string", maxLength=255, example="My Company"),
     *             @OA\Property(property="address", type="string", minLength=10, maxLength=500, example="1234 Some Street, City"),
     *             @OA\Property(property="meli", type="string", pattern="^\d{10}$", example="1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application submitted successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="object")
     *         )
     *     )
     * )
     */
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
      /**
     * @OA\Get(
     *     path="/api/apply/all",
     *     tags={"Admin"},
     *     summary="Get all applications",
     *     description="Returns list of all apply entries",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of applications",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Apply")
     *             )
     *         )
     *     )
     * )
     */
      public function get_applys()
      {
        $applys = apply::get();
        return response()->json([
         'data' => $applys
        ], 200);
      }
      /**
     * @OA\Delete(
     *     path="/api/apply/fail",
     *     tags={"Admin"},
     *     summary="Delete an application (fail)",
     *     description="Deletes an application by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Application deleted successfully"),
     *     @OA\Response(response=404, description="Application not found")
     * )
     */
      public function fail_apply(Request $request)
      {
        $apply = apply::findOrFail($request->id);

        $apply->delete();
      }
      /**
     * @OA\Post(
     *     path="/api/apply/verify",
     *     tags={"Admin"},
     *     summary="Verify and approve an application",
     *     description="Updates user role and details from application and deletes the application",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Application verified and user updated"),
     *     @OA\Response(response=404, description="Application or user not found")
     * )
     */
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
