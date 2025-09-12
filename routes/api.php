<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Api\User\NotificationController;
use App\Http\Controllers\Api\User\GroupCreationRequestController;
use App\Http\Controllers\Api\Admin\AdminGroupCreationRequestController;

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
Route::group(['middleware' => 'auth:sanctum'], function () {
    /*
|----------------------------------------------------------------------
|:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
|   ADMIN ROUTE :::::::::::::::::::::::::::::::::::::::::::::
|:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
|----------------------------------------------------------------------
*/
    Route::group(['prefix' => 'admin', 'middleware' => 'role:ADMIN,SUPER_ADMIN'], function () {
        Route::get('/', [AdminController::class, 'getAdminUser']);

        Route::group(['prefix' => 'group_requests'], function () {
            Route::get('/', [AdminGroupCreationRequestController::class, 'getGroupRequests']);
            Route::get('/all', [AdminGroupCreationRequestController::class, 'getAllGroupRequests']);
            Route::get('/{id}/approve', [AdminGroupCreationRequestController::class, 'approveGroupRequest']);
            Route::get('/{id}/reject', [AdminGroupCreationRequestController::class, 'rejectGroupRequest']);
        });
    });

    Route::group(['prefix' => 'super_admin', 'middleware' => 'role:SUPER_ADMIN'],function (){
        Route::group(['prefix'=>'users'],function(){
            Route::get('/', [\App\Http\Controllers\Api\SuperAdmin\UserController::class,'getUsers']);
        });
    });

    /*
|--------------------------------------------------------------------------
|   PROFILE ROUTE -----------------------------------------------------------
|--------------------------------------------------------------------------
*/
    Route::group(['prefix' => 'profile'], function () {
        /*   --------- GET REQUESTS --------- */
        /*  get the user data */
        Route::get('/', [ProfileController::class, 'getProfile']);
        Route::get('/posts', [ProfileController::class, 'getUserPosts']);
        /*  get the OTHER use data */
        Route::get('/developer/{id}', [ProfileController::class, 'getDeveloperProfile']);

        /*   --------- POST REQUESTS --------- */
        Route::post('/developer/edit/{id}', [ProfileController::class, 'editProfile']);
        Route::post('/developer/image/edit', [ProfileController::class, 'uploadProfileImage']);
        Route::post('/posts/search', [ProfileController::class, 'searchPosts']);
        Route::delete('/developer/image', [ProfileController::class, 'deleteProfileImage']);
    });

    /*
|--------------------------------------------------------------------------
|   POSTS ROUTE -----------------------------------------------------------
|--------------------------------------------------------------------------
*/
    Route::group(['prefix' => 'posts'], function () {
        /*   --------- GET REQUESTS --------- */
        Route::get('/', [PostController::class, 'getPosts']);
        Route::get('/{id}', [PostController::class, 'getPostById']);
        Route::get('/{id}/detail', [PostController::class, 'getDetailPostById']);
        Route::get('/{id}/comments', [PostController::class, 'getComments']);

        /*   --------- POST REQUESTS --------- */
        Route::post('/', [PostController::class, 'store']);
        Route::post('/like', [PostController::class, 'likePost']);
        Route::post('/comment', [PostController::class, 'commentPost']);
        Route::post('/download', [PostController::class, 'download']);

        /*   --------- PUT/PATCH REQUESTS --------- */
        Route::match(['put', 'patch'], '/{id}', [PostController::class, 'updatePost']);
        Route::match(['put', 'patch'], '/{id}/comment', [PostController::class, 'updateComment']);

        /*   --------- DELETE REQUESTS --------- */
        Route::delete('/{id}', [PostController::class, 'deletePost']);
        Route::delete('/{id}/comment', [PostController::class, 'deleteComment']);
    });

/*
|--------------------------------------------------------------------------
|   GROUP ROUTE -----------------------------------------------------------
|--------------------------------------------------------------------------
*/
    Route::group(['prefix' => 'groups'], function () {
        Route::get('/{id}/join', [UserController::class, 'joinGroup']);
        Route::post('/create', [GroupController::class, 'createGroup']);
    });

    Route::group(['prefix' => 'users'], function () {
        /*   --------- GET REQUESTS --------- */
        Route::get('/{id}/follow', [UserController::class, 'followUser']);
    });
Route::group(['prefix'=>"group_requests"],function(){
    Route::get('/',[GroupCreationRequestController::class,'getGroupCreationRequests']);
    Route::get('/{id}',[GroupCreationRequestController::class,'getGroupCreationRequestsById']);
    Route::delete('/{id}',[GroupCreationRequestController::class,'deleteGroupCreationRequestsById']);
});

/*
|--------------------------------------------------------------------------
| NOTIFICATION ROUTE -----------------------------------------------------------
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'notifications'],function(){
    Route::get('/',[NotificationController::class,'getNotifications']);
    Route::get('/{id}',[NotificationController::class,'getNotificationById']);

    Route::match(['put', 'patch'],'/{id}/read',[NotificationController::class,'updateNotificationReadStatus'])->whereNumber('id');
    Route::match(['put', 'patch'],'/all/read',[NotificationController::class,'updateNotificationAllReadStatus']);

    Route::delete('/{id}',[NotificationController::class,'deleteNotification'])->whereNumber('id');
    Route::delete('/all/read',[NotificationController::class,'deleteAllReadNotification']);
});

    Route::post('/set/role', [AuthController::class, 'setRole']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
/*

|--------------------------------------------------------------------------

*/


/*
|--------------------------------------------------------------------------
|   Oauth REDIRECT LINK RETURN
|--------------------------------------------------------------------------
*/
Route::get('auth/github', [GitHubController::class, 'redirectToGitHub']);
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);

/*
/--------------------------------------------------------------------------
/ Simple Login
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
/* ------------------------------------------------------------------------ */
