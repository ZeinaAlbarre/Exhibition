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
            $message = 'Error during ticket booking Request. Please try again ';
            $code = 500;
        }
        return ['data' => $data , 'message' => $message, 'code' =>$code];

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
        $qrCode = $request->input('qr_code');
        $ticket = Ticket::where('qr_code', $qrCode)->first();
        if ($ticket && !$ticket->is_used) {
            $ticket->update(['is_used' => true]);
            return response()->json(['status' => 'success', 'message' => 'Ticket is valid.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Ticket is invalid or already used.']);
        }
    }

    public function showStandInfo($stand_id)
    {
        DB::beginTransaction();
        try {
            $standCompanies=Company_stand::query()->where('stand_id',$stand_id)->with('stand','company')->orderBy('stand_price','desc')->get();
            DB::commit();
            $data = $standCompanies;
            $message = 'Company stand show successfully .';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing exhibition stands. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

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

