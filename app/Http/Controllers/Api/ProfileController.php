<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /*
|--------------------------------------------------------------------------
|   GET PROFILE DATA FOR BOTH CLIENT AND DEVELOPER
|--------------------------------------------------------------------------
*/
    public function getProfile(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'client') {
            $user->load('clientProfile');
            return response()->json([
                'user' => $user,
                'profile' => $user->clientProfile,
            ]);
        }

        if ($user->role === 'developer') {
            $user->load('developerProfile');
            return response()->json([
                'user' => $user,
                'profile' => $user->developerProfile,
            ]);
        }

        return response()->json(['message' => 'Role not supported'], 400);
    }
    /*
|--------------------------------------------------------------------------
|   EDIT PROFILE FOR ( DEVELOPER )
|--------------------------------------------------------------------------
*/
    /*  I should validate the email with Gmail using mailtrap  */
    /* but I don't have that much time :(:(  but I will add it in the future */
    public function editProfile(Request $request)
    {
        $this->validateProfileData($request);
        $user = $request->user();
        $user->update($request->only(['name', 'phone', 'bio', 'main_career','gender']));
        $user->developerProfile()->update($request->only(['skills', 'address', 'github_url', 'linkedin_url', 'portfolio_url']));
        return response()->json(['message' => 'Success']);
    }
    private function validateProfileData(Request $request)
    {
        return $request->validate([
            /*  for user table */
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'main_career' => 'nullable|string|max:255',
            /*  for developer table :) */
            'skills' => 'nullable|max:255',
            'address' => 'nullable|string|max:255',
            'github_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
        ]);
    }
    /*
|--------------------------------------------------------------------------
|   EDIT PROFILE IMAGE ( DEVELOPER )
|--------------------------------------------------------------------------
*/
    public function uploadProfileImage(Request $request)
    {
        $user = $request->user();
        $this->validateProfileImage($request);
        /*  when the user image is URL or imagePath . I delete if it is imagePath and replace new image filePath  */
        /*  We have to know user will provide a file not image URL */
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            /*  condition allow --> image FILE */
            if (!empty($user->profile_url) && !Str::startsWith($user->profile_url, ['http://', 'https://'])) {
                if (Storage::disk('public')->exists($user->profile_url)) {
                    Storage::disk('public')->delete($user->profile_url);
                }
                $filePath = $image->store('profile', 'public');
                $user->profile_url = $filePath;
            }
            /*  condition allow --> image URL */
            if (empty($user->profile_url) || Str::startsWith($user->profile_url, ['http://', 'https://'])) {
                $filePath = $image->store('profile', 'public');
                $user->profile_url = $filePath;
            }
            $user->save();
            return response()->json([
                'message' => 'Profile Image updated successfully.',
                'user' => $user,
            ]);
        }
        return response()->json([
            'message' => 'The image is required.',
            'user' => $user,
        ]);
    }
    private function validateProfileImage(Request $request)
    {
        return $request->validate(
            [
                'image' => 'required|image|mimes:jpg,jpeg,webp,png|max:2048',
            ],
            [
                'image.required' => 'The image is required.',
                'image.image' => 'The file must be an image.',
                'image.mimes' => 'The image must be a file of type: jpg, jpeg, webp, png.',
                'image.max' => 'The image must not be greater than 2MB.',
            ],
        );
    }

    /*
|--------------------------------------------------------------------------
|   EDIT PROFILE IMAGE ( DEVELOPER )
|--------------------------------------------------------------------------
*/
    public function deleteProfileImage(Request $request)
    {
        $user = $request->user();
        if (empty($user->profile_url)) {
            return response()->json([
                'message' => 'No profile image found.',
                'user' => $user,
            ]);
        }
        if (!Str::startsWith($user->profile_url, ['http://', 'https://']) && Storage::disk('public')->exists($user->profile_url)) {
            Storage::disk('public')->delete($user->profile_url);
        }
        $user->profile_url = null;
        $user->save();
        return response()->json([
            'message' => 'Profile Image deleted successfully.',
            'user' => $user,
        ]);
    }
    /*
|--------------------------------------------------------------------------
|   GET USER POSTS
|--------------------------------------------------------------------------
*/
    public function getUserPosts(Request $request)
    {
        $user = $request->user();
        $posts = $user
            ->posts()
            ->with(['user', 'file'])
            ->withCount('likedUsers')
            ->withExists([
                'likedUsers as liked' => function ($q) use ($request) {
                    $q->where('user_id', $request->user()->id);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'message' => 'Posts retrieved successfully.',
            'posts' => $posts,
        ]);
    }
    /*
|--------------------------------------------------------------------------
|   GET USER POSTS BY SEARCH QUERY
|--------------------------------------------------------------------------
*/
    public function searchPosts(Request $request)
    {
        $user = $request->user();
        $posts = $user
            ->posts()
            ->with(['user', 'file'])
            ->when($request->searchQuery, function ($query, $searchQuery) {
                return $query->whereAny(['title', 'content', 'code_lang'], 'LIKE', '%' . $searchQuery . '%');
            })
            ->orderBy('created_at', $request->input('sortBy', 'desc'))
            ->get();
        return response()->json([
            'message' => 'Posts retrieved successfully.',
            'posts' => $posts,
        ]);
    }
    public function getDeveloperProfile(Request $request,$id)
    {
        $sortBy = $request->query('sortBy', 'created_at,desc');
        $user = User::where('id', $id)
            ->with('developerProfile')
            ->withCount(['posts','followers','followings'])
            ->withExists([
                'followings as isFollower' => function ($q) use ($request) {
                    $q->where('following_id', $request->user()->id);
                },
                'followers as isFollowing' => function ($q) use ($request) {
                    $q->where('follower_id', $request->user()->id);
                },

            ])
            ->first();
        $posts = Post::where('user_id',$id)
            ->with(['user', 'file'])
            ->withCount('likedUsers')
            ->withExists([
                'likedUsers as liked' => function ($q) use ($request) {
                    $q->where('user_id', $request->user()->id);
                },
                'postFollowers as followed' => function ($q) use ($request) {
                    $q->where('follower_id', $request->user()->id);
                },
            ])
            ->when($sortBy, function ($query, $sortBy) {
                $sort = explode(',', $sortBy);
                $query->orderBy($sort[0], $sort[1]);
            })
            ->get();
        return response()->json([
            'user' => $user,
            'profile' => $user->developerProfile,
            'posts' => $posts,
        ]);
    }
}

/* whereJsonContains('column_name', ['value1', 'value2']) */
