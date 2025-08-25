<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class GitHubController extends Controller
{
    public function redirectToGitHub(){
        $url = Socialite::driver('github')->stateless()->redirect()->getTargetUrl();
        return response()->json([
            'url'=>$url
        ]);
    }
    public function handleGitHubCallback(){
        $githubUser = Socialite::driver('github')->stateless()->user();
        $user = User::updateOrCreate(
            ['email'=>$githubUser->getEmail()],
            [
                'name'=>$githubUser->getName() ?? $githubUser->getNickname(),
                'oauth_id'=>$githubUser->getId(),
                "oauth_provider" => "github"
            ]
            );
            $token = $user->createToken("KK's-github")->plainTextToken;
 return redirect()->to( 'http://localhost:5173/auth/oauth/callback?token=' . $token);
    }
}
