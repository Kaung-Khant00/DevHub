<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    /*
|--------------------------------------------------------------------------
|   GET PROFILE DATA FOR BOTH CLIENT AND DEVELOPER
|--------------------------------------------------------------------------
*/
    public function getProfile(Request $request){
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
    public function editProfile(Request $request){
        $this->validateProfileData($request);
        $user = $request->user();
        $user->update($request->only(['name', 'email', 'phone']));
        $user->developerProfile()->update($request->only(['skills', 'address', 'github_url', 'linkedin_url', 'portfolio_url']));
        return response()->json(['message'=>"Success"]);
    }
    private function validateProfileData(Request $request){
        return $request->validate([
            /*  for user table */
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:255',
            /*  for developer table :) */
            'skills' => 'nullable|max:255',
            'address' => 'nullable|string|max:255',
            'github_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
        ],[
            'email.unique' => 'The email has already been taken.',
        ]);
    }

}
