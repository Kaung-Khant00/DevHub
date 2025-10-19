<?php

namespace App\Http\Controllers\Api\User;

use App\Models\File;
use App\Models\Group;
use App\Models\GroupPost;
use Illuminate\Http\Request;
use App\Models\GroupPostLike;
use App\Models\GroupPostComment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class GroupPostController extends Controller
{
    public function createGroupPost(Request $request, $id)
    {
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
                'post' => $post->load(['file','user']),
            ],
            201,
        );
    }
    protected function validateGroupPost(Request $request)
    {
        $request->validate(
            [
                'group_id' => 'required|exists:groups,id',
                'title' => 'nullable|string|max:255',
                'content' => 'required|string|max:10000',
                'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
                'file' => 'nullable|file|max:10240',
                'code' => 'nullable|string',
                'codeLang' => 'nullable|string|max:255',
                'tags' => 'nullable|array|max:3',
                'tags.*' => 'nullable|string|max:25',
            ],
            [
                'image.max' => 'The image can not be greater than 5 MB.',
                'file.max' => 'The file can not be greater than 10 MB.',
                'codeLang.max' => 'The code language can not be greater than 255 characters.',
            ],
        );
    }

    private function getGroupPostData(Request $request)
    {
        return [
            'group_id' => $request->input('group_id'),
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

    public function getGroupPosts(Request $request, $groupId)
    {
        $group = Group::findOrFail($groupId);
        $page = $request->query('page', 1);
        $per_page = $request->query('per_page', 10);
        $posts = GroupPost::where('group_id', $group->id)
            ->with(['user', 'file'])
            ->withCount('likedUsers')
            ->withExists([
                'likedUsers as liked' => function ($q) use ($request) {
                    $q->where('user_id', $request->user()->id);
                },
                'postFollowers as followed' => function ($q) use ($request) {
                    $q->where('follower_id', $request->user()->id);
                },
            ])
            ->latest()
            ->paginate($per_page, ['*'], 'page', $page);
        return response()->json([
            'posts' => $posts,
        ]);
    }

    public function likeGroupPost($postId, Request $request)
    {
        $post = GroupPost::findOrFail($postId);
        $liked = $post->toggleGroupPostLike($request->user()->id);
        return response()->json([
            'message' => 'Post liked successfully.',
            'liked' => $liked,
            'post' => $post->loadCount('likedUsers'),
        ]);
    }
    public function getDetailPostById(Request $request, $id)
    {
        $post = GroupPost::with(['user', 'file'])
            ->withCount('likedUsers', 'comments')
            ->withExists([
                'likedUsers as liked' => function ($q) use ($request) {
                    $q->where('user_id', $request->user()->id);
                },
                'postFollowers as followed' => function ($q) use ($request) {
                    $q->where('follower_id', $request->user()->id);
                },
            ])
            ->findOrFail($id);
        return response()->json([
            'message' => 'Detail Post retrieved successfully.',
            'post' => $post,
        ]);
    }

    public function getGroupPostDetailById($postId, Request $request)
    {
        $post = GroupPost::where('id', $postId)
            ->with(['user', 'file'])
            ->withCount('likedUsers')
            ->withExists([
                'likedUsers as liked' => function ($q) use ($request) {
                    $q->where('user_id', $request->user()->id);
                },
                'postFollowers as followed' => function ($q) use ($request) {
                    $q->where('follower_id', $request->user()->id);
                },
            ])
            ->first();
        if (!$post) {
            return response()->json(['message' => 'Group Post not found.'], 404);
        }
        return response()->json([
            'message' => 'Detail Group Post retrieved successfully.',
            'post' => $post,
        ]);
    }
    public function createGroupPostComment(Request $request, $postId)
    {
        $request->validate([
            'comment' => 'required|string|max:2500',
        ]);
        $post = GroupPost::findOrFail($postId);
        $comment = $post->comments()->create([
            'post_id' => $postId,
            'user_id' => $request->user()->id,
            'comment' => $request->comment,
        ]);
        $comment->load('user');
        return response()->json([
            'message' => 'Commented successfully.',
            'comment' => $comment,
        ]);
    }

    public function getGroupPostComments($postId, Request $request)
    {
        $post = GroupPost::findOrFail($postId);
        $page = $request->query('page', 1);
        $per_page = $request->query('per_page', 10);
        $comments = $post
            ->comments()
            ->with('user')
            ->paginate($per_page, ['*'], 'page', $page);
        return response()->json([
            'comments' => $comments,
        ]);
    }
    public function updateGroupPostComment(Request $request, $postId)
    {
        $request->validate([
            'comment' => 'required|string|max:2500',
        ]);
        $comment = GroupPostComment::find($postId);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found.'], 404);
        }

        if ($request->user()->id != $comment->user_id) {
            return response()->json(['message' => "You don't have permission to update this comment."], 403);
        }
        $comment->update([
            'comment' => $request->comment,
        ]);
        $comment->load(['user']);
        return response()->json([
            'message' => 'Comment updated successfully.',
            'comment' => $comment,
        ]);
    }
    public function deleteGroupPostComment(Request $request, $postId)
    {
        $user = $request->user();
        $comment = GroupPostComment::find($postId);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found.'], 404);
        }
        if ($user->id != $comment->user_id) {
            return response()->json(['message' => "You don't have permission to delete this comment."], 403);
        }
        $comment->delete();
        return response()->json([
            'message' => 'Comment deleted successfully.',
            'id' => $comment->id,
        ]);
    }
    public function deleteGroupPost(Request $request, $postId)
    {
        $user = $request->user();
        $post = GroupPost::findOrFail($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }
        if ($user->id != $post->user_id) {
            return response()->json(['message' => "You don't have permission to delete this post."], 403);
        }

        if (!empty($post->image) && is_string($post->image) && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }
        if (!empty($post->file) && Storage::disk('public')->exists($post->file['path'])) {
            Storage::disk('public')->delete($post->file['path']);
        }
        DB::transaction(function () use ($post) {
            GroupPostComment::where('post_id', $post->id)->delete();
            GroupPostLike::where('post_id', $post->id)->delete();
            $post->delete();
            if($post->file_id){
            File::where('id',$post->file['id'])->delete();
        }
        });
            return response()->json([
            'message' => 'Post deleted successfully.',
            'id' => $post->id,
        ]);
    }

    public function updateGroupPost(Request $request, $id)
    {

        $this->validateGroupPost($request);
        $post = GroupPost::where('user_id', $request->user()->id)->findOrFail($id);

        $groupPostData = $this->getGroupPostData($request);
        $groupFileData = $this->getFileInfoData($request);


        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $this->deleteImage($post);
            $imagePath = $image->store('images', 'public');
            $groupPostData['image'] = $imagePath;
        }else  if ($request->boolean('isDeleteImage')) {
            logger('delete image');
            $this->deleteImage($post);

            $groupPostData['image'] = null;
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $this->deleteFile($post);
            $filePath = $file->store('files', 'public');
            $groupFileData['path'] = $filePath;
            $file = $post->file()->updateOrCreate($groupFileData);
            $groupPostData['file_id'] = $file->id;
        }else if ($request->boolean('isDeleteFile')) {
            $this->deleteFile($post);
            $fileId = $post->file_id;
            $post->update(['file_id' => null]);
            File::where('id', $fileId)->delete();
        }
        if($post->file_id !== null){
            $file = $post->file()->update($groupFileData);
        }
        $post->update($groupPostData);
        $post->load(['user','file']);
        return response()->json([
            'message' => 'Group Post updated successfully.',
            'post' => $post,
        ]);
        }

        private function deleteImage($post)
    {
        if (!empty($post->image) && is_string($post->image) && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }
    }
    private function deleteFile($post)
    {
        if (!empty($post->file) && Storage::disk('public')->exists($post->file['path'])) {
            Storage::disk('public')->delete($post->file['path']);
        }
    }
}
