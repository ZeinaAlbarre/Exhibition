<?php

namespace App\Http\Controllers;

use App\Http\Requests\addProductRequest;
use App\Services\companyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Responses\Response;
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

}
