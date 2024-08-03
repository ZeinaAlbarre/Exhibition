<?php

namespace App\Services;

use App\Models\Rate;
use Illuminate\Support\Facades\Auth;

class rateService
{

        public function addRate($request, $exhibitionId): array
    {
        $data = [];
        try {
            $rate = Rate::create([
                'user_id'=>Auth::id(),
                'exhibition_id' => $exhibitionId,
                'rate' => request()->rate,
            ]);
            $data = $rate;
            $message = 'Rate added successfully.';
            $code = 200;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

        public function updateRate($request , $rateId): array
    {
        $data = [];
        try {
            $rate = Rate::where('id', $rateId)->first();
            if(!is_null(request()->rate)) {
                $rate->update([
                    'rate' => request()->rate,
                ]);
            }
            $data = $rate;
            $message = 'Rate updated successfully.';
            $code = 200;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

        public function showExhibitionRate($exhibitionId): array
    {
        $data = [];
        try {
            $rates = Rate::query()->where('exhibition_id', $exhibitionId)->where('user_id',Auth::id())->first();
            $data = $rates;
            $message = 'Rates retrieved successfully.';
            $code = 200;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];
    }


}
;
