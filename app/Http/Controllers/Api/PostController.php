<?php

namespace App\Http\Controllers\Api;

use id;
use App\Models\File;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /*
  |-------------------------------------------------------------------------
  | GET ALL POSTS In PAGINATION WITH NEWEST POST
  |--------------------------------------------------------------------------
  */
    public function getPosts(Request $request)
    {
        $sortBy = $request->query('sortBy');
        $perPage = $request->query('perPage');
        $currentPage = $request->query('currentPage');
        $posts = Post::public()->with(['user', 'file'])
            ->withCount(['likedUsers', 'comments'])
            ->withExists([
                'likedUsers as liked' => function ($q) use ($request) {
                    $q->where('user_id', $request->user()->id);
                },
     'postFollowers as followed' => function ($q) use ($request) {
            $q->where('follower_id', $request->user()->id);
        },
            ])
            ->when($sortBy, function ($query, $sortBy) {
                $sort = explode(',', $sortBy);
                $query->orderBy($sort[0], $sort[1]);
            })
            ->paginate($perPage, ['*'], 'page', $currentPage);
        return response()->json([
            'message' => 'Posts retrieved successfully.',
            'data' => $posts,
        ]);
    }

    /*
  |-------------------------------------------------------------------------
  | Create Post
  |--------------------------------------------------------------------------
  */
    public function store(Request $request)
    {
        $this->validatePost($request);
        $userId = $request->user()->id;
        $postData = $this->getPostData($request);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            // $imageName = time().'_'.$image->getClientOriginalName();
            $imagePath = $image->store('images', 'public');
            $postData['image'] = $imagePath;
        }
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            // $fileName = time().'_'.$file->getClientOriginalName();
            $filePath = $file->store('files', 'public');
            $fileInfo = $this->getFileInfoData($request);
            $fileInfo['path'] = $filePath;
            $file = File::create($fileInfo);
            $postData['file_id'] = $file->id;
        }

        $post = Post::create(
            array_merge($postData, [
                'user_id' => $userId,
            ]),
        );

        return response()->json(
            [
                'message' => 'Post created successfully.',
                'post' => $post->load('file','user'),
            ],
            201,
        );
    }
    /*  I asked chat gpt about file types here */
    private function validatePost(Request $request)
    {
        $request->validate(
            [
                'title' => 'nullable|string|max:255',
                'content' => 'required|string|max:8000',
                'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
                'file' => 'nullable|file|max:10240',
                'code' => 'nullable|string|max:5000',
                'code_lang' => 'nullable|string',
            ],
            [
                'image.max' => 'The image can not be greater than 5 MB.',
                'file.max' => 'The file can not be greater than 10 MB.',
            ],
        );
    }
    private function getPostData(Request $request)
    {
        return [
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
    /*
  |-------------------------------------------------------------------------
  | Get Post By ID
  |--------------------------------------------------------------------------
  */
    public function getPostById($id,Request $request)
    {
        $post = Post::with('user')->withExists([
            'postFollowers as followed' => function ($q) use ($request) {
                $q->where('follower_id', $request->user()->id);
            }
        ])->find($id);
        if($post->user_id !== $request->user()->id){
            $post = $post->public();
        }
        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }
        return response()->json([
            'message' => 'Post retrieved successfully.',
            'data' => $post,
        ]);
    }

    /*
  |-------------------------------------------------------------------------
  | Get Post By ID
  |--------------------------------------------------------------------------
  */
    public function getDetailPostById(Request $request, $id)
    {
        $post = Post::public()->where('id', $id)
            ->with(['user', 'file'])
            ->withCount('likedUsers', 'comments')
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
            return response()->json(['message' => 'Post not found.'], 404);
        }
        return response()->json([
            'message' => 'Detail Post retrieved successfully.',
            'post' => $post,
        ]);
    }

    /*
  |-------------------------------------------------------------------------
  | Update OR EDIT Post by ID
  |--------------------------------------------------------------------------
  */
    public function updatePost($id, Request $request)
    {
        $post = Post::find($id);
        logger($request);
        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $this->validateUpdatingPost($request,true);
        $postData = $this->getPostData($request);
        /*
|-----------------------------------
|  File And Image deleting when the user wants to delete not update
*/
        if ($request->boolean('isDeleteImage')) {
            logger('delete image');
            $this->deleteImage($post);

            $postData['image'] = null;
        }
        if ($request->boolean('isDeleteFile')) {
            $this->deleteFile($post);

            $postData['file'] = null;
        }
        /*
|--------------------------------
|  UPDATE File And Image and delete the previous ones
*/
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $this->deleteImage($post);
            $imagePath = $image->store('images', 'public');
            $postData['image'] = $imagePath;
        }
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $this->deleteFile($post);
            $filePath = $file->store('files', 'public');
            $postData['file'] = $filePath;
        }
        $post->update($postData);
        $post->load('user');
        return response()->json([
            'message' => 'Post updated successfully.',
            'post' => $post,
        ]);
    }
    private function validateUpdatingPost(Request $request,$isUpdating = false)
    {
        $request->validate(
            [
                'title' => 'nullable|string|max:255',
                'content' => [Rule::requiredIf($isUpdating),'string','max:8000'],
                'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
                'file' => 'nullable|file|mimes:html,css,scss,sass,js,ts,jsx,tsx,vue,php,py,java,c,cpp,h,cs,go,rb,sh,json,xml,yml,yaml,sql,csv,env,md,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,7z,tar,gz|max:10240',
                'code' => 'nullable|string',
                'code_lang' => 'nullable|string',
            ],
            [
                'image.max' => 'The image can not be greater than 5 MB.',
                'file.max' => 'The file can not be greater than 10 MB.',
            ],
        );
    }

    /*
|-------------------------------------------------------------------------
| DELETE POST BY ID
|--------------------------------------------------------------------------
*/
    public function deletePost($id, Request $request)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }
        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $this->deleteImage($post);
        $this->deleteFile($post);
        $post->reports()->delete();
        $post->comments()->delete();
        PostLike::where('post_id', $post->id)->delete();
        $post->forceDelete();
        if($post->file_id){
            File::where('id',$post->file['id'])->delete();
        }

        return response()->json(['id' => $post->id, 'message' => 'Post deleted successfully.']);
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

    /*
  |-------------------------------------------------------------------------
  | Get Post By ID
  |--------------------------------------------------------------------------
  */
    public function likePost(Request $request)
    {
        $post = Post::find($request->post_id);

        /*  syncWithoutDetaching will add only if the user id is not present in the post :) */
        /*  I toggled the like and return false if the user is already like and return true when the user did't like the post before */
        $liked = $post->toggleLike($request->user()->id);
        $post->load('user')->loadCount('likedUsers');
        /*  And reinsert the post liked data back to the post object :) */
        $post->liked = $liked;
        return response()->json([
            'message' => 'Post toggled successfully.',
            'post' => $post,
        ]);
    }

    /*
|-------------------------------------------------------------------------
| Comment Post
|--------------------------------------------------------------------------
*/
    public function commentPost(Request $request)
    {
        $this->validateComment($request);
        $post = Post::whereNotIn('privacy', ['private'])->find($request->post_id);
        $comment = $post->comments()->create([
            'post_id' => $request->post_id,
            'comment' => $request->comment,
            'user_id' => $request->user()->id,
        ]);
        $comment->load('user');
        return response()->json([
            'message' => 'Commented successfully.',
            'comment' => $comment,
        ]);
    }
    private function validateComment(Request $request)
    {
        return $request->validate([
            'post_id' => 'required|exists:posts,id',
            'comment' => 'required|string|max:1000',
        ]);
    }

    /*
|-------------------------------------------------------------------------
| FETCH COMMENT OF HTE POST
|--------------------------------------------------------------------------
*/
    public function getComments($id, Request $request)
    {
        $sortBy = $request->query('sortBy');
        $perPage = $request->query('perPage');
        $currentPage = $request->query('currentPage');
        $comments = PostComment::where('post_id', $id)
            ->with(['user'])
            ->when($sortBy, function ($query, $sortBy) {
                $sort = explode(',', $sortBy);
                $query->orderBy($sort[0], $sort[1]);
            })->latest()
            ->paginate($perPage, ['*'], 'page', $currentPage);
        return response()->json([
            'message' => 'Comments fetched successfully.',
            'comments' => $comments,
        ]);
    }

    /*
|-------------------------------------------------------------------------
| UPDATE COMMENT
|--------------------------------------------------------------------------
*/
    public function updateComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        logger($id);
        $comment = PostComment::find($id);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found.'], 404);
        }

        if ($request->user()->id != $comment->user_id) {
            return response()->json(['message' => "You don't have permission to update this comment."], 403);
        }
        $comment->update([
            "comment"=> $request->comment,
        ]);
        $comment->load(['user','post']);
        return response()->json([
            'message' => 'Comment updated successfully.',
            'comment' => $comment,
        ]);
    }

        /*
|-------------------------------------------------------------------------
| DELETE COMMENT
|--------------------------------------------------------------------------
*/
    public function deleteComment(Request $request,$id){
        $user = $request->user();
        $comment = PostComment::find($id);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found.'], 404);
        }
        if ($user->id != $comment->user_id) {
            return response()->json(['message' => "You don't have permission to delete this comment."], 403);
        }
        $comment->delete();
        return response()->json([
            'message' => 'Comment deleted successfully.',
            'id' => $comment->id
        ]);
    }
    /*
|-------------------------------------------------------------------------
| DOWNLOAD FILE
|--------------------------------------------------------------------------
*/
    public function download(Request $request)
    {
        $file = $request->input('path');
        logger($file);
        if (!$file || !Storage::disk('public')->exists($file)) {
            abort(404, 'File not found');
        }

        $path = Storage::disk('public')->path($file);
        return response()->download($path);
    }
}
