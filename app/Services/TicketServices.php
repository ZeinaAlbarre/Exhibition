<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Company_stand;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Exhibition_visitor;
use App\Models\Payment;
use App\Models\Qr;
use App\Models\Stand;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Ticket;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;


class TicketServices
{
    public function createTicket($exhibition_id): array
    {
        DB::beginTransaction();
        try {
            $user=Auth::user();
            $exhibition=Exhibition::query()->findOrFail($exhibition_id);
            $is_exist= Exhibition_visitor::query()->where('exhibition_id',$exhibition_id)
                ->where('user_id',$user->id)->first();
                if(!is_null($is_exist))
                {
                    $data=[];
                    $message='You have previously registered for this exhibition';
                    $code=400;
                    return ['data' => $data , 'message' => $message, 'code' =>$code];
                }
                $payment=Payment::query()->where('user_id',$user->id)->first();
                if(!$payment){
                    $data=[];
                    $message='You have not created an financial account yet. ';
                    $code=400;
                    return ['data' => $data , 'message' => $message, 'code' =>$code];
                }
                if($payment['amount'] >= $exhibition['price']){
                    $payment['amount'] -= $exhibition['price'] ;
                    $payment->save();
                }
                else
                {
                    $data=[];
                    $message='You don not have enough money to register please check that you have '. $exhibition['price'] .' SYP in your financial account';
                    $code=400;
                    return ['data' => $data , 'message' => $message, 'code' =>$code];
                }
                $exhibitionVisitor = Exhibition_visitor::create([
                    'exhibition_id' => $exhibition_id,
                    'user_id' => $user->id,
                ]);
                $qrCodeData = $exhibitionVisitor->id . '-' . now()->timestamp;
                $qrCode = QrCode::format('png')->size(300)->generate($qrCodeData);
                $qrCodePath = 'qrcodes/' . $qrCodeData . '.png';
                Storage::disk('public')->put($qrCodePath, $qrCode);
                $qr=Qr::create([
                    'user_id' => $user->id,
                    'exhibition_id' => $exhibition_id,
                    'url' => $qrCodeData,
                    'img' => $qrCodePath
                ]);
                DB::commit();
                $data =[$exhibitionVisitor,$qr];
                $message='you are registered successfully to exhibition';
                $code=200;

        }
        catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = '';
            $code = 500;
        }
        return ['data' => $data , 'message' => $message, 'code' =>$code];

    }

    public function createCompanyExhibitionRequest($exhibition_id){
        DB::beginTransaction();
        try {
            $user=Auth::user();
            $company_exhibition=Exhibition_company::query()->create([
                'user_id'=>$user->id,
                'exhibition_id'=>$exhibition_id,
                'status'=>0
            ]);
            DB::commit();
            $data = $company_exhibition;
            $message = 'The request sent successfully. ';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during sending company exhibition request. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function showCompaniesExhibitionRequest($exhibition_id){
        DB::beginTransaction();
        try {
            $exhibition_company=Exhibition_company::query()->where('exhibition_id',$exhibition_id)
                ->where('status',0)->pluck('user_id');
            $user=User::query()->whereIn('id',$exhibition_company)->with('userable')->get();
            DB::commit();
            $data=$user;
            $message = 'Companies request has been shown successfully. ';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing company Request. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $e->getMessage(), 'code' => $code];

        }
    }

    public function acceptCompanyExhibitionRequest($user_id,$exhibition_id)
    {
        DB::beginTransaction();
        try {
            $companyExhibition=Exhibition_company::query()->where('user_id',$user_id)
                ->where('exhibition_id',$exhibition_id)->first();
            $companyExhibition['status']=1;
            $companyExhibition->save();
            DB::commit();
            $data = $companyExhibition;
            $message = 'The company request accepted successfully';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during accepting company Request. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function rejectCompanyExhibitionRequest($user_id,$exhibition_id)
    {
        DB::beginTransaction();
        try {
            $companyExhibition=Exhibition_company::query()->where('user_id',$user_id)
                ->where('exhibition_id',$exhibition_id)->first();
            $companyExhibition->delete();
            DB::commit();
            $data = [];
            $message = 'The company request rejected successfully';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during rejecting company Request. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function showQR($exhibition_id)
    {
        DB::beginTransaction();
        try {
            $user=Auth::user()->id;
            $qr = Qr::query()->where('user_id',$user)
                ->where('exhibition_id',$exhibition_id)->first();
            if($qr){
                $data=$qr;
                $message = 'qr has been shown successfully';
                $code = 200;
            }
            else{
                $data=[];
                $message = '';
                $code=200;
            }
        }
        catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing qr. Please try again ';
            $code = 500;
        }
        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

    public function validateTicket($request)
    {
        DB::beginTransaction();
        try {
            $qrCode = request()->qr_code;
            $qr = Qr::query()->where('url', $qrCode)->first();
            if ($qr && !$qr->is_used) {
                $qr->update([
                    'is_used' => true,
                    'Attended'=> 1
                    ]);
                $code = 200;
                $message = 'Ticket is valid.';
                $data=$qr;
            }
            else {
                $data=[];
                $code = 200;
                $message = 'Ticket is invalid or already used.';
            }
        }
        catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error. Please try again ';
            $code = 500;
        }
        return ['data' => $data , 'message' => $message, 'code' =>$code];
    }

    public function ScanExit($request)
    {
        DB::beginTransaction();
        try {
            $qrCode = request()->qr_code;
            $qr = Qr::query()->where('url', $qrCode)->first();
            if ($qr) {
                $qr->update([
                    'is_used' => false,
                ]);
                $code = 200;
                $message = 'visitor can out Exhibition.';
                $data=$qr;
            }
            else {
                $data=[];
                $code = 200;
                $message = 'Ticket is invalid.';
            }
        }
        catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error. Please try again ';
            $code = 500;
        }
        return ['data' => $data , 'message' => $message, 'code' =>$code];
    }

    public function showStandInfo($stand_id)
    {
        DB::beginTransaction();
        try {
            $standCompany=Company_stand::query()->where('stand_id',$stand_id)->where('status',1)->with('stand','company')->first();
            $standCompanies=Company_stand::query()->where('stand_id',$stand_id)->where('status',0)->with('stand','company')->orderBy('stand_price','desc')->get();
            if($standCompany){
                DB::commit();
                $data = $standCompany;
                $message = 'This stand is accepted already. ';
                $code = 200;
            }
            else
            {
                DB::commit();
                $data = $standCompanies;
                $message = 'Company stand has been show successfully .';
                $code = 200;
            }
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing exhibition stands. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $e->getMessage(), 'code' => $code];

        }
    }

    public function standBooking($request): array
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $stands = $request['stands'];
            $payment = Payment::query()->where('user_id', $user->id)->first();

            if (!$payment) {
                DB::commit();
                $data = [];
                $message = 'You have not created an financial account yet. ';
                $code = 400;
                return ['data' => $data, 'message' => $message, 'code' => $code];
            }

            $companyS = [];
            $unavailableStands = [];

            foreach ($stands as $item) {
                $company_stand = Company_stand::query()->where('company_id', $user['userable_id'])->where('stand_id', $item['id'])->first();
                $stand = Stand::query()->where('id', $item['id'])->first();

                if ($payment['amount'] < $item['stand_price']) {
                    $unavailableStands[] = $stand['name'];
                } else {
                    if ($company_stand) {
                        $company_stand->update([
                            'company_id' => $user['userable_id'],
                            'stand_id' => $item['id'],
                            'stand_price' => $item['stand_price'],
                        ]);
                        $companyS[] = $company_stand;
                    } else {
                        $companyS[] = Company_stand::query()->create([
                            'company_id' => $user['userable_id'],
                            'stand_id' => $item['id'],
                            'stand_price' => $item['stand_price'],
                        ]);
                    }
                }
            }

            DB::commit();
            if (count($unavailableStands) ==1) {
                $message = 'We can not book for you ' . implode(', ', $unavailableStands) . ' stand because you do not have enough money for it but the other stand you enter booked successfully.';
            }
            else if(count($unavailableStands) >1){
                $message = 'We can not book for you ' . implode(' and ', $unavailableStands) . ' stands because you do not have enough money for them but the other stand you enter booked successfully.';
            }
            else {
                $message = 'The stand has been successfully booked';
            }

            $data = $companyS;
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

    public function countStandCompany($stand_id){
        DB::beginTransaction();
        try {
            $company=Company_stand::query()->where('stand_id',$stand_id)->get();
            DB::commit();
            $data = count($company);
            $message = 'The Company who booked this stand has been shown successfully. ';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during count company stand. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];
        }
    }

    public function payCompanyEmployee($request,$exhibition_id){

        DB::beginTransaction();
        try {
            $user=Auth::user();
            $exhibition=Exhibition::query()->findOrFail($exhibition_id);
            $payment=Payment::query()->where('user_id',$user->id)->first();
            if(!$payment){
                $data=[];
                $message='You have not created an financial account yet. ';
                $code=200;
                return ['data' => $data , 'message' => $message, 'code' =>$code];
            }
            if($payment['amount'] >= $exhibition['price'] * $request['num']){
                $payment['amount'] -= $exhibition['price'] * $request['num'] ;
                $payment->save();
            }
            else
            {
                $data=[];
                $message='You don not have enough money to register please check that you have '. $exhibition['price'] * $request['num'] .' SYP in your financial account';
                $code=200;
                return ['data' => $data , 'message' => $message, 'code' =>$code];
            }
            DB::commit();
            $data = [];
            $message = 'The company employee have been successfully paid. ';
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

}

