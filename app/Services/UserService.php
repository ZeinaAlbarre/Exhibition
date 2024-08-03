<?php

namespace App\Services;

use App\Mail\AcceptCompanyemail;
use App\Mail\RejectCompanyemail;
use App\Mail\SendCodeemail;
use App\Mail\SendCodeResetPassword;
use App\Models\Company;
use App\Models\Employee;
use App\Models\ResetCodePassword;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Exception;
use Spatie\Permission\Models\Role;

class UserService
{

    public function visitor_register($request):array{

        DB::beginTransaction();
        try {
            $user=User::query()->create([
                'name'=>$request['name'],
                'email'=>$request['email'],
                'phone'=>$request['phone'],
                'password'=>Hash::make($request['password']),
                'password_confirmation'=>Hash::make($request['password_confirmation']),
            ]);

            $visitor=Visitor::query()->create([
                'gender'=>$request['gender'],
                'birth_date'=>$request['birth_date'],
            ]);

            $user['token']=$user->createToken("token")->plainTextToken;

            $code_v = mt_rand(100000, 999999);
            $user->code= $code_v;
            $user->expire_at= now()->addHour();

            Mail::to($user['email'])->send(new SendCodeemail($code_v));


            $user->userable()->associate($visitor);
            $user->save();

            $visitorRole = Role::query()->where('name','visitor')->first();
            $user->assignRole($visitorRole);

            $permissions=$visitorRole->permissions()->pluck('name')->toArray();
            $user->givePermissionTo($permissions);

            $user->load('roles','permissions');
            $user=User::query()->find($user['id']);
            $user=$this->appendRolesAndPermissions($user);
            DB::commit();
            $data[]=[$user,$visitor];
            $message = 'Verification code sent successfully to your email';
            $code = 200;
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during registration. Please try again ';
            $code = 500;
        }
        return ['user' => $data, 'message' => $message, 'code' => $code];
    }

    public function company_register($request): array
    {
        DB::beginTransaction();
        try{
           $user = User::query()->where('email', $request['email'])->first();
            $companyName = Company::query()->where('company_name', $request['company_name'])->first();
            $companyBusinessEmail = Company::query()->where('business_email', $request['business_email'])->first();
            if (($user||$companyName||$companyBusinessEmail)) {
                if($user && !is_null($user['code'])) {
                    $company=Company::query()->where('id',$user['userable_id'])->first();
                    $user->delete();
                    $company->delete();
                }
               else if(!$user&&$companyName){
                    $userId=User::query()->where('userable_id',$companyName['id'])->first();
                    if(!is_null($userId['code']))
                    {
                        $userId->delete();
                        $companyName->delete();
                    }
                }
                else if(!$user&&$companyBusinessEmail){
                    $userId=User::query()->where('userable_id',$companyBusinessEmail['id'])->first();
                    if(!is_null($userId['code']))
                    {
                        $userId->delete();
                        $companyBusinessEmail->delete();
                    }
                }
            }
           $user1 = User::query()->where('email', $request['email'])->first();
            $companyName1 = Company::query()->where('company_name', $request['company_name'])->first();
            $companyBusinessEmail1 = Company::query()->where('business_email', $request['business_email'])->first();
            if ($user1||$companyName1||$companyBusinessEmail1) {
                if ($user1 && is_null($user1['code'])) {
                    if ($user1 && $companyName1 && $companyBusinessEmail1) {
                         DB::commit();
                        $data = [];
                        $message = 'The email and company name and business email has already been taken. ';
                        $code = 200;
                        return ['user' => $data, 'message' => $message, 'code' => $code];
                    } else if ($user1 && $companyName1) {
                        DB::commit();
                        $data = [];
                        $message = 'The email and company name has already been taken. ';
                        $code = 200;
                        return ['user' => $data, 'message' => $message, 'code' => $code];
                    } else if ($user1 && $companyBusinessEmail1) {
                        DB::commit();
                        $data = [];
                        $message = 'The email and business email has already been taken. ';
                        $code = 200;
                        return ['user' => $data, 'message' => $message, 'code' => $code];
                    } else if ($user1) {
                        DB::commit();
                        $data = [];
                        $message = 'The email has already been taken. ';
                        $code = 200;
                        return ['user' => $data, 'message' => $message, 'code' => $code];
                    }

                }
              if($companyName1){
                    $userId=User::query()->where('userable_id',$companyName1['id'])->first();
                    if(is_null($userId['code']))
                    {
                        if($companyBusinessEmail1){
                            DB::commit();
                            $data=[];
                            $message = 'The company name and business email has already been taken. ';
                            $code = 200;
                            return ['user' => $data, 'message' => $message, 'code' => $code];
                        }
                        if($companyName1){
                            DB::commit();
                            $data=[];
                            $message = 'The company name has already been taken. ';
                            $code = 200;
                            return ['user' => $data, 'message' => $message, 'code' => $code];
                        }
                    }
                }
                if($companyBusinessEmail1){
                    $userId=User::query()->where('userable_id',$companyBusinessEmail1['id'])->first();
                    if(is_null($userId['code']))
                    {
                        DB::commit();
                        $data=[];
                        $message = 'The business email has already been taken. ';
                        $code = 200;
                        return ['user' => $data, 'message' => $message, 'code' => $code];
                    }
                }
            }

            $img=Str::random(32).".".time().'.'.request()->commercial_register->getClientOriginalExtension();

            $user=User::query()->create([
                'name'=>$request['name'],
                'email'=>$request['email'],
                'phone'=>$request['phone'],
                'password'=>Hash::make($request['password']),
                'password_confirmation'=>Hash::make($request['password_confirmation']),
            ]);

            $company=Company::query()->create([
                'company_name'=>$request['company_name'],
                'business_email'=>$request['business_email'],
                'website'=>$request['website'],
                'office_address'=>$request['office_address'],
                'summary'=>$request['summary'],
                'body'=>$request['body'],
                'status'=>'0',
                'commercial_register'=>$img,
            ]);
            $user['token']=$user->createToken("token")->plainTextToken;

            Storage::disk('public')->put($img, file_get_contents($request['commercial_register']));

            $code_v = mt_rand(100000, 999999);
            $user->code= $code_v;
            $user->expire_at= now()->addHour();

            Mail::to($company['business_email'])->send(new SendCodeemail($code_v));

            $user->userable()->associate($company);
            $user->save();


            $companyRole = Role::query()->where('name','company')->first();
            $user->assignRole($companyRole);


            $permissions=$companyRole->permissions()->pluck('name')->toArray();
            $user->givePermissionTo($permissions);

            $user->load('roles','permissions');
            $user=User::query()->find($user['id']);
            $user=$this->appendRolesAndPermissions($user);
            DB::commit();
            $data[]=[$user,$company];
            $message = 'Verification code sent successfully to your email';
            $code = 200;
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during registration please try again';
            $code = 500;
        }
        return ['user' => $data, 'message' => $message, 'code' => $code];

    }

