<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Controllers */
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Api\User\GroupPostController;
use App\Http\Controllers\Api\User\NotificationController;
use App\Http\Controllers\Api\User\GroupCreationRequestController;
use App\Http\Controllers\Api\Admin\AdminGroupCreationRequestController;

/*
|--------------------------------------------------------------------------
| Public Auth Routes (no auth)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('auth/github', [GitHubController::class, 'redirectToGitHub']);
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);

/*
|--------------------------------------------------------------------------
| Protected routes (sanctum)
|--------------------------------------------------------------------------
|
| All routes below require authentication via Sanctum.
|
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Helper: current authenticated user
    |--------------------------------------------------------------------------
    */
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    /*
    |--------------------------------------------------------------------------
    | Admin area (ADMIN, SUPER_ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')
        ->middleware('role:ADMIN,SUPER_ADMIN')
        ->group(function () {

            // Basic admin info
            Route::get('/', [AdminController::class, 'getAdminUser']);

            // Group creation requests handled by admin
            Route::prefix('group_requests')->group(function () {
                Route::get('/',       [AdminGroupCreationRequestController::class, 'getGroupRequests']);
                Route::get('/all',   [AdminGroupCreationRequestController::class, 'getAllGroupRequests']);
                Route::get('/{id}/approve', [AdminGroupCreationRequestController::class, 'approveGroupRequest']);
                Route::get('/{id}/reject',  [AdminGroupCreationRequestController::class, 'rejectGroupRequest']);
            });
    });

    /*
    |--------------------------------------------------------------------------
    | Super admin area (SUPER_ADMIN)
    |--------------------------------------------------------------------------
    */
    Route::prefix('super_admin')
        ->middleware('role:SUPER_ADMIN')
        ->group(function () {
            Route::prefix('users')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\SuperAdmin\UserController::class, 'getUsers']);
            });
    });

    /*
    |--------------------------------------------------------------------------
    | Profile routes
    |--------------------------------------------------------------------------
    | - /profile            => get authenticated user profile
    | - /profile/posts      => posts by the authenticated user
    | - /profile/developer/{id} => other developers' public profile
    */
    Route::prefix('profile')->group(function () {
        Route::get('/',                       [ProfileController::class, 'getProfile']);
        Route::get('/posts',                  [ProfileController::class, 'getUserPosts']);
        Route::get('/developer/{id}',         [ProfileController::class, 'getDeveloperProfile']);

        Route::post('/developer/edit/{id}',   [ProfileController::class, 'editProfile']);
        Route::post('/developer/image/edit',  [ProfileController::class, 'uploadProfileImage']);
        Route::post('/posts/search',          [ProfileController::class, 'searchPosts']);
        Route::delete('/developer/image',     [ProfileController::class, 'deleteProfileImage']);
    });

    /*
    |--------------------------------------------------------------------------
    | Posts routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('posts')->group(function () {
        // GET
        Route::get('/',             [PostController::class, 'getPosts']);
        Route::get('/{id}',         [PostController::class, 'getPostById']);
        Route::get('/{id}/detail',  [PostController::class, 'getDetailPostById']);
        Route::get('/{id}/comments',[PostController::class, 'getComments']);

        // POST
        Route::post('/',            [PostController::class, 'store']);
        Route::post('/like',        [PostController::class, 'likePost']);
        Route::post('/comment',     [PostController::class, 'commentPost']);
        Route::post('/download',    [PostController::class, 'download']);

        // PUT/PATCH
        Route::match(['put','patch'],'/{id}', [PostController::class, 'updatePost']);
        Route::match(['put','patch'],'/{id}/comment', [PostController::class, 'updateComment']);

        // DELETE
        Route::delete('/{id}',           [PostController::class, 'deletePost']);
        Route::delete('/{id}/comment',   [PostController::class, 'deleteComment']);
    });

    /*
    |--------------------------------------------------------------------------
    | Groups routes
    |--------------------------------------------------------------------------
    | Note: join is currently a GET in your original file — consider making it POST
    | if you later want to follow REST principles (action that mutates server state).
    */
    Route::prefix('groups')->group(function () {
        Route::get('/',         [GroupController::class, 'getGroups']);
        Route::get('/{id}',     [GroupController::class, 'getGroupDetail']);
        Route::get('/{id}/join', [UserController::class, 'joinGroup']); // keep as-is for now
        Route::post('/create',  [GroupController::class, 'createGroup']);
        Route::post('/{id}/post', [GroupPostController::class, 'createGroupPost']);


    });

    /*
    |--------------------------------------------------------------------------
    | Users actions
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->group(function () {
        Route::get('/{id}/follow', [UserController::class, 'followUser']);
    });

    /*
    |--------------------------------------------------------------------------
    | Group creation requests (user-facing)
    |--------------------------------------------------------------------------
    */
    Route::prefix('group_requests')->group(function () {
        Route::get('/',       [GroupCreationRequestController::class, 'getGroupCreationRequests']);
        Route::get('/{id}',   [GroupCreationRequestController::class, 'getGroupCreationRequestsById']);
        Route::delete('/{id}',[GroupCreationRequestController::class, 'deleteGroupCreationRequestsById']);
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->group(function () {
        Route::get('/',                   [NotificationController::class, 'getNotifications']);
        Route::get('/{id}',               [NotificationController::class, 'getNotificationById']);
        Route::match(['put','patch'], '/{id}/read',   [NotificationController::class, 'updateNotificationReadStatus'])->whereNumber('id');
        Route::match(['put','patch'], '/all/read',    [NotificationController::class, 'updateNotificationAllReadStatus']);
        Route::delete('/{id}',           [NotificationController::class, 'deleteNotification'])->whereNumber('id');
        Route::delete('/all/read',       [NotificationController::class, 'deleteAllReadNotification']);
    });

    /*
    |--------------------------------------------------------------------------
    | Misc / misc admin helpers
    |--------------------------------------------------------------------------
    */
    Route::post('/set/role', [AuthController::class, 'setRole']);
    Route::post('/logout',   [AuthController::class, 'logout']);
});
