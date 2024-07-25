<?php

namespace App\Http\Controllers;

use App\Services\TicketServices;
use Illuminate\Http\Request;
use App\Http\Responses\Response;
use Illuminate\Http\JsonResponse;


class TicketController extends Controller
{
    private TicketServices $ticketServices;
    public function __construct(TicketServices $ticketServices){
        $this->ticketServices=$ticketServices;
    }

    public function createTicket($exhibition_id, $user_id)
    {
        $data=[];
        try{
            $data=$this->ticketServices->createTicket($exhibition_id, $user_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }



    }
    public function showQR(Request  $request)
    {
        $data=[];
        try{
            $data=$this->ticketServices->showQR($request);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }
}
