<?php

namespace App\Http\Controllers;

use App\Http\Requests\addCategoryRequest;
use App\Http\Responses\Response;
use App\Models\Category;
use App\Models\Exhibition_category;
use App\Services\categoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    private CategoryService $categoryService;
    public function __construct(categoryService $categoryService){
        $this->categoryService=$categoryService;
    }


    public function addCategory(addCategoryRequest $request, $exhibition_id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->categoryService->addCategory($request->validated(), $exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function deleteCategory($category_id){
        $data=[];
        try{
            $data=$this->categoryService->deleteCategory($category_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function showExhibitionCategory($exhibition_id){
        $data=[];
        try{
            $data=$this->categoryService->showExhibitionCategory($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }
}
