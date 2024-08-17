<?php

namespace App\Http\Controllers;

use App\Http\Responses\Response;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Exhibition_visitor;
use App\Models\Rate;
use App\Models\Stand;
use App\Services\FavoriteService;
use App\Services\reportServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Report extends Controller
{
    private reportServices $reportService;
    public function __construct(reportServices $reportServices){
        $this->reportService=$reportServices;
    }

    public function getExhibitionReport($exhibition_id)
    {

        $data=[];
        try{
            $data=$this->reportService->getExhibitionReport($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }


    public function getExhibitionAverageRating($exhibition_id)
    {
        $data=[];
        try{
            $data=$this->reportService->getExhibitionAverageRating($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function ExcelVisitorAppReport()
    {
        $data=[];
        try{
            $data=$this->reportService->ExcelVisitorAppReport();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }
    public function ExcelVisitorExhibitionReport($exhibition_id)
    {
        $data=[];
        try{
            $data=$this->reportService->ExcelVisitorExhibitionReport($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }
    public function ExcelVisitorCompanyExhibitionReport($exhibition_id)
    {
        $data=[];
        try{
            $data=$this->reportService->ExcelVisitorCompanyExhibitionReport($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }
    public function standMoneyReport($exhibition_id)
    {
        $data=[];
        try{
            $data=$this->reportService->standMoneyReport($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }
    public function AgeVisitor($exhibition_id)
    {
        $data=[];
        try{
            $data=$this->reportService->AgeVisitor($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }
    public function AgeAppVisitor()
    {
        $data=[];
        try{
            $data=$this->reportService->AgeAppVisitor();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }


    public function financialStudyReport($exhibition_id){
        $data=[];
        try{
            $data=$this->reportService->financialStudyReport($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }


}
