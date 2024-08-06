<?php

namespace App\Http\Controllers;

use App\Http\Requests\addProductRequest;
use App\Http\Requests\SearchCompanyRequest;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Services\companyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Responses\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    protected $compantService;

    public function __construct(companyService $companyService)
    {
        $this->compantService = $companyService;
    }

    public function addProduct(addProductRequest $request): JsonResponse
    {
        $data=[];
        try{
            $data=$this->compantService->addProduct($request->validated());
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function deleteProduct($id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->compantService->deleteProduct($id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function updateProduct(addProductRequest $request, $id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->compantService->updateProduct($request->validated(), $id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showProducts($id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->compantService->showProducts($id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showCompanies(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->compantService->showCompanies();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function searchCompany(SearchCompanyRequest $request): JsonResponse
    {
        $data=[];
        try{
            $data=$this->compantService->searchCompany($request->validated());
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function removeCompany($user_id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->compantService->removeCompany($user_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showRegisterCompanyExhibition(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->compantService->showRegisterCompanyExhibition();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showUnRegisterCompanyExhibition(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->compantService->showUnRegisterCompanyExhibition();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showMyStand($exhibition_id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->compantService->showMyStand($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showCompanyStand($stand_id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->compantService->showCompanyStand($stand_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

}
