<?php

namespace App\Http\Controllers;

use App\Http\Requests\StandBookingRequest;
use App\Http\Requests\CompanyEmployeeRequest;
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

    public function createTicket($exhibition_id)
    {
        $data=[];
        try{
            $data=$this->ticketServices->createTicket($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showQR($exhibition_id)
    {
        $data=[];
        try{
            $data=$this->ticketServices->showQR($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showAvailableStand($exhibition_id)
    {
        $data=[];
        try{
            $data=$this->ticketServices->showAvailableStand($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function standBooking(StandBookingRequest $request)
    {
        $data=[];
        try{
            $data=$this->ticketServices->standBooking($request->validated());
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function payCompanyEmployee(CompanyEmployeeRequest $request,$exhibition_id){
        $data=[];
        try{
            $data=$this->ticketServices->payCompanyEmployee($request->validated(),$exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

}
