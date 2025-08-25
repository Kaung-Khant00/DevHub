<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\Auth\GoogleController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('auth/github/callback',[GitHubController::class,'handleGitHubCallback']);
Route::get('auth/google/callback',[GoogleController::class,'handleGoogleCallback']);
