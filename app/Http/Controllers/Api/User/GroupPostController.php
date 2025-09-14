<?php

namespace App\Http\Controllers\Api\User;

use App\Models\File;
use App\Models\Group;
use App\Models\GroupPost;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupPostController extends Controller
{
    public function createGroupPost(Request $request,$id){
        $group = Group::findOrFail($id);
        logger($request->all());
        $this->validateGroupPost($request);
        $userId = $request->user()->id;
        $postData = $this->getGroupPostData($request);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('images', 'public');
            $postData['image'] = $imagePath;
        }
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('files', 'public');
            $fileInfo = $this->getFileInfoData($request);
            $fileInfo['path'] = $filePath;
            $file = File::create($fileInfo);
            $postData['file_id'] = $file->id;
        }

        $post = GroupPost::where('group_id', $group->id)->create(
            array_merge($postData, [
                'user_id' => $userId,
            ]),
        );

        return response()->json(
            [
                'message' => 'Post created successfully.',
                'post' => $post,
            ],
            201,
        );
    }
    protected function validateGroupPost(Request $request){
        $request->validate(
            [
                'group_id'=> 'required|exists:groups,id',
                'title' => 'nullable|string|max:255',
                'content' => 'required|string|max:10000',
                'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
                'file' => 'nullable|file|max:10240',
                'code' => 'nullable|string',
                'code_lang' => 'nullable|string',
            ],
            [
                'image.max' => 'The image can not be greater than 5 MB.',
                'file.max' => 'The file can not be greater than 10 MB.',
            ],
        );
    }

        private function getGroupPostData(Request $request)
    {
        return [
            'group_id'=> $request->input('group_id'),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'code' => $request->input('code'),
            'code_lang' => $request->input('codeLang'),
            'tags' => $request->input('tags'),
        ];
    }
        private function getFileInfoData(Request $request)
    {
        return [
            'name' => $request->input('fileInfo.name'),
            'size' => $request->input('fileInfo.size'),
            'type' => $request->input('fileInfo.type'),
        ];
    }
}
