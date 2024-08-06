<?php

namespace App\Http\Controllers;

use App\Http\Requests\StandBookingRequest;
use App\Http\Requests\CompanyEmployeeRequest;
use App\Models\Qr;
use App\Services\TicketServices;
use Illuminate\Http\Request;
use App\Http\Responses\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;


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

    public function createCompanyExhibitionRequest($exhibition_id)
    {
        $data=[];
        try{
            $data=$this->ticketServices->createCompanyExhibitionRequest($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showCompaniesExhibitionRequest($exhibition_id)
    {
        $data=[];
        try{
            $data=$this->ticketServices->showCompaniesExhibitionRequest($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function acceptCompanyExhibitionRequest($user_id,$exhibition_id)
    {
        $data=[];
        try{
            $data=$this->ticketServices->acceptCompanyExhibitionRequest($user_id,$exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function rejectCompanyExhibitionRequest($user_id,$exhibition_id)
    {
        $data=[];
        try{
            $data=$this->ticketServices->rejectCompanyExhibitionRequest($user_id,$exhibition_id);
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

    public function validateTicket(Request $request)
    {
        $data=[];
        try{
            $data=$this->ticketServices->validateTicket($request);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function ScanExit(Request $request)
    {
        $data=[];
        try{
            $data=$this->ticketServices->ScanExit($request);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showStandInfo($stand_id)
    {
        $data=[];
        try{
            $data=$this->ticketServices->showStandInfo($stand_id);
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

    public function countStandCompany($stand_id)
    {
        $data=[];
        try{
            $data=$this->ticketServices->countStandCompany($stand_id);
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
