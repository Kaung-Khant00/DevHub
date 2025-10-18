<?php

namespace App\Http\Controllers\Api;

use App\Models\File;
use App\Models\User;
use App\Models\Report;
use App\Models\PostComment;
use Illuminate\Http\Request;
use App\Models\DeveloperProfile;
use Illuminate\Support\Facades\DB;
use App\Models\DeveloperConnection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

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
    public function deleteUser(Request $request){
        if($request->user()->role != 'developer'){
            return response()->json([
                "message" => "You can't delete other user account."
            ],403);
        }
        if(trim($request->confirmText) !== 'Delete my account'){
            return response()->json([
                "message" => "Please type delete my account."
            ],422);
        }

    $user = $request->user();
    DB::beginTransaction();
    try {
        /*  I delete the profile first */
        DeveloperProfile::where('user_id', $user->id)->delete();
        // $user->clientProfile()->delete();

        /*  delete the post feed field (file attachment , images, comments ) */

        $user->posts()->with('file')->chunkById(100, function ($posts) use ($user) {
             /*  1. delete file attachments */
             foreach($posts as $post){

                /*  2. delete posts with images */
                if(isset($post->image)){
                    if( Storage::disk('public')->exists($post->image)){
                        Storage::disk('public')->delete($post->image);
                    }
                }
                /*  3. delete comments */
                PostComment::where('post_id', $post->id)->delete();
                /*  4. delete reports */
                Report::where('reportable_id', $post->id)->delete();
                $post->withTrashed()->forceDelete();

               if($post->file()->exists()){
                    if( Storage::disk('public')->exists($post->file->path)){
                        Storage::disk('public')->delete($post->file->path);
                    }
                    $file = File::find($post->file->id);
                    $file->delete();
                }
             }
         });
         /*  delete group creation request field */
        $user->groupCreationRequests()->where('status','pending')->delete();
        PostComment::where('user_id', $user->id)->delete();
        /* delete notification field */
        $user->notifications()->delete();
        /*  delete followed user and following user relationship field */
        DeveloperConnection::where('following_id', $user->id)->orWhere('follower_id', $user->id)->delete();

        $user->update([
            'name' => 'Deleted User',
            'email' => "Deleted User {$user->id}",
            'password' => null,
            'oauth_provider' => null,
            'profile_url' => null,
            'age' => null,
            'gender' => null,
            'main_career' => null,
            'phone' => null,
            'bio' => null
        ]);
        $user->save();
        $user->delete();
        DB::commit();
        return response()->json([
            'message' => 'User deleted successfully.'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to delete user.',
            'error' => $e
        ], 500);
    }
}
}
