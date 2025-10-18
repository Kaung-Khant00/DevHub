<?php

namespace App\Http\Controllers\Api;

use App\Models\DeveloperConnection;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ClientProfile;
use App\Models\DeveloperProfile;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
            $token = $user->createToken(time())->plainTextToken;
            return response()->json([
                'message' => 'User registered successfully.',
                'user' => $user,
                'token' => $token,
            ], 201);
    }
    private function validateRegistration(Request $request)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed|max:40',
            'password_confirmation' => 'required|string|min:8|max:40',
            'role' => 'required|string|in:developer,client',
        ],[
            'role.in' => 'The role must be either developer or client.',
            'password_confirmation.same' => 'The confirm password must match the password.',
            'password.min' => 'The password must be at least 8 characters.',
            'password_confirmation.min' => 'The confirm password must be at least 8 characters.',
            'email.unique' => 'The email has already been taken.',
        ]);
    }
    private function getRegistrationData(Request $request){
        return [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password_confirmation'),
            'role' => $request->input('role'),
        ];
    }
/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/
    public function login(Request $request)
    {
        logger($request->all());
        $this->validateLogin($request);
        $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'Invalid credentials.'], 401);
    }
    if(!Hash::check($request->password, $user->password)){
        return response()->json(['message' => 'Invalid credentials.'], 401);
    }

    $token = $user->createToken(time())->plainTextToken;
    return response()->json([
        'message' => 'Login successful',
        'role' => $user->role,
        'token' => $token,
        'user' => $user
    ]);
    }
    private function validateLogin(Request $request)
    {
        return $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|max:40',
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

        /*
|--------------------------------------------------------------------------
| USER ROLE SETTING AFTER OAUTH
|--------------------------------------------------------------------------
*/
    public function setRole(Request $request)
{
    $request->validate([
        'role' => 'required|string|in:developer,client',
    ]);

    $user = $request->user();
    if($user->role){
        return response()->json([
            'message' => 'Role is already set and cannot be changed.',
            'user' => $user
        ], 400);
    }
    if($request->input('role') === "developer"){
        $user->developerProfile()->create();
    }
    if($request->input('role') === "client"){
        $user->clientProfile()->create();
    }
    $user->role = $request->input('role');
    $user->save();

    return response()->json([
        'message' => 'Role updated successfully.',
        'user' => $user
    ]);
}
}
