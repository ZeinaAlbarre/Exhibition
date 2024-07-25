<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddExhibitionSectionRequest;
use App\Http\Requests\addScheduleRequest;
use App\Http\Requests\EmployeeStatusRequest;
use App\Http\Requests\ExhibitionRequest;
use App\Http\Requests\ExhibitionStatusRequest;
use App\Http\Requests\SearchExhibitionRequest;
use App\Http\Requests\UpdateExhibitionRequest;
use App\Http\Requests\VisitorSiginUpRequst;
use App\Http\Responses\Response;
use App\Services\ExhibitionService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExhibitionController extends Controller
{
    private ExhibitionService $exhibitionService;
    public function __construct(ExhibitionService $exhibitionService){
        $this->exhibitionService=$exhibitionService;
    }
    public function addExhibition(ExhibitionRequest $request): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->addExhibition($request->validated());
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function showExhibitionRequest(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->showExhibitionRequest();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function acceptExhibition($id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->acceptExhibition($id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function rejectExhibition($id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->rejectExhibition($id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function deleteExhibition($id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->deleteExhibition($id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function updateExhibition(UpdateExhibitionRequest $request,$id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->updateExhibition($request->validated(),$id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function showUpdateExhibitions(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->showUpdateExhibitions();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function showUpdateExhibition($id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->showUpdateExhibition($id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function acceptExhibitionUpdate($id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->acceptExhibitionUpdate($id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function rejectExhibitionUpdate($id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->rejectExhibitionUpdate($id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function addExhibitionSection(AddExhibitionSectionRequest $request,$id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->addExhibitionSection($request->validated(),$id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function deleteExhibitionSection($exhibition_id,$section_id)
    {
        $data=[];
        try{
            $data=$this->exhibitionService->deleteExhibitionSection($exhibition_id,$section_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function addExhibitionMedia(Request $request,$exhibition_id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->addExhibitionMedia($request,$exhibition_id);
            return Response::Success($data['data'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function deleteExhibitionMedia($media_id)
    {
        $data=[];
        try{
            $data=$this->exhibitionService->deleteExhibitionMedia($media_id);
            return Response::Success($data['data'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showOrganizerExhibition(){
        $data=[];
        try{
            $data=$this->exhibitionService->showOrganizerExhibition();
            return Response::Success($data['data'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }


    public function showCompanyRequests($exhibition_id)
    {
        try {
            $response = $this->exhibitionService->showCompanyRequests($exhibition_id);
            return Response::Success($response['data'], $response['message']);
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            return Response::Error([], $message);
        }
    }

    public function acceptCompanyRequest($exhibition_id, $company_id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->acceptCompanyRequest($exhibition_id, $company_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function rejectCompanyRequest($exhibition_id, $company_id): JsonResponse
    { $data=[];
        try{
            $data=$this->exhibitionService->rejectCompanyRequest($exhibition_id, $company_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function addSchedule($exhibition_id, addScheduleRequest $request)
    {
        $data=[];
        try{
            $data=$this->exhibitionService->addSchedule($exhibition_id, $request);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function deleteSchedule($schedule_id)
    {
        $data=[];
        try{
            $data=$this->exhibitionService->deleteSschedule($schedule_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function updateSchedule($schedule_id, addScheduleRequest $request)
    {
        $data=[];
        try{
            $data=$this->exhibitionService->updateSchedule($schedule_id,$request);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showSchedule($schedule_id)
    {
        $data=[];
        try{
            $data=$this->exhibitionService->showScheduale($schedule_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showExhibitionSchedule($exhibition_id)
    {
        $data=[];
        try{
            $data=$this->exhibitionService-> showExhibitionScheduale($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function addStand(Request $request, $exhibition_id)
    {
        $data=[];
        try{
            $data=$this->exhibitionService->addStand($request, $exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function updateStand(Request $request, $stand_id)
    {
        $data=[];
        try{
            $data=$this->exhibitionService->updateStand($request, $stand_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function deleteStand($stand_id)
    {
        $data=[];
        try{
            $data=$this->exhibitionService->deleteStand($stand_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showExhibitionStands($exhibition_id)
    {
        $data=[];
        try{
            $data=$this->exhibitionService-> showExhibitionStands($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showEmployeeExhibition(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->showEmployeeExhibition();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function searchExhibition(SearchExhibitionRequest $request): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->searchExhibition($request->validated());
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }


    public function showEndExhibition(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->showEndExhibition();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function showExhibition($id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->showExhibition($id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function showExhibitionSection($section_id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->showExhibitionSection($section_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function showAvailableExhibition(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->showAvailableExhibition();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function showAvailableCompanyExhibition(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->showAvailableCompanyExhibition();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function changeExhibitionStatus(ExhibitionStatusRequest $request,$id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->changeExhibitionStatus($request->validated(),$id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }
    public function changeEmployeeStatus(EmployeeStatusRequest $request): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->changeEmployeeStatus($request->validated());
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }



    public function addSponsor(Request $request, $exhibition_id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->addSponser($request,$exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }


    public function deleteSponsor($sponsor_id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->deleteSponsor($sponsor_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showExhibitionSponsors($exhibition_id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->exhibitionService->showExhibitionSponsors($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }


    public function filter_Exhibition_today()
    {
        $data=[];
        try{
            $data=$this->exhibitionService->filter_Exhibition_today();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function filter_Exhibition_thisWeek()
    {
        $data=[];
        try{
            $data=$this->exhibitionService->filter_Exhibition_thisWeek();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function filter_Exhibition_later()
    {
        $data=[];
        try{
            $data=$this->exhibitionService->filter_Exhibition_later();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showCompany($company_id)
    {
        try {
            $response = $this->exhibitionService->showCompany($company_id);

            return Response::Success($response['data'], $response['message'], $response['code']);
        } catch (\Throwable $th) {
            return Response::Error([], $th->getMessage());
        }
    }
    public function showExhibitionCompany($exhibition_id){
        $data=[];
        try{
            $data=$this->exhibitionService->showExhibitionCompany($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }



}

