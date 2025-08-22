<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ClientProfile;
use App\Models\DeveloperProfile;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /*
|--------------------------------------------------------------------------
| REGISTRATION
|--------------------------------------------------------------------------
*/
    public function register(Request $request)
    {
        $this->validateRegistration($request);
        $userData = $this->getRegistrationData($request);
        $user = User::create($userData);
        if($request->role == 'developer'){
            $data = DeveloperProfile::create([
                'user_id' => $user->id,
            ]);
        }
        if($request->role == 'client'){
            $data = ClientProfile::create([
                'user_id' => $user->id,
            ]);
        }
/*         if($request->role == '' || $request->role == null){
            return response()->json([
                'message' => 'Role is required.'
            ], 400);
        }*/
            return response()->json([
                'message' => 'User registered successfully.',
                'user' => $user,
            ], 201);
    }
    private function validateRegistration(Request $request)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ]);
    }
    private function getRegistrationData(Request $request){
        return [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ];
    }

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

    public function login(Request $request)
    {
        $this->validateLogin($request);
        $credentials = $request->only('email', 'password');
        logger($credentials);
    if (!auth()->attempt($credentials)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }
    $user = auth()->user();
    $token = $user->createToken(time())->plainTextToken;
    return response()->json([
        'message' => 'Login successful',
        'access_token' => $token,
        'token_type' => 'Bearer',
        'user' => $user
    ]);
    }
    private function validateLogin(Request $request)
    {
        return $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);
    }

    /*
|--------------------------------------------------------------------------
| LOG OUT
|--------------------------------------------------------------------------
*/
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout successful']);
    }
}
