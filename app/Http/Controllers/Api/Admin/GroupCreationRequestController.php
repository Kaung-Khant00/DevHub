<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupCreationRequest;
use Illuminate\Http\Request;

class GroupCreationRequestController extends Controller
{
        /*
  |-------------------------------------------------------------------------
  | GET ALL GROUP CREATION REQUEST
  |--------------------------------------------------------------------------
  */
    public function getGroupRequests(Request $request){
        $per_page = $request->query('per_page', 10);
        $page = $request->query('page',1);
        $status = $request->query('status','pending');
        $groupCreationRequests = GroupCreationRequest::with('user')->where('status', $status)->paginate($per_page, ['*'],'page', $page);
        return response()->json([
            'group_creation_requests' => $groupCreationRequests,
            'message' => 'Group Creation Requests Fetched Successfully'
        ]);
    }
/*
|-------------------------------------------------------------------------
| ALLOW GROUP CREATION REQUEST
|--------------------------------------------------------------------------
*/
    public function approveGroupRequest($id){
        $groupCreationRequest = GroupCreationRequest::findOrFail($id);

        $groupCreationRequest->update([
            'status' => 'approved'
        ]);

        return response()->json([
            'message' => 'Group Creation Request Approved Successfully',
            'group_creation_request'=> $groupCreationRequest->refresh()
        ]);
    }

/*
|-------------------------------------------------------------------------
| DENY GROUP CREATION REQUEST
|--------------------------------------------------------------------------
*/
    public function RejectGroupRequest($id){
        $groupCreationRequest = GroupCreationRequest::findOrFail($id);
        $groupCreationRequest->update([
            'status' => 'rejected'
            ]);

        return response()->json([
            'message' => 'Group Creation Request Denied Successfully',
            'group_creation_request'=> $groupCreationRequest
        ]);
    }
}
