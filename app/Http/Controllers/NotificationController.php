<?php

namespace App\Http\Controllers;
use App\Services\NotificationService;
use Illuminate\Http\Request;




class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }



    public function send(Request $request)
    {


        $token=$request->input('token');
        $title=$request->input('title');
        $body=$request->input('body');
        $data=$request->input('data',[]);
        $response=$this->notificationService->send($token,$title,$body,$data);
            return response()->json(['success' => true,$response]);

    }


}
