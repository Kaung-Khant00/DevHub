<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function followUser(Request $request,$id){
        $user = User::find($request->user()->id);
        $followed = $user->toggleFollowingUser($id);
        return response()->json([
            "message"=> "Toggle following successfully.",
            "user" => $user,
            'id' => $id,
            'followed' => $followed
        ]);
    }
}
