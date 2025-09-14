<?php

namespace App\Http\Controllers\Api;

use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\GroupCreationRequest;

class GroupController extends Controller
{
        /*
  |-------------------------------------------------------------------------
  | CREATE GROUP REQUEST TO THE ADMIN
  |--------------------------------------------------------------------------
  */
    public function createGroup(Request $request){
        $this->validateGroup($request);
        $groupData = $this->getGroupCreationRequestData($request);
        $image = $request->file("image");
        $image = $image->store('images', 'public');
        $groupData['image'] = $image;
        $group_creation_request = GroupCreationRequest::create( $groupData );
        return response()->json([
            'message' => 'Group creation request sent successfully.',
            'group_creation_request' => $group_creation_request,
        ]);
    }
    public function validateGroup(Request $request){
        return $request->validate([
            "name"=> "required|max:40|string|unique:groups,name",
            'description' => 'nullable|max:255|string',
            'image'=> 'nullable|image|mimes:jpg,jpeg,webp,png|max:2048',
            'tags' => 'nullable|array',
            'tags.*'=> 'nullable|string|max:40',
            'rules'=> 'nullable|string|max:1000',
            'rules.*'=> 'nullable|string|max:200',
        ]);
    }
    public function getGroupCreationRequestData(Request $request){
        return [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'tags' => $request->input('tags'),
            'user_id' => $request->user()->id
        ];
    }
        /*
  |-------------------------------------------------------------------------
  | FETCH GROUPS DATA
  |--------------------------------------------------------------------------
  */
  public function getGroups(Request $request){
    $groups = Group::withExists([
        'members as joined' => function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        }
    ])->withCount('members')->get();
    return response()->json([
        'groups' => $groups,
    ]);
  }
  public function getGroupDetail($id){
    $group = Group::with('user')->where('id',$id)->withExists(
        ['members as joined' => function ($q) use ($id) {
            $q->where('group_id', $id);
        }]
    )->withCount('members')->first();
    return response()->json([
        'message'=>'Group retrieved successfully.',
        'group'=> $group
    ]);
  }
}
