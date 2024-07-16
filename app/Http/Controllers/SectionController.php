<?php

namespace App\Http\Controllers;

use App\Http\Responses\Response;
use App\Models\Section;
use App\Services\SectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    private SectionService $sectionService;
    public function __construct(SectionService $sectionService){
        $this->sectionService=$sectionService;
    }


    public function addSection(Request $request):JsonResponse
    {
        $data=[];
        try{
            $data=$this->sectionService->addSection($request);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function deleteSection($section_id)
    {
        $data=[];
        try{
            $data=$this->sectionService->deleteSection($section_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }
    public function showSections()
    {
        $data=[];
        try{
            $data=$this->sectionService->showSections();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }
}
