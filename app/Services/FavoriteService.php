<?php

namespace App\Services;

use App\Models\Exhibition;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoriteService
{
    public function addFavorite($exhibition_id): array
    {
        DB::beginTransaction();
        try {
            $user=Auth::user()->id;
            $exhibition=Exhibition::query()->find($exhibition_id);
            $favorite=Favorite::query()->create([
                'exhibition_id'=>$exhibition_id,
                'user_id'=>$user,
            ]);
            DB::commit();
            $message = 'Favorite added successfully. ';
            $code = 200;
            return ['data' => $favorite, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during added favorite. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }

    }

    public function deleteFavorite($id): array
    {
        DB::beginTransaction();
        try {
            $favorite=Favorite::query()->find($id);
            $favorite->delete();
            DB::commit();
            $data = [];
            $message = 'Favorite deleted successfully. ';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during deleted favorite. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }

    }

    public function showFavorite(): array
    {
        DB::beginTransaction();
        try {
            $user=Auth::user()->id;
            $favorite=Favorite::query()->where('user_id',$user)->with('exhibition')->get();
            DB::commit();
            $data = $favorite;
            $message = 'Favorites shown successfully. ';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during show favorites. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }

    }
}
