<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;


/**
 * @OA\Tag(
 *     name="User Management",
 *     description="APIs for managing users"
 * )
 */
class usercontroller extends Controller
{
    /**
     * @OA\Delete(
     *     path="/api/user/{id}",
     *     tags={"User Management"},
     *     summary="Delete a user by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function delete_user($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    }
    /**
     * @OA\Get(
     *     path="/api/user/list",
     *     tags={"User Management"},
     *     summary="List all users",
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
     *     @OA\Response(
     *         response=404,
     *         description="No users found"
     *     )
     * )
     */
    public function user_list()
    {
        $users = User::get();
        if (!$users){
            return response()->json([], 404);
        }
        $roleMap = [
            1 => 'کاربر',
            2 => 'تولید کننده',
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
                'company_address' => $user->comany_address,
                'created_at' => $user->created_at
             ];

        return response()->json([
            'users' => $response
        ]);    
        }
    }
}
