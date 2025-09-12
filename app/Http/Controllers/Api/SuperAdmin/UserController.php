<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function getUsers(){
        $page = request()->query('page',1);
        $users = User::latest()->where('role', 'developer')->paginate(1,['*'], 'page', $page);
        return response()->json([
            'data' => $users,
        ]);

    }
}
