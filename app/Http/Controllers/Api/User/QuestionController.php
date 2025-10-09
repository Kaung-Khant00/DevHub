<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
    private function validateQuestion(Request $request){
        $request->validate([
            'title'=>'required|string|max:255',
            'body'=>'required|string|max:5000',
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
            'title' => $request->title,
            'body' => $request->body,
            'code_snippet' => $request->code_snippet,
            'is_anonymous' => $request->is_anonymous ,
            'is_solved' => false,
            'tags' => $request->tags
        ];
    }
}
