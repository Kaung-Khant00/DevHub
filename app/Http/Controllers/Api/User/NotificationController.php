<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $user = $request->user();
        $type = $request->query('type');
        $notifications = $user
            ->notifications()
            ->when($type, function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->get();
        return response()->json([
            'message' => 'Notifications retrieved successfully.',
            'notifications' => $notifications,
        ]);
    }
    public function getNotificationById(Request $request,$id){
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();
        if($notification->user_id === $request->user()->id){
            $post = $this->postFromNotification($notification);
            logger($post);
            if($post){
                $notification->post = $post;
            }
        }
        return response()->json([
            'message' => 'Notification retrieved successfully.',
            'notification' => $notification,
        ]);
    }
    private function postFromNotification($notification){
        if(($notification->type === 'POST_REMOVED_TEMPORARY' || $notification->type === 'POST_RESTORED') && isset($notification->data['post_id'])){
            return Post::withoutGlobalScopes()->with('user')->find($notification->data['post_id']);
        }
        return null;
    }
    public function updateNotificationReadStatus(Request $request, $id){
        $request->user()->notifications()->where('id', $id)->update([
            'is_read' => true]);
        return response()->json([
            'message'=> 'Notification updated successfully.',
        ]);
    }
    public function updateNotificationAllReadStatus(Request $request){
        $request->user()->notifications()->where('is_read', false)->update([
            'is_read'=> true
        ]);
        return response()->json([
            'message'=> 'All Notifications read successfully.',
        ]);
    }
    public function deleteNotification(Request $request,$id){
        $request->user()->notifications()->where('id', $id)->delete();
        return response()->json([
            'message'=> 'Notifications deleted successfully.',
        ]);
    }
    public function deleteAllReadNotification(Request $request){
        $request->user()->notifications()->where('is_read', true)->delete();
        return response()->json([
            'message'=> 'All Read Notifications deleted successfully.',
        ]);
    }
}
