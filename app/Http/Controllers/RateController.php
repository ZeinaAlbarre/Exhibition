<?php

namespace App\Http\Controllers;

use App\Http\Responses\Response;
use App\Services\rateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RateController extends Controller
{
    protected $rateService;

    public function __construct(RateService $rateService)
    {
        $this->rateService = $rateService;
    }

    public function addRate(Request $request, $exhibitionId): JsonResponse
    {
        $data=[];
        try{
            $data=$this->rateService->addRate($request, $exhibitionId);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function updateRate(Request $request, int $rateId): JsonResponse
    {
        $data=[];
        try{
            $data=$this->rateService->updateRate($request, $rateId);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showExhibitionRate(int $exhibitionId): JsonResponse
    {
        $data=[];
        try{
            $data=$this->rateService->showExhibitionRate($exhibitionId);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showUserExhibitionRate(int $exhibitionId): JsonResponse
    {
        $data=[];
        try{
            $data=$this->rateService->showUserExhibitionRate($exhibitionId);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

}
