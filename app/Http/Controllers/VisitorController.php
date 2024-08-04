<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchVisitorRequest;
use App\Services\VisitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Responses\Response;


class VisitorController extends Controller
{
    protected $visitorService;

    public function __construct(VisitorService $visitorService)
    {
        $this->visitorService = $visitorService;
    }

    public function showVisitors()
    {
        $data=[];
        try{
            $data=$this->visitorService->showVisitors();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function searchVisitor(SearchVisitorRequest $request)
    {
        $data=[];
        try{
            $data=$this->visitorService->searchVisitor($request->validated());
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function removeVisitor($user_id)
    {
        $data=[];
        try{
            $data=$this->visitorService->removeVisitor($user_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }
}
