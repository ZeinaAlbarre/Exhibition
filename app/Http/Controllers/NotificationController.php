<?php

namespace App\Http\Controllers;
use App\Models\Exhibition;
use App\Models\Exhibition_visitor;
use App\Models\Scheduale;
use App\Models\User;
use App\Notifications\beforStartExhibition;
use App\Notifications\schedualeNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{



    public function send(Request $request)
    {


        $token=$request->input('token');
        $title=$request->input('title');
        $body=$request->input('body');
        $data=$request->input('data',[]);
        $response=$this->notificationService->send($token,$title,$body,$data);
            return response()->json(['success' => true,$response]);

    }

    public function showUnreadNotifications()
    {
        $unreadNotifications = Auth::user()->unreadNotifications;

        return response()->json(['data'=>$unreadNotifications]);
    }


    public function markNotificationAsRead($notificationId)
    {

        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();

        if ($notification) {

            $notification->markAsRead();

            return response()->json(['message' => 'Notification marked as read successfully!']);
        }

        return response()->json(['message' => 'Notification not found!'], 404);
    }
    public function markAllAsRead()
    {
        // Mark all unread notifications as read
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read successfully!']);
    }

}
