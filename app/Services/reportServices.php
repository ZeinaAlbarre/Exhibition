<?php

namespace App\Services;

use App\Exports\CompaniesExport;
use App\Exports\ExhibitionCompanyVisitorrExport;
use App\Exports\ExhibitionVisitorExport;
use App\Models\Exhibition_company;
use App\Models\Exhibition_visitor;
use App\Models\Qr;
use App\Models\Rate;
use App\Models\Stand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class reportServices
{
    public function getExhibitionReport($exhibition_id)
    {
        $data=[];
        DB::beginTransaction();
        try {
            $companiesCount = Exhibition_company::where('exhibition_id', $exhibition_id)->count();
            $visitorsCount = Exhibition_visitor::where('exhibition_id', $exhibition_id)->count();
            $standsCount = Stand::where('exhibition_id', $exhibition_id)->count();
            $stands = Stand::where('exhibition_id', $exhibition_id)
                ->orderBy('company_num', 'desc')
                ->take(5)
                ->get();
            $data = [
                'companiesCount' => $companiesCount,
                'standsCount' => $standsCount,
                'visitorsCount' => $visitorsCount,
                'stands' => $stands,
            ];
            $code=200;
            $message='companiesCount and standsCount and visitorsCount and all stands are successfully show';
        }
        catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error. Please try again ';
            $code = 500;
        }
        return ['data' => $data , 'message' => $message, 'code' =>$code];

    }
    public function getExhibitionAverageRating($exhibition_id)
    {
        DB::beginTransaction();

        try {
            $averageRating = Rate::query()
                ->where('exhibition_id', $exhibition_id)
                ->avg('rate');
            if ($averageRating === null) {
                $averageRating = 0;
            }
            $percentageRating = ($averageRating / 5) * 100;
            DB::commit();
            $message = 'Average rating calculated successfully.';
            $code = 200;
            return ['data' => ['percentage_rating' => $percentageRating], 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during calculating average rating. Please try again.';
            $code = 500;
            return ['data' => [], 'message' => $message, 'code' => $e->getCode()];
        }
    }
    public function ExcelCompanyAppReport(){
        DB::beginTransaction();
        try {
            $filePath = Excel::store(new CompaniesExport(), 'reports/users.xlsx');
            return ['data' => $filePath, 'message' => 'Download complete successfully', 'code' => 200];
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Error .';
            $code = 500;
            return ['data' => [], 'message' => $message, 'code' => $e->getCode()];

        }
    }
    public function ExcelVisitorAppReport(){
        DB::beginTransaction();
        try {
            $filePath = Excel::store(new CompaniesExport(), 'reports/users.xlsx');
            return ['data' => $filePath, 'message' => 'Download complete successfully', 'code' => 200];
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Error .';
            $code = 500;
            return ['data' => [], 'message' => $message, 'code' => $e->getCode()];

        }
    }
    public function ExcelVisitorExhibitionReport($exhibitionId){
        DB::beginTransaction();
        try {
            $filePath = Excel::store(new ExhibitionVisitorExport($exhibitionId), 'reports/visitors_exhibition_' . $exhibitionId . '.xlsx');
            return ['data' => $filePath, 'message' => 'Download complete successfully', 'code' => 200];
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Error .';
            $code = 500;
            return ['data' => [], 'message' => $e->getMessage(), 'code' => $e->getCode()];

        }
    }

    public function ExcelVisitorCompanyExhibitionReport($exhibitionId){
        DB::beginTransaction();
        try {
            $filePath = Excel::store(new ExhibitionCompanyVisitorrExport($exhibitionId), 'reports/companies_visitors_exhibition_' . $exhibitionId . '.xlsx');
            return ['data' => $filePath, 'message' => 'Download complete successfully', 'code' => 200];
        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Error .';
            $code = 500;
            return ['data' => [], 'message' => $e->getMessage(), 'code' => $e->getCode()];

        }
    }

}
