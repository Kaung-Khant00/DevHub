<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function getAdminUser(Request $request){
        $admin = $request->user();
        return response()->json([
            'admin' => $admin->load('adminProfile'),
        ]);
    }
}
