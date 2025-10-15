<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\DeveloperProfile;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json([
            'url' => $url,
        ]);
    }
    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName() ?? $googleUser->getNickname(),
                'oauth_id' => $googleUser->getId(),
                'oauth_provider' => 'google',
                'role' => 'developer'
            ],
        );
        DeveloperProfile::updateOrCreate(
            ['user_id'=>$user->id]
        );
        if ($user->profile_url == null) {
            $user->profile_url = $googleUser->getAvatar();
            $user->save();
        }
        $token = $user->createToken("KK's-google")->plainTextToken;
        return redirect()->to('http://localhost:5173/auth/oauth/callback?token=' . $token);
    }
}
