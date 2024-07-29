<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function addMoney($request,$user_id){
        DB::beginTransaction();
        try {
            $user=User::query()->find($user_id);
            if($user){
                $payment=Payment::query()->create([
                    'user_id'=>$user_id,
                    'amount'=>$request['amount'],
                ]);
                DB::commit();
                $data = $payment;
                $message = 'The money has been successfully added to the user account. ';
                $code = 200;

            }else{
                DB::commit();
                $data = [];
                $message = 'User not found';
                $code = 404;
            }
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during adding money Request. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $e->$message, 'code' => $code];

        }
    }

    public function showMoney(){
        DB::beginTransaction();
        try {
            $user=Auth::user()->id;
            $payment=Payment::query()->where('user_id',$user)->first();
            if($payment){
                DB::commit();
                $data = $payment;
                $message = 'your financial accout has been successfully displayed. ';
                $code = 200;
            }else{
                DB::commit();
                $data = [];
                $message = 'You have not created an financial account yet. ';
                $code = 200;
            }
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing your money amount. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }
}
