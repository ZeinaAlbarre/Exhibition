<?php

namespace App\Http\Responses;

class Response
{
    public static function Success($data,$message,$code=200){
        return response()->json([
            'status'=>$code,
            'data'=>$data,
            'message'=>$message
        ],200);
    }
    public static function Error($data,$message,$code=500){
        return response()->json([
            'status'=> $code,
            'data'=>$data,
            'message'=>$message
        ],200);
    }
    public static function Validation($data,$message,$code=422){
        return response()->json([
            'status'=>$code,
            'data'=>$data,
            'message'=>$message
        ],200);
    }
}
