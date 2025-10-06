<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Report;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminReportController extends Controller
{
    public function getReports(Request $request)
    {
        $page = $request->query('page', 1);
        $per_page = $request->query('per_page', 10);
        $type = $request->query('type');
        $status = $request->query('status');
        logger($status);
        $reports = Report::when($type, function ($query) use ($type) {
                return $query->where('reportable_type', $type);
            })
            ->with(['reportable','reporter'])
            ->when($status,function ($query) use ($status){
                return $query->where('status', $status);
            })
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
    public function deletePostPermanently($id){
        $report = Report::findOrFail($id);
        $report->reportable()->delete();
        Notification::create([
            'user_id' => $report->reportable->user_id,
            'type' => 'POST_DELETED_PERMANENTLY',
            'title' => 'Post Removed by Moderation Team',
            'message' => 'Your post has been permanently removed following a content review for violating our community guidelines.',
            'data' => ['post_id' => $report->reportable->id],
            'is_read' => false,
        ]);
        $report->update(['status'=>'resolved']);
        return response()->json([
            'report' => $report->load(['reportable','reporter'])
        ]);
    }
    public function notifyOwner(Request $request){
        logger($request->all());
        $this->validateNotification($request);
        Notification::create([
            'user_id' => $request->user_id,
            'type' => 'POST_REPORTED',
            'title' => $request->title,
            'message' => $request->message,
            'data' => ['post_id' => $request->post_id],
            'is_read' => false,
        ]);
        return response()->json([
            'message' => 'Notification sent successfully.'
        ]);
    }
    private function validateNotification(Request $request){
        return $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'message' => 'required|string',
            'post_id' => 'required|exists:posts,id',
        ]);
    }
}
