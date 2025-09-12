<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class GroupCreationRequestController extends Controller
{
    public function getGroupCreationRequests(Request $request)
    {
        $user = $request->user();
        $groupCreationRequests = $user->groupCreationRequests()->get();
        return response()->json([
            'message' => 'Notifications retrieved successfully.',
            'group_creation_requests' => $groupCreationRequests,
        ]);
    }

    public function getGroupCreationRequestsById(Request $request, $id)
    {
        $user = $request->user();
        $groupCreationRequest = $user->groupCreationRequests()->where('id', $id)->first();
        return response()->json([
            'message' => 'Group Create Request retrieved successfully.',
            'group_creation_request' => $groupCreationRequest,
        ]);
    }

    public function deleteGroupCreationRequestsById(Request $request,$id){
        $user = $request->user();
        $groupCreationRequest = $user->groupCreationRequests()->where('id',$id)->first();
        if(!empty($groupCreationRequest->image) && Storage::disk('public')->exists($groupCreationRequest->image)){
            Storage::disk('public')->delete($groupCreationRequest->image);
        }
        $groupCreationRequest->delete();
        return response()->json([
            'message' => 'Group Create Request deleted successfully.',
            'id' => $id
        ]);
    }
}
