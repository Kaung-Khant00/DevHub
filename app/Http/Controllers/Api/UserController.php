<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Group;
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
    public function joinGroup(Request $request,$id){
        $user = $request->user();
        $group = Group::find($id);
        if($group->user_id == $user->id){
            return response()->json([
                "message"=> "You can't join your own group.",
                'id' => $id
            ]);
        }
        $isJoined = $user->toggleJoinGroup($id);
        return response()->json([
            "message"=> "Toggle join group successfully.",
            'joined'=> $isJoined,
            'id' => $id
        ]);
    }
}
