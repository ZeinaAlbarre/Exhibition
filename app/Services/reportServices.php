<?php

namespace App\Services;

use App\Exports\CompaniesExport;
use App\Exports\ExhibitionCompanyVisitorrExport;
use App\Exports\ExhibitionVisitorExport;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Exhibition_visitor;
use App\Models\Qr;
use App\Models\Rate;
use App\Models\Stand;
use App\Models\Visitor;
use Carbon\Carbon;
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

    public function AgeVisitor($exhibition_id)
    {
        DB::beginTransaction();
        try {
            $users = Qr::query()
                ->where('exhibition_id', $exhibition_id)
                ->get();
            $ageGroups = [
                '0-18' => 0,
                '19-25' => 0,
                '26-35' => 0,
                '36-45' => 0,
                '46-55' => 0,
                '56+' => 0,
            ];
            foreach ($users as $user) {
                if ($user->userable_type === 'App\Models\Visitor') {
                    $visitor = Visitor::find($user->userable_id);
                    if ($visitor) {
                        $birthDate = Carbon::parse($visitor->birth_date);
                        $age = Carbon::now()->diffInYears($birthDate);
                        if ($age <= 18) {
                            $ageGroups['0-18']++;
                        } elseif ($age <= 25) {
                            $ageGroups['19-25']++;
                        } elseif ($age <= 35) {
                            $ageGroups['26-35']++;
                        } elseif ($age <= 45) {
                            $ageGroups['36-45']++;
                        } elseif ($age <= 55) {
                            $ageGroups['46-55']++;
                        } else {
                            $ageGroups['56+']++;
                        }
                    }
                }
            }
            DB::commit();
            return [
                'data' => $ageGroups,
                'message' => 'Age distribution successfully retrieved',
                'code' => 200
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'data' => [],
                'message' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    public function AgeAppVisitor()
    {
        DB::beginTransaction();
        try {
            $visitor=Visitor::all();

            $ageGroups = [
                '0-18' => 0,
                '19-25' => 0,
                '26-35' => 0,
                '36-45' => 0,
                '46-55' => 0,
                '56+' => 0,
            ];
            foreach ($visitor as $user) {
                $birthDate = Carbon::parse($user->birth_date);
                $age = Carbon::now()->diffInYears($birthDate);
                if ($age <= 18) {
                    $ageGroups['0-18']++;
                } elseif ($age <= 25) {
                    $ageGroups['19-25']++;
                } elseif ($age <= 35) {
                    $ageGroups['26-35']++;
                } elseif ($age <= 45) {
                    $ageGroups['36-45']++;
                } elseif ($age <= 55) {
                    $ageGroups['46-55']++;
                } else {
                    $ageGroups['56+']++;
                }
            }
            DB::commit();
            return [
                'data' => $ageGroups,
                'message' => 'Age distribution successfully retrieved',
                'code' => 200
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'data' => [],
                'message' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    public function standMoneyReport($exhibition_id){
        DB::beginTransaction();
        try {
            $stands = Stand::query()->where('exhibition_id',$exhibition_id)
                ->join('company_stands', 'stands.id', '=', 'company_stands.stand_id')
                ->select('stands.name as stand_name', 'stands.price as original_price', 'company_stands.stand_price as bid_price', 'company_stands.company_id as company_id')
                ->where('company_stands.stand_price', '>', 'stands.price')
                ->orderBy('company_stands.stand_price', 'desc')
                ->limit(5)
                ->get();
            DB::commit();
            $data = $stands;
            $message = '';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing exhibition Request. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }
    
    public function financialStudyReport($exhibition_id){
        DB::beginTransaction();
        try {

            $exhibitionVisitor = Exhibition_visitor::query()->where('exhibition_id', $exhibition_id)->count();
            $ticketPrice =Exhibition::query()->where('id', $exhibition_id)->value('price');
            $totalRevenue = $exhibitionVisitor * $ticketPrice;


            $totalStandPrice =Stand::query()->where('exhibition_id', $exhibition_id)->sum('price');


            $organizerFees = 300;


            $netProfit = ($totalRevenue + $totalStandPrice) - $organizerFees;


            if ($netProfit > 0) {
                $message = "Profit: The exhibition generated a profit of " . $netProfit ;
            } else {
                $message = "Loss: The exhibition incurred a loss of " . abs($netProfit) ;
            }
            $data=[
                'total ticket Revenue'=>$totalRevenue,
                'totalStandPrice' =>$totalStandPrice,
                'organizerFees'=>$organizerFees

            ];

            DB::commit();
            return ['data' => $data, 'message' => $message, 'code' => 200];

        } catch (\Exception $e) {
            DB::rollback();
            $message = 'Error.';
            $code = 500;
            return ['data' => [], 'message' => $message, 'code' => $e->getCode()];
        }
    }

}
