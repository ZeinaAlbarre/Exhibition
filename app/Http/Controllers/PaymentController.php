<?php

namespace App\Http\Controllers;

use App\Http\Requests\addPaymentRequest;
use App\Http\Responses\Response;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService){
        $this->paymentService=$paymentService;
    }

    public function addMoney(addPaymentRequest $request, $user_id)
    {
        $data=[];
        try{
            $data=$this->paymentService->addMoney($request->validated(), $user_id);
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showMoney()
    {
        $data=[];
        try{
            $data=$this->paymentService->showMoney();
            return Response::Success($data['data'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }
}
