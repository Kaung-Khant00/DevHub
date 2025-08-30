<?php

namespace App\Http\Controllers\Api;

use App\Models\File;
use App\Models\Post;
use Illuminate\Http\Request;
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
        $posts = Post::with(['user', 'file'])
            ->withCount('likedUsers')
            ->withExists([
                'likedUsers as liked' => function ($q) use ($request) {
                    $q->where('user_id', $request->user()->id);
                },
            ])
            ->when($sortBy,function($query,$sortBy){
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
                'post' => $post,
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
    public function getPostById($id)
    {
        $post = Post::with('user')->find($id);
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
  | Update OR EDIT Post by ID
  |--------------------------------------------------------------------------
  */
    public function update($id, Request $request)
    {
        $post = Post::find($id);
        logger($request);
        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $this->validateUpdatingPost($request);
        $isDeleteImage = $request->input('isDeleteImage', false);
        $isDeleteFile = $request->input('isDeleteFile', false);
        $postData = $this->getPostData($request);
        /*
|-----------------------------------
|  File And Image deleting when the user wants to delete not update
*/
        if ($isDeleteImage === true) {
            $this->deleteImage($post);

            $postData['image'] = null;
        }
        if ($isDeleteFile === true) {
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
    private function validateUpdatingPost(Request $request)
    {
        $request->validate(
            [
                'title' => 'nullable|string|max:255',
                'content' => 'required|string|max:10000',
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
    public function delete($id, Request $request)
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
        $post->delete();
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
        if (!empty($post->file) && is_string($post->file) && Storage::disk('public')->exists($post->file)) {
            Storage::disk('public')->delete($post->file);
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
