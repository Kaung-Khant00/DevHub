<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\QuestionMessage;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    public function askQuestion(Request $request){
        $this->validateQuestion($request);
        $question = $this->getQuestionData($request);
        if($request->hasFile('image')){
            $image = $request->file('image');
            $question['image_path'] = $image->store('questions', 'public');
        }
        $question = Question::create(array_merge($question, ['user_id'=>$request->user()->id]));

        return response()->json([
            'message' => 'Question posted successfully',
            'question' => $question
        ], 201);
    }
    private function validateQuestion(Request $request,$isUpdating = false){
        $request->validate([
            'title'=>['string','max:255',Rule::requiredIf(!$isUpdating)],
            'body'=>['string','max:5000',Rule::requiredIf(!$isUpdating)],
            'code_snippet'=>'nullable|string|max:5000',
            'image'=>'nullable|max:3072|image|mimes:png,jpg,jpeg,webp', // max 3MB
            'is_anonymous'=>'nullable|boolean',
            'tags' => 'nullable|array|max:3',
            'tags.*' => 'nullable|string|max:40',
        ],[
            'tags.max' => 'You can only add up to 3 tags',
        ]);
    }
    private function getQuestionData(Request $request){
        return [
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'code_snippet' => $request->input('code_snippet'),
            'is_anonymous' => $request->input('is_anonymous') ,
            'tags' => $request->input('tags')
        ];
    }
    public function getQuestions(Request $request){
        $searchQuery = $request->query('searchQuery');
        $page = $request->query('page',1);
        $per_page = $request->query('perPage',4);
        $sortBy = $request->query('sortBy');
        $status = $request->query('status');
        $questions = Question::when($searchQuery,function ($query) use ($searchQuery){
            return $query->whereAny(['title','body','code_snippet'], 'LIKE', '%' . $searchQuery . '%');
        })
        ->when($status,function ($query) use ($status){
            return $query->where('is_solved', $status);
        })
        ->withExists([
            'likedUsers as is_liked' => function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
        }])
        ->when('sortBy',function ($query) use ($sortBy) { $sort = explode(',', $sortBy); $query->orderBy($sort[0], $sort[1]);})
        ->withCount(['questionMessages','likedUsers'])->latest()
        ->paginate($per_page, ['*'], 'page', $page);
        return response()->json([
            'questions' => $questions
        ]);
    }
    public function getQuestionDetailById(Request $request, $id){
        /*  fetching question detail */
        $question = Question::when(!Question::where('id', $id)->value('is_anonymous'),function($query){
            return $query->with('user');
        })->findOrFail($id);
        $question->is_owner = $question->isOwner($request->user()->id);
        return response()->json([
            'question' => $question,
        ]);
    }
    public function commentQuestion(Request $request,$id){
        $request->validate([
            'body' => 'required|string|max:2500',
            'type' => 'required|in:comment,solution'
        ]);
        $question = Question::findOrFail($id);
        $message = $question->questionMessages()->create([
            'question_id' => $id,
            'user_id' => $request->user()->id,
            'body' => $request->body,
            'type' => $request->type
        ]);
        $message->load('user');
        return response()->json([
            'message' => 'Commented successfully.',
            'data' => $message,
        ]);
    }
    public function getQuestionMessages(Request $request,$id){
        $question = Question::findOrFail($id);
        $page = $request->query('page',1);
        $perPage = $request->query('perPage',10);
        $sortBy = $request->query('sortBy','created_at,desc');
        [$sorting, $order] = explode(',', $sortBy);
        $type = $request->query('type');
        logger($type);
        $messages = $question->questionMessages()->when($type,function($query) use ($type) {
            return $query->where('type', $type);
        })->with('user')->orderBy($sorting, $order)->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'messages' => $messages,
            'type' => $type ?? 'all'
        ]);
    }

    public function editQuestion(Request $request,$id){
        $this->validateQuestion($request,true);
        $question = Question::findOrFail($id);
        $questionData = $this->getQuestionData($request);
        /*  remove the image if the new image is uploaded or deleted the original one */
        if($request->isRemoveImage){
            if( !empty($question->image_path) && Storage::disk('public')->exists($question->image_path)){
                Storage::disk('public')->delete($question->image_path);
            }
            $questionData['image_path'] = null;
        }
        /*  store the image in the storage */
        if($request->hasFile('image')){
            $image = $request->file('image');
            $questionData['image_path'] = $image->store('questions', 'public');
        }
        $question->update($questionData);
        return response()->json([
            'message' => 'Question updated successfully',
            'question' => $question
        ]);
    }

    public function toggleQuestionLike(Request $request,$id){
        $question = Question::findOrFail($id);
        $is_liked = $question->toggleLike($request->user()->id);
        $question = $question->loadCount(['likedUsers','questionMessages']);
        return response()->json([
            'message' => 'Question liked successfully',
            'question' => $question,
            'is_liked' => $is_liked
        ]);
    }
    public function updateComment(Request $request,$id){
        $request->validate([
            'body' => 'required|string|max:2500',
        ]);
        $message = QuestionMessage::where('user_id',$request->user()->id)->findOrFail($id);
        $message->update([
            'body' => $request->body,
        ]);
        return response()->json([
            'message' => 'Comment updated successfully.',
            'data' => $message->load('user'),
        ]);
    }
}