    public function organizer_register($request): array
    {
        DB::beginTransaction();
        try {
            $user=User::query()->create([
                'name'=>$request['name'],
                'email'=>$request['email'],
                'phone'=>$request['phone'],
                'password'=>Hash::make($request['password']),
                'password_confirmation'=>Hash::make($request['password_confirmation']),
            ]);

            $user['token']=$user->createToken("token")->plainTextToken;

            $code_v = mt_rand(100000, 999999);
            $user->code = $code_v;
            $user->expire_at =now()->addHour();
            $user->save();
            Mail::to($user['email'])->send(new SendCodeResetPassword($code_v));

            $organizerRole = Role::query()->where('name','organizer')->first();
            $user->assignRole($organizerRole);

            $permissions=$organizerRole->permissions()->pluck('name')->toArray();
            $user->givePermissionTo($permissions);
            $user->load('roles','permissions');
            $user=User::query()->find($user['id']);
            $user=$this->appendRolesAndPermissions($user);

            DB::commit();
            $message = 'Verification code sent successfully to your email.';
            $code = 200;
        }catch (\Exception $e) {
            DB::rollback();
            $user=[];
            $message = 'Error during registration';
            $code = 500;
        }
        return ['user' => $user, 'message' => $message, 'code' => $code];
    }

    public function add_employee($request): array
    {
        DB::beginTransaction();
        try{
            $user=User::query()->create([
                'name'=>$request['name'],
                'email'=>$request['email'],
                'phone'=>$request['phone'],
                'password'=>Hash::make($request['password']),
                'password_confirmation'=>Hash::make($request['password_confirmation']),
            ]);
            $user['token']=$user->createToken("token")->plainTextToken;
            $employee=Employee::query()->create([
                'is_available'=>0,
            ]);

            $user->userable()->associate($employee);
            $user->save();
            $employeeRole = Role::query()->where('name','employee')->first();
            $user->assignRole( $employeeRole);

            $permissions=$employeeRole->permissions()->pluck('name')->toArray();
            $user->givePermissionTo($permissions);

            $user->load('roles','permissions');
            $user=User::query()->find($user['id']);
            $user=$this->appendRolesAndPermissions($user);
            DB::commit();
            $data[]=[$user,$employee];
            $message='Employee added successfully.';
            return ['user'=>$data ,'message'=>$message];
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during registration';
            $code = 500;
            return ['user'=>$data ,'message'=>$message,'code'=>$code];
        }

    }

