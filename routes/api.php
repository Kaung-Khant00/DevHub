<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\Auth\GoogleController;


/*  Default route from laravel :) */
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});


/*
|--------------------------------------------------------------------------
|   -- MAIN ROUTE -- PROTECTED ROUTE -- MAIN API ROUTE --
|--------------------------------------------------------------------------
*/
Route::group(['middleware'=>'auth:sanctum'],function(){
Route::post('/logout', [AuthController::class, 'logout']);
/*
|--------------------------------------------------------------------------
|   PROFILE ROUTE -----------------------------------------------------------
|--------------------------------------------------------------------------
*/
Route::group(['prefix'=>'profile'],function(){
    Route::get('/', [ProfileController::class, 'getProfile']);
    Route::post('/developer/edit/{id}', [ProfileController::class, 'editProfile']);
    Route::post('/developer/image/edit', [ProfileController::class, 'uploadProfileImage']);
    Route::delete('/developer/image', [ProfileController::class, 'deleteProfileImage']);
});

/*
|--------------------------------------------------------------------------
|   POSTS ROUTE -----------------------------------------------------------
|--------------------------------------------------------------------------
*/
Route::group(['prefix'=>'posts'],function(){
    Route::get('/newest', [PostController::class, 'getNewestPosts']);
    Route::get('/{id}', [PostController::class, 'getPostById']);
    Route::post('/', [PostController::class, 'store']);
    Route::post('/edit/{id}', [PostController::class, 'update']);
    Route::delete('/{id}', [PostController::class, 'delete']);
});


Route::post('/set/role', [AuthController::class, 'setRole']);

});


/*
|--------------------------------------------------------------------------
|   Oauth REDIRECT LINK RETURN
|--------------------------------------------------------------------------
*/
Route::get('auth/github',[GitHubController::class,'redirectToGitHub']);
Route::get('auth/google',[GoogleController::class,'redirectToGoogle']);


/*
/--------------------------------------------------------------------------
/ Simple Login
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
/* ------------------------------------------------------------------------ */
