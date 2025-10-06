<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Report;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminReportController extends Controller
{
    public function getReports()
    {
        $page = request()->query('page', 1);
        $per_page = request()->query('per_page', 10);
        $type = request()->query('type');
        logger($type);
        $reports = Report::when($type, function ($query) use ($type) {
                return $query->where('reportable_type', $type);
            })
            ->with(['reportable','reporter'])
            ->paginate($per_page, ['*'], 'page', $page);
            logger($reports);
        return response()->json([
            'reports' => $reports,
        ]);
    }

    public function getReportDetail(Request $request,$id){
        $report = Report::with(['reportable','reporter'])->findOrFail($id);
        logger(json_encode($report));
        return response()->json([
            'report' => $report
        ]);
    }

    public function responseToReporter(Request $request){
        $report = Report::findOrFail($request->id);
        $report->update([
            'status' => $request->status
        ]);
        return response()->json([
            'report' => $report->load(['reportable','reporter'])
        ]);
    }

    public function togglePostTemporarily($id){
        $report = Report::with('reportable')->findOrFail($id);
        if($report->reportable->visibility){
            $this->notifyTemporaryTakeDown($report->reportable->user_id,$report->reportable->id);
        }
        if(!$report->reportable->visibility){
            $this->notifyRestorePost($report->reportable->user_id,$report->reportable->id);
        }
        $report->reportable()->update([
            'visibility' => !$report->reportable->visibility
        ]);
        return response()->json([
            'report' => $report->load(['reportable','reporter'])
        ]);
    }
    public function notifyTemporaryTakeDown($id,$postId){
        Notification::create([
            'user_id' => $id,
            'type' => 'POST_REMOVED_TEMPORARY',
            'title' => 'Post Taken Down Temporarily',
            'message' => 'Your post has been taken down temporarily for review. We will notify you once the review is complete.',
            'data' => ['post_id' => $postId],
            'is_read' => false,
        ]);
    }
    public function notifyRestorePost($id,$postId){
        Notification::create([
            'user_id' => $id,
            'type' => 'POST_RESTORED',
            'title' => 'Post Restored',
            'message' => 'Your post has been restored.',
            'data' => ['post_id' => $postId],
            'is_read' => false,
        ]);
    }
    /*             $table->foreignId('user_id')->constrained('users');
            $table->string('type');
            $table->string('title');
            $table->string('message');
            $table->json('data');
            $table->boolean('is_read')->default(false);
            $table->timestamps(); */

/*     public function deletePostPermanently($id){
        $report = Report::findOrFail($id);
        $report->reportable()->delete();
        return response()->json([
            'report' => $report->load(['reportable','reporter'])
        ]);
    } */
}