    public function delete_employee($id)
    {
        DB::beginTransaction();
        try {
            $user=User::query()->find($id);
            if(!is_null($user)){
                $employee_id = $user['userable_id'];
                $employee = Employee::query()->find($employee_id);
                $employee->delete();
                $user->delete();
                DB::commit();
                $message='Account deleted successfully';
                $code=200;

            }else{
                DB::commit();
                $message='user not found';
                $code=404;
            }
        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during deleted employee please try again';
            $code = 500;
        }
        return ['user'=>[],'message'=>$message,'code'=>$code];
    }

    public function code_check_verification($request,$id): array
    {
        DB::beginTransaction();
        try{
            $user = User::query()->Where('id', $id)->first();
            $now=Carbon::now();
            if(!is_null($user))
            {
                if( !is_null($user['code'])  && $user['code_attempts']<3 && $request['code']!=$user['code'])
                {
                    $user['code_attempts']+=1;
                    $user->save();
                    DB::commit();
                    $message='please enter code again';
                    $code = 422;
                    $data=[];

                }
                else if(!is_null($user['code'])  && $user['code_attempts']>=3 && $request['code']!=$user['code'])
                {
                    $user['code_attempts']==0;
                    $user->save();
                    DB::commit();
                    $message='Your attempts to enter the code have ended. Please request a code to be sent and try again';
                    $code = 422;
                    $data=[];
                }
                else if(!is_null($user['code']) && $now->isBefore($user['expire_at'])&& $request['code']==$user['code']&& $user['code_attempts']<3 ){
                    $user->code=null;
                    $user->expire_at=null;
                    $user->save();
                    DB::commit();
                    $data=$user;
                    $message='Code verified successfully.';
                    $code=200;
                }
                else
                {
                    $user->code=null;
                    $user->expire_at=null;
                    $user->save();
                    DB::commit();
                    $data=[];
                    $message='Verification code has expired. Please request a new one. ';
                    $code=422;
                }
            }
            else
            {
                DB::commit();
                $data = [];
                $message = 'User not found';
                $code = 404;
            }

        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during registration';
            $code = 500;
        }
        return['user'=>$data,'message'=>$message,'code'=>$code];
    }

