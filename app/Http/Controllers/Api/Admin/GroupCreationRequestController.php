<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupCreationRequest;
use Illuminate\Http\Request;

class GroupCreationRequestController extends Controller
{
    public function getGroupRequests(Request $request){
        $per_page = $request->input('per_page', 10);
        $page = $request->input('page',1);
        $groupCreationRequests = GroupCreationRequest::with('user')->paginate($per_page, ['*'],'page', $page);
        return response()->json([
            'group_creation_requests' => $groupCreationRequests,
            'message' => 'Group Creation Requests Fetched Successfully'
        ]);
    }
}
