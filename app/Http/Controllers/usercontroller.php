<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class usercontroller extends Controller
{
    public function delete_user($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    }
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
                'company_address' => $user->comany_address
             ];

        return response()->json([
            'users' => $response
        ]);    
        }
    }
}
