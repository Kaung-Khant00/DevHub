<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Group;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\GroupCreationRequest;

class AdminGroupCreationRequestController extends Controller
{

/*
|-------------------------------------------------------------------------
| GET ALL GROUP CREATION REQUEST
|--------------------------------------------------------------------------
*/
 public function getAllGroupRequests(Request $request){
        $per_page = $request->query('per_page', 10);
        $page = $request->query('page',1);
        $searchQuery = $request->query('searchQuery');
        logger($searchQuery);
        $groupCreationRequests = GroupCreationRequest::with('user')
        ->when($searchQuery, function ($query) use ($searchQuery) {
            return $query->where('name', 'LIKE', '%' . $searchQuery . '%');})
            ->latest()->paginate($per_page, ['*'],'page', $page);
        return response()->json([
            'group_creation_requests' => $groupCreationRequests,
            'message' => 'ALL Group Creation Requests Fetched Successfully'
        ]);
    }

/*
|-------------------------------------------------------------------------
| GET GROUP CREATION REQUEST WITH STATUS
|--------------------------------------------------------------------------
*/
    public function getGroupRequests(Request $request){
        $per_page = $request->query('per_page', 10);
        $page = $request->query('page',1);
        $status = $request->query('status','pending');
        if($status == "all"){
            $status = null;
        }
        $searchQuery = $request->query('searchQuery');
        $groupCreationRequests = GroupCreationRequest::with('user')
        ->where('status', $status)
        ->when($searchQuery, function ($query) use ($searchQuery) {
            return $query->where('name', 'LIKE', '%' . $searchQuery . '%');})
            ->paginate($per_page, ['*'],'page', $page);
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
    public function approveGroupRequest($id,Request $request){
        $groupCreationRequest = GroupCreationRequest::findOrFail($id);
        $groupCreationRequest->load('user');
        $groupCreationRequest->update([
            'status' => 'approved'
        ]);
        $groupData = $this->makeGroupData($groupCreationRequest);
        $group = Group::create($groupData);
        $notificationData = $this->makeApproveJson($groupCreationRequest->id,$groupCreationRequest->user_id,$request->user()->id,$group->id,$group->name);
        Notification::create($notificationData);

        return response()->json([
            'message' => 'Group Creation Request Approved Successfully',
            'group_creation_request'=> $groupCreationRequest->refresh()->load('user')
        ]);
    }
            private function makeGroupData($groupCreationRequest){
                return [
                    'name' => $groupCreationRequest->name,
                    'description' => $groupCreationRequest->description,
                    'image' => $groupCreationRequest->image,
                    'user_id' => $groupCreationRequest->user->id,
                    'tags' => $groupCreationRequest->tags
                ];
            }
    private function makeApproveJson($id,$userId,$adminId,$groupId,$groupName){
        return [
            'type' => 'GROUP_CREATION_REQUEST_APPROVED',
            'user_id' => $userId,
            'is_read' => false,
            'title' => 'Group Creation Request Approved',
            'message' => 'Congratulations! Your request to create the group “'.$groupName.'” has been approved. You can now start building your community, inviting members, and sharing content.',
            'data' => [
                'group_creation_request_id' => $id,
                'approved_by' => $adminId,
                'group_id'=>$groupId
            ]
        ];
    }

/*
|-------------------------------------------------------------------------
| DENY GROUP CREATION REQUEST
|--------------------------------------------------------------------------
*/
    public function RejectGroupRequest($id,Request $request){
        $groupCreationRequest = GroupCreationRequest::findOrFail($id);
        $groupCreationRequest->update([
            'status' => 'rejected'
            ]);
        $notificationData = $this->makeRejectJson($groupCreationRequest,$request->user()->id);
        Notification::create($notificationData);

        return response()->json([
            'message' => 'Group Creation Request Denied Successfully',
            'group_creation_request'=> $groupCreationRequest->refresh()->load('user')
        ]);
    }
        private function makeRejectJson($groupCreationRequest,$adminId){
        return [
            'type' => 'GROUP_CREATION_REQUEST_REJECTED',
            'user_id' => $groupCreationRequest->user_id,
            'is_read' => false,
            'title' => 'Group Creation Request Rejected',
            'message' => 'Thank you for submitting your request to create the group “'.$groupCreationRequest->name.'”. After careful review, we’re unable to approve it at this time.',
            'data' => [
                'group_creation_request_id' => $groupCreationRequest->id,
                'rejected_by' => $adminId,
            ]
        ];
    }
}
