<?php

namespace App\Http\Controllers;

use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\Response;


class FavoriteController extends Controller
{
    private FavoriteService $favoriteService;
    public function __construct(FavoriteService $favoriteService){
        $this->favoriteService=$favoriteService;
    }

    public function addFavorite($exhibition_id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->favoriteService->addFavorite($exhibition_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function deleteFavorite($id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->favoriteService->deleteFavorite($id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function showFavorite(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->favoriteService->showFavorite();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

}
