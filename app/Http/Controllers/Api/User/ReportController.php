<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Post;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function reportPost($postId, Request $request){
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        $post = Post::findOrFail($postId);
        $report = Report::create([
            'reported_type' => 'post',
            'reporting_id' => $postId,
            'reporter_id' => $request->user()->id,
            'reason' => $request->reason,
        ]);
        return response()->json([
            'message' => 'Reported successfully.',
            'report' => $report,
        ]);
    }
}

/*
            $table->id();
            $table->enum('reported_type',['user','post','group','question','answer','simple']);
            $table->integer('reported_id')->nullable();
            $table->foreignId('reporter_id')->constrained('users');
            $table->text('reason');
            $table->enum('status',['pending','resolved'])->default('pending');
            $table->timestamps();
        });
*/