    public function refresh_code($id): array
    {
        DB::beginTransaction();
        try{
            $user = User::query()->find($id);
            if(!is_null($user))
            {
                $user['code_attempts']=0;
                $user['code']=null;
                $user['expire_at']=null;
                $code_v=mt_rand(100000, 999999);

                $user['code']=$code_v;
                $user['expire_at']=now()->addHour();
                $user->save();

                Mail::to($user['email'])->send(new SendCodeemail($code_v));

                DB::commit();
                $data=[];
                $message = 'Verification code sent successfully to your email.';
                $code=200;
            }
            else
            {
                DB::commit();
                $data = [];
                $message = 'User not found';
                $code = 404;
            }
        }catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during refreshing code please try again';
            $code = 500;
        }
        return['user'=>$data,'message'=>$message,'code'=>$code];
    }

    public function login($request):array
    {
        DB::beginTransaction();
        try{
            $user = User::query()->where('email', $request['email'])->first();

            if (!is_null($user)) {

                if ($user->hasRole('company')) {
                    $company_id = $user['userable_id'];
                    $company = Company::query()->find($company_id);
                }
                if (!Auth::attempt($request->only(['email', 'password']))) {
                    DB::commit();
                    $user = [];
                    $message = 'Incorrect email or password.';
                    $code = 400;
                }
                else
                {
                    if($user->hasRole('company') && is_null($company)){
                        DB::commit();
                        $user = [];
                        $message = 'Company not found';
                        $code = 401;
                    }
                    else if($user->hasRole('company')&& $company['status']==0){
                        DB::commit();
                        $user = [];
                        $message = 'Your account has not been accepted yet';
                        $code = 401;
                    }
                    else
                    {
                        $token = $user->createToken("token")->plainTextToken;
                        $user['token'] = $token;
                        $user->save();
                        DB::commit();
                        $user = $this->appendRolesAndPermissions($user);
                        $message = 'Logged in successfully.';
                        $code = 200;
                    }
                }
            }
            else {
                DB::commit();
                $user = [];
                $message = 'User not found';
                $code = 404;
            }
        }catch (\Exception $e) {
            DB::rollback();
            $user=[];
            $message = 'Error during login please try again';
            $code = 500;
        }

        return ['user' => $user, 'message' => $message, 'code' => $code];
    }

    public function logout(): array
    {
        DB::beginTransaction();
        try{
            $user=Auth::user();
            if(!is_null($user)){
                $user->tokens()->delete();
                DB::commit();
                $message='User logged out successfully';
                $code=200;
            }
            else{
                DB::commit();
                $message='invalid token';
                $code=404;
            }
        }
        catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during logout please try again';
            $code = 500;
        }
        return ['user'=>[],'message' => $message, 'code' => $code];
    }

    public function forgot_password($request): array
    {
        DB::beginTransaction();
        try{
            $user = User::query()->where('email', $request['email'])->first();
            if (is_null($user))
            {
                $message = 'user not found.';
                $code=404;
                return['user'=>[],'message'=>$message,'code'=>$code];
            }
            ResetCodePassword::query()->where('email', $request['email'])->delete();
            $code_v=mt_rand(100000, 999999);

            $codeData = ResetCodePassword::query()->create([
                'email'=>$request['email'],
                'code'=>$code_v,

            ]);

            // Send email to user
            Mail::to($request['email'])->send(new SendCodeResetPassword($code_v));

            DB::commit();
            $message = 'code sent successfully to your email.';
            $code=200;


        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during reset password please try again';
            $code = 500;
        }

        return['user'=>$user,'message'=>$message,'code'=>$code];
    }

    public function code_check($request,$user_id): array{

        DB::beginTransaction();
        try {
            $user=User::query()->where('id',$user_id)->first();
            $passwordReset = ResetCodePassword::query()
                ->Where('email',$user['email'])
                ->where('code',$request['code'])->first();

            if(is_null($passwordReset))
            {
                $message = 'error code try again.';
                $code=404;
                return['user'=>[],'message'=>$message,'code'=>$code];
            }

            if ($passwordReset->created_at  < now()->subHour()) {
                $passwordReset->delete();
                DB::commit();
                $message='Verification code has expired. Please request a new one.';
                $code=422;
            }
            else
            {
                DB::commit();
                $message='Code verified successfully. You can change your password now.';
                $code=200;
            }
        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during reset password please try again';
            $code = 500;
            return['user'=>[],'message'=>$message,'code'=>$code];
        }

        return['user'=>$user,'message'=>$message,'code'=>$code];
    }

    public function reset_password($request,$id): array
    {
        DB::beginTransaction();
        try{
            $user = User::query()->firstWhere('id', $id);
            // update user password
            $user->update(['password' => Hash::make($request['password'])]);
            DB::commit();
            $message='password has been successfully reset';
            $code = 200;
        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during reset password please try again';
            $code = 500;
        }

        return['user'=>[],'message'=>$message,'code'=>$code];
    }
    public function reset_visitor_password($request): array
    {
        DB::beginTransaction();
        try{
            $id=Auth::user()->id;
            $user = User::query()->firstWhere('id',$id);
            // update user password
            if( request()->old_password ==Auth::user()->password) {
                $user->update(['password' => Hash::make($request['password'])]);
                DB::commit();
                $message = 'password has been successfully reset';
                $code = 200;
            }
            else{
                $message = 'you are enter old password wrong!';
                $code = 200;
            }
        }catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            $code = 500;
        }

        return['user'=>$user,'message'=>$message,'code'=>$code];
    }
    public function showCompanyRegisterRequest(){
        DB::beginTransaction();
        try{
            $user=User::query()->where('userable_type','App\Models\Company')->whereNull('code')->with('userable')->get();
            $users=[];
            foreach ($user as $item){
                $company=Company::query()->where('id',$item['userable_id'])->first();
                if($company['status']=='0'){
                    $users[]=$item;
                }
            }
            DB::commit();
            $data=$users;
            $message='Show company has been shown successfully. ';
            $code = 200;
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during showing company request';
            $code = 500;
        }


        return ['user'=>$data,'message'=>$message,'code'=>$code];
    }

    public function accept_company($id):array
    {
        DB::beginTransaction();
        try{
            $user=User::query()->find($id);
            if(!is_null($user)){
                $company_id=$user['userable_id'];
                $company=Company::query()->find($company_id);
                if(!is_null($company)){
                    $company->status='1';
                    $company->save();
                    Mail::to($company->business_email)->send(new AcceptCompanyemail($company->company_name));
                    DB::commit();
                    $message='Company accepted successfully';
                    $code=200;
                    $data[]=[$user,$company];
                }
                else{
                    DB::commit();
                    $message='Company not found';
                    $code=404;
                    $data=[];
                }
            }
            else {
                DB::commit();
                $message='user not found';
                $code=404;
                $data=[];
            }
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during registration';
            $code = 500;
        }


        return ['user'=>$data,'message'=>$message,'code'=>$code];
    }

    public function reject_company($id):array
    {
        DB::beginTransaction();
        try{
            $user=User::query()->find($id);
            if(!is_null($user)){
                $company_id=$user['userable_id'];
                $company=Company::query()->find($company_id);
                if(!is_null($company)){
                    Mail::to($company->business_email)->send(new AcceptCompanyemail($company->company_name));
                    $company->delete();
                    $user->delete();
                    DB::commit();
                    $message='Company rejected successfully';
                    $code=200;
                }
                else{
                    DB::commit();
                    $message='company not found';
                    $code=404;
                }
            }
            else {
                DB::commit();
                $message='user not found';
                $code=404;
            }
        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during reject company please try again';
            $code = 500;
        }

        return ['user'=>[],'message'=>$message,'code'=>$code];
    }

    public function deleteAccount(): array
    {
        DB::beginTransaction();
        try {
            $user_id=Auth::user()->id;
            $user=User::query()->find($user_id);
            if ($user->hasRole('company')) {
                $company_id = $user['userable_id'];
                $company = Company::query()->find($company_id);
                $company->delete();
            }
            else if ($user->hasRole('visitor')) {
                $visitor_id = $user['userable_id'];
                $visitor= Visitor::query()->find($visitor_id);
                $visitor->delete();
            }
            $user->delete();
            DB::commit();
            $message='Account deleted successfully';
            $code=200;

        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during deleted account please try again';
            $code = 500;
        }
        return ['user'=>[],'message'=>$message,'code'=>$code];
    }

    public function showProfile(){
        DB::beginTransaction();
        try {
            $user_id=Auth::user()->id;
            $user=User::query()->where('id',$user_id)->with('userable')->first();
            DB::commit();
            $data=$user;
            $message='Profile information displayed successfully';
            $code=200;
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during show profile info please try again';
            $code = 500;
        }
        return ['user'=>$data,'message'=>$message,'code'=>$code];
    }

    public function updateCompanyProfile($request): array
    {
        DB::beginTransaction();
        try {
            $user_id=Auth::user()->id;
            $user=User::query()->find($user_id);
            $user->update([
                'name'=>$request['name'],
                'phone'=>$request['phone'],
            ]);
            $company_id=$user['userable_id'];
            $company=Company::query()->find($company_id);
            if(!is_null(request()->img)) {
                $img = Str::random(32) . "." . time() . '.' . request()->img->getClientOriginalExtension();
                $company->update([
                    'img'=>$img
                ]);
                Storage::disk('public')->put($img, file_get_contents(request()->img));
            }
            $company->update([
                'business_email'=>$request['business_email'],
                'website'=>$request['website'],
                'office_address'=>$request['office_address'],
                'summary'=>$request['summary'],
                'body'=>$request['body'],
            ]);


            DB::commit();
            $data[]=[$user,$company];
            $message='Profile updated successfully';
            $code=200;
        }catch (\Exception $ex){
            DB::rollback();
            $data=[];
            $message = 'Error during update profile info please try again';
            $code = 500;
        }
        return ['user'=>$data,'message'=>$message,'code'=>$code];
    }

    public function showEmployee(){
        DB::beginTransaction();
        try {
            $employee=User::query()->where('userable_type','App\Models\Employee')->get();
            $employeeStatus=[];
            foreach ($employee as $emp){
                $employeeStatus[$emp['id']]=$emp->userable->is_available;
            }
            DB::commit();
            $message = 'The employee were displayed successfully. ';
            $code = 200;
            return ['user' => $employee, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing exhibition Request. Please try again ';
            $code = 500;
            return ['user' => $data, 'message' => $e->getMessage(), 'code' => $code];

        }
    }

    private function appendRolesAndPermissions($user){
        $roles = [];
        foreach ($user->roles as $role){
            $roles[]=$role->name;
        }
        unset($user['roles']);
        $user['roles']=$roles;

        $permissions = [];
        foreach ($user->permissions as $permission){
            $permissions[]=$permission->name;
        }
        unset($user['permissions']);
        $user['permissions']=$permissions;

        return $user;
    }

}
