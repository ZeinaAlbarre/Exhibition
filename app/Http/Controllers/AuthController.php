<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanySiginUpRequst;
use App\Http\Requests\CompanyUpdateRequest;
use App\Http\Requests\EmployeeSiginUpRequst;
use App\Http\Requests\OrganizerSiginUpRequst;
use App\Http\Requests\UserCodeCheckRequest;
use App\Http\Requests\UserForgetPasswordRequest;
use App\Http\Requests\UserResetPasswordRequest;
use App\Http\Requests\UserSignInRequest;
use App\Http\Requests\VisitorSiginUpRequst;
use App\Http\Responses\Response;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    private UserService $userService;
    public function __construct(UserService $userService){
        $this->userService=$userService;
    }
    public function visitor_register(VisitorSiginUpRequst $request): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->visitor_register($request->validated());
            return Response::Success($data['user'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }


    }


    public function company_register(CompanySiginUpRequst $request): JsonResponse
    {
        $data=[];

        try{
            $data=$this->userService->company_register($request->validated());
            return Response::Success($data['user'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function organizer_register(OrganizerSiginUpRequst $request)
    {
        $data=[];
        try{
            $data=$this->userService->organizer_register($request->validated());
            return Response::Success($data['user'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }


    public function add_employee(EmployeeSiginUpRequst $request): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->add_employee($request->validated());
            return Response::Success($data['user'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function delete_employee($id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->delete_employee($id);
            return Response::Success($data['user'],$data['message']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }


    public function code_check_verification(UserCodeCheckRequest $request,$id)
    {
        $data=[];
        try{
            $data=$this->userService->code_check_verification($request->validated(),$id);
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function refresh_code($id)
    {
        $data=[];
        try{
            $data=$this->userService->refresh_code($id);
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }


    public function login(UserSignInRequest $request): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->login($request);
            if ($data['user']!=null)
            return Response::Success($data['user'],$data['message'],$data['code']);
            else
                return Response::Error($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }


    public function logout(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->logout();
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }


    public function UserForgotPassword(UserForgetPasswordRequest $request): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->forgot_password($request->validated());
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function UserCodeCheck(UserCodeCheckRequest $request,$user_id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->code_check($request->validated(),$user_id);
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function UserResetPassword(UserResetPasswordRequest $request,$id): JsonResponse
    {

        $data=[];
        try{
            $data=$this->userService->reset_password($request->validated(),$id);
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function reset_visitor_password(UserResetPasswordRequest $request): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->reset_visitor_password($request->validated());
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }
    public function showCompanyRegisterRequest(): JsonResponse
    {

        $data=[];
        try{
            $data=$this->userService->showCompanyRegisterRequest();
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }

    }

    public function accept_company($id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->accept_company($id);
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function reject_company($id): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->reject_company($id);
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function deleteAccount(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->deleteAccount();
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showProfile(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->showProfile();
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function updateCompanyProfile(CompanyUpdateRequest $request): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->updateCompanyProfile($request->validated());
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function showEmployee(): JsonResponse
    {
        $data=[];
        try{
            $data=$this->userService->showEmployee();
            return Response::Success($data['user'],$data['message'],$data['code']);
        }catch (\Throwable $th){
            $message=$th->getMessage();
            return Response::Error($data,$message);
        }
    }


}
