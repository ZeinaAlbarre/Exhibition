<?php

namespace App\Services;

use App\Mail\AcceptCompanyRequest;
use App\Mail\AcceptExhibitionEmail;
use App\Mail\RejectCompanyRequest;
use App\Mail\RejectExhibitionEmail;
use App\Models\Company;
use App\Models\Company_stand;
use App\Models\Employee;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Exhibition_employee;
use App\Models\Exhibition_organizer;
use App\Models\Exhibition_revision;
use App\Models\Exhibition_section;
use App\Models\Exhibition_sponser;
use App\Models\Exhibition_visitor;
use App\Models\Media;
use App\Models\Payment;
use App\Models\Qr;
use App\Models\Scheduale;
use App\Models\Section;
use App\Models\Sponser;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\Permission\Models\Role;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExhibitionService
{

    public function addExhibition($request):array
    {
        DB::beginTransaction();
        try{
            $exhibition=Exhibition::query()->create([

                'title'=>$request['title'],
                'body'=>$request['body'],
                'start_date'=>$request['start_date'],
                'end_date'=>$request['end_date'],
                'time'=>$request['time'],
                'price'=>$request['price'],
                'location'=>$request['location'],
                'status'=>0
            ]);
            $exhibitionOrganizer=Exhibition_organizer::query()->create([
                'exhibition_id'=>$exhibition['id'],
                'user_id'=>auth()->id(),
            ]);
            if(request()->hasFile('cover_img')){
                $img=Str::random(32).".".time().'.'.request()->cover_img->getClientOriginalExtension();
                $exhibition['cover_img']=$img;
                Storage::disk('public')->put($img, file_get_contents($request['cover_img']));
                $exhibition->save();
            }
            $randomEmployee = Employee::query()->where('is_available',0)->inRandomOrder()->value('id');
            if(is_null($randomEmployee)){
                $randomEmployee = Employee::query()->where('is_available',1)->inRandomOrder()->value('id');
            }
            $user=User::query()->where('userable_id',$randomEmployee)->where('userable_type','App\Models\Employee')->first();
            $exhibitionEmployee=Exhibition_employee::query()->create([
                'exhibition_id'=>$exhibition['id'],
                'user_id'=>$user['id'],
            ]);
            DB::commit();
            $data=$exhibition;
            $message = 'Exhibition added successfully. ';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during adding exhibition. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function showExhibitionRequest(): array
    {
        DB::beginTransaction();
        try {
            $user=Auth::user()->id;
            $exhibition=[];
            $exhibitionEmployee=Exhibition_employee::query()->where('user_id',$user)->get();
            if($exhibitionEmployee) {
                foreach ($exhibitionEmployee as $item) {
                    $exhibitionID = $item->exhibition_id;
                    $exhibit = Exhibition::query()->where('id', $exhibitionID)->where('status', 0)->first();
                    if (!is_null($exhibit)) {
                        $exhibition[] = $exhibit;
                    }
                }
            }
            DB::commit();
            if(!$exhibition) $message='There are no exhibition request yet. ';
            else $message='Exhibition requests have been successfully displayed. ';
            $data=$exhibition;
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];


        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during showing exhibition Request. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $e->getMessage(), 'code' => $code];

        }
    }

    public function acceptExhibition($id):array
    {
        DB::beginTransaction();
        try {
            $exhibition=Exhibition::query()->find($id);
            $exhibitionOrganizer=Exhibition_organizer::query()->where('exhibition_id',$id)->first();
            $user_id=$exhibitionOrganizer['user_id'];
            $user=User::query()->find($user_id);
            $exhibition['status']=1;
            $exhibition->save();
            Mail::to($user->email)->send(new AcceptExhibitionEmail($user->name,$exhibition->title));
            DB::commit();
            $data=$exhibition;
            $message='Exhibition accepted successfully. ';
            $code = 200;
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during accepting exhibition. Please try again ';
            $code = 500;
        }
        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

    public function rejectExhibition($id):array
    {
        DB::beginTransaction();
        try {
            $exhibition=Exhibition::query()->find($id);
            $exhibitionOrganizer=Exhibition_organizer::query()->where('exhibition_id',$id)->first();
            $user_id=$exhibitionOrganizer['user_id'];
            $user=User::query()->find($user_id);
            Mail::to($user->email)->send(new RejectExhibitionEmail($user->name,$exhibition->title));
            $exhibition->delete();
            $exhibitionOrganizer->delete();
            DB::commit();
            $message='Exhibition rejected successfully. ';
            $code = 200;

        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during rejecting exhibition. Please try again ';
            $code = 500;
        }
        return ['data' => [], 'message' => $message, 'code' => $code];
    }

    public function deleteExhibition($id):array
    {
        DB::beginTransaction();
        try {
            $exhibition=Exhibition::query()->find($id);
            $exhibitionOrganizer=Exhibition_organizer::query()->where('exhibition_id',$id)->first();
            if($exhibition['cover_img']){
                Storage::disk('public')->delete($exhibition['cover_img']);
            }
            $exhibition->delete();
            $exhibitionOrganizer->delete();
            DB::commit();
            $message='Exhibition deleted successfully. ';
            $code = 200;
            return ['data' => [], 'message' => $message, 'code' => $code];

        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during deleting exhibition. Please try again ';
            $code = 500;
            return ['data' => [], 'message' => $message, 'code' => $e->getCode()];
        }
    }

    public function updateExhibition($request,$id):array
    {
        DB::beginTransaction();
        try{
            $user=Auth::user();
            $exhibition=Exhibition::query()->find($id);
            if($exhibition['status']<4) {
                $titleExists = Exhibition::query()->where('title', $request['title'])->where('id', '!=', $id)->exists();
                if ($user->hasRole('employee')) {
                    if ($request['title'] != $exhibition['title'] && $titleExists) {
                        DB::commit();
                        $data = [];
                        $message = 'The title has already been taken.';
                        $code = 400;
                    } else if (($request['title'] != $exhibition['title'] && !$titleExists) || $request['title'] == $exhibition['title']) {

                        $exhibition->update([
                            'title' => $request['title'],
                            'body' => $request['body'],
                            'start_date' => $request['start_date'],
                            'end_date' => $request['end_date'],
                            'time' => $request['time'],
                            'price' => $request['price'],
                            'location' => $request['location'],
                            'status' => $exhibition['status']
                        ]);
                        if (request()->has('number_of_stands')) {
                            $exhibition['number_of_stands'] = $request['number_of_stands'];
                            $exhibition->save();
                        }
                        if (request()->hasFile('cover_img')) {
                            $img = Str::random(32) . "." . time() . '.' . request()->cover_img->getClientOriginalExtension();
                            $exhibition['cover_img'] = $img;
                            Storage::disk('public')->put($img, file_get_contents($request['cover_img']));
                            $exhibition->save();
                        }
                        if (request()->hasFile('exhibition_map')) {
                            $img = Str::random(32) . "." . time() . '.' . request()->exhibition_map->getClientOriginalExtension();
                            $exhibition['exhibition_map'] = $img;
                            Storage::disk('public')->put($img, file_get_contents($request['exhibition_map']));
                            $exhibition->save();
                        }
                        DB::commit();
                        $data = $exhibition;
                        $message = 'Exhibition updated successfully.';
                        $code = 400;
                    }

                } else {

                    $exhibitionRevision = Exhibition_revision::query()->find($id);
                    $titleRExists = Exhibition_revision::query()->where('title', $request['title'])->where('id', '!=', $id)->exists();
                    if ($exhibitionRevision) {
                        DB::commit();
                        $data = [];
                        $message = 'Please wait until your previous modification is accepted, then submit your new modification. ';
                        $code = 400;
                    } else if (($request['title'] != $exhibition['title'] && $titleExists) || $titleRExists) {
                        DB::commit();
                        $data = [];
                        $message = 'The title has already been taken.';
                        $code = 400;
                    } else if (($request['title'] != $exhibition['title'] && !$titleExists) || $request['title'] == $exhibition['title']) {

                        $exhibitionR = Exhibition_revision::query()->create([
                            'id' => $exhibition['id'],
                            'title' => $request['title'],
                            'body' => $request['body'],
                            'start_date' => $request['start_date'],
                            'end_date' => $request['end_date'],
                            'time' => $request['time'],
                            'price' => $request['price'],
                            'location' => $request['location'],
                            'status' => $exhibition['status']
                        ]);
                        if (request()->has('number_of_stands')) {
                            $exhibitionR['number_of_stands'] = $request['number_of_stands'];
                            $exhibitionR->save();
                        }
                        if (request()->hasFile('cover_img')) {
                            $img = Str::random(32) . "." . time() . '.' . request()->cover_img->getClientOriginalExtension();
                            $exhibitionR['cover_img'] = $img;
                            Storage::disk('public')->put($img, file_get_contents($request['cover_img']));
                            $exhibitionR->save();
                        }
                        if (request()->hasFile('exhibition_map')) {
                            $img = Str::random(32) . "." . time() . '.' . request()->cover_img->getClientOriginalExtension();
                            $exhibitionR['exhibition_map'] = $img;
                            Storage::disk('public')->put($img, file_get_contents($request['exhibition_map']));
                            $exhibitionR->save();
                        }
                        DB::commit();
                        $data = $exhibitionR;
                        $message = 'Your amendment has been sent to the official in charge of the exhibition. Please wait for the modifications to be accepted. ';
                        $code = 200;
                    }
                }
            }
            else{
                DB::commit();
                $data = [];
                $message = 'The exhibitioin was end ,You can not update exhibition. ';
                $code = 200;
            }
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during updating exhibition. Please try again ';
            $code = 500;
            return ['data' => [], 'message' => $message, 'code' => $e->getCode()];
        }
    }

    public function showUpdateExhibitions(){
        DB::beginTransaction();
        try {
            $employee=Auth::user();
            $employeeEx=Exhibition_employee::query()->where('user_id',$employee->id)->get();
            $exhibitions=[];
            if($employeeEx){
                foreach ($employeeEx as $item){
                    $exhibitionId=Exhibition::query()->where('id',$item['exhibition_id'])->first();
                    if($exhibitionId){
                        $exhibition=Exhibition_revision::query()->where('id',$exhibitionId['id'])->first();
                        if($exhibition) $exhibitions[]=$exhibition;
                    }
                }
            }

            DB::commit();
            $data=$exhibitions;
            if(!$exhibitions) $message='There are no exhibition request. ';
            else $message='The modified exhibitions were successfully displayed. ';
            $code = 200;

        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during showed exhibitions. Please try again ';
            $code = 500;
        }
        return ['data' => $data, 'message' => $message, 'code' => $code];

    }

    public function showUpdateExhibition($id){
        DB::beginTransaction();
        try {
           // $exhibition=Exhibition::query()->find($id);
            $modifiedExhibition=Exhibition_revision::query()->where('id',$id)->first();
            DB::commit();
           // $data[]=[$exhibition,$modifiedExhibition];
            $data=$modifiedExhibition;
            $message='Exhibition showed successfully. ';
            $code = 200;

        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during showed exhibition. Please try again ';
            $code = 500;
        }
        return ['data' => $data, 'message' => $message, 'code' => $code];

    }

    public function acceptExhibitionUpdate($id):array
    {
        DB::beginTransaction();
        try{
            $exhibition=Exhibition::query()->find($id);
            $exhibitionR=Exhibition_revision::query()->find($id);
            if (!$exhibition)
            {
                DB::commit();
                $message='invalid id.';
                $code = 404;
                return ['data' => [], 'message' => $message, 'code' => $code];
            }
            else{
                $exhibition->update([
                    'title'=>$exhibitionR['title'],
                    'body'=>$exhibitionR['body'],
                    'start_date'=>$exhibitionR['start_date'],
                    'end_date'=>$exhibitionR['end_date'],
                    'time'=>$exhibitionR['time'],
                    'price'=>$exhibitionR['price'],
                    'location'=>$exhibitionR['location'],
                    'status'=>$exhibitionR['status'],
                    'number_of_stands'=>$exhibitionR['number_of_stands'],
                    'cover_img'=>$exhibitionR['cover_img'],
                    'exhibition_map'=>$exhibitionR['exhibition_map'],
                ]);
                $exhibitionR->delete();
                DB::commit();
                $message='Exhibition updated successfully. ';
                $code = 200;
                return ['data' => $exhibition, 'message' => $message, 'code' => $code];
            }

        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during accept exhibition update. Please try again ';
            $code = 500;
            return ['data' => [], 'message' => $message, 'code' => $e->getCode()];
        }
    }

    public function rejectExhibitionUpdate($id):array
    {
        DB::beginTransaction();
        try{
            $exhibition=Exhibition::query()->find($id);
            $exhibitionR=Exhibition_revision::query()->find($id);
            if (!$exhibition)
            {
                DB::commit();
                $message='invalid id.';
                $code = 404;
                return ['data' => [], 'message' => $message, 'code' => $code];
            }
            else{
                $exhibitionR->delete();
                DB::commit();
                $message='Exhibition rejected successfully. ';
                $code = 200;
                return ['data' => [], 'message' => $message, 'code' => $code];
            }

        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during reject exhibition update. Please try again ';
            $code = 500;
            return ['data' => [], 'message' => $message, 'code' => $e->getCode()];
        }
    }

    public function addExhibitionSection($request, $id)
    {
        DB::beginTransaction();
        try {
            $exhibition = Exhibition::query()->find($id);
            if (!$exhibition) {
                $data = [];
                $message = 'Invalid id. ';
                $code = 404;
            } else {
                $section_id = $request['sections'];
                $sections = [];
                foreach ($section_id as $item) {
                    $exhibition_section = Exhibition_section::query()->where('exhibition_id', $id)->where('section_id', $item['id'])->first();
                    if (!$exhibition_section) {
                        $exhibition_section = Exhibition_section::query()->create([
                            'exhibition_id'=>$id,
                            'section_id'=>$item['id']
                        ]);
                        $sections[] = Section::query()->find($item['id']);
                    }
                }
                DB::commit();
                $data[] = [$exhibition, $sections];
                $message = 'Exhibition section added successfully';
                $code = 200;
            }
        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during adding exhibition section. Please try again ';
            $code = 500;
        }
        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

    public function deleteExhibitionSection($exhibition_id,$section_id)
    {
        DB::beginTransaction();
        try {
            $exhibition = Exhibition::query()->find($exhibition_id);
            if (!$exhibition) {
                $data = [];
                $message = 'Invalid id. ';
                $code = 404;
            }
            else {
                $exhibition_section = Exhibition_section::query()->where('exhibition_id', $exhibition_id)->
                where('section_id', $section_id)->delete();
                DB::commit();
                $data = [];
                $message = 'Exhibition section deleted successfully';
                $code = 200;
            }
        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during deleting exhibition section. Please try again ';
            $code = 500;
        }
        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

    public function showExhibitionSection($section_id){
        DB::beginTransaction();
        try {
            $user=Auth::user();
            $exhibition=[];
            $exhibition_section=Exhibition_section::query()->where('section_id',$section_id)->get();
            if($exhibition_section){
                foreach($exhibition_section as $item){
                    $exhibition_id=$item->exhibition_id;
                    if($user->hasRole('company')){
                        $exhibit=Exhibition::query()->where('id', $exhibition_id)
                            ->whereIn('status',[2,3,4])
                            ->orderBy('created_at','desc')
                            ->first();
                        if(!is_null($exhibit)){
                            $exhibition[] = $exhibit;
                        }
                    }else{
                        $exhibit=Exhibition::query()->where('id', $exhibition_id)
                            ->whereIn('status',[3,4])
                            ->orderBy('created_at','desc')
                            ->first();
                        if(!is_null($exhibit)){
                            $exhibition[] = $exhibit;
                        }
                    }


                }
            }
            DB::commit();
            $data = $exhibition;
            $message = 'The section exhibitions was shown successfully.';
            $code = 200;

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing exhibitions. Please try again ';
            $code = 500;
        }
        return ['data' => $data, 'message' => $message, 'code' => $code];

    }

    public function addExhibitionMedia($request,$exhibition_id)
    {
        DB::beginTransaction();
        try {
            $img = Str::random(32) . "." . time() . "." . $request->img->getClientOriginalExtension();

            $media = Media::query()->create([
                'mediable_id' => $exhibition_id,
                'mediable_type' => 'App\Models\Exhibition',
                'url' => $img
            ]);

            Storage::disk('public')->put($img, file_get_contents($request->file('img')));
            DB::commit();
            $message = 'add media successfully';
            $code = 200;
            $data = [];

            return ['data' => $data, 'message' => $message, 'code' => $code];
        }
        catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during add media . Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }

    }

    public function deleteExhibitionMedia($media_id)
    {
        DB::beginTransaction();
        try {
        $media = Media::query()->where('id','=',$media_id)->first();
        $media->delete();

        Storage::disk('public')->delete($media_id);

        DB::commit();
        $message = 'delete media successfully';
        $code = 200;
        $data = [];

        return ['data' => $data, 'message' => $message, 'code' => $code];
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during delete media . Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function showOrganizerExhibition()
    {
        DB::beginTransaction();
        try {
            $user=Auth::user()->id;
            $exhibition=[];
            $exhibitionOrganizer=Exhibition_organizer::query()->where('user_id',$user)
                ->orderBy('created_at','desc')->get();
            if($exhibitionOrganizer) {
                foreach ($exhibitionOrganizer as $i) {
                    $exhibitionID = $i->exhibition_id;
                    $exhibit = Exhibition::query()->where('id', $exhibitionID)->whereIn('status', [1,2,3,4])->first();
                    if (!is_null($exhibit)) {
                        $exhibition[] = $exhibit;
                    }
                }
            }
            DB::commit();
            if(!$exhibition) $message='There are no exhibition accepted yet. ';
            else $message='Exhibitions have been successfully displayed. ';
            $data=$exhibition;
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];


        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during showing exhibitions . Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function acceptCompanyRequest($company_id,$stand_id)
    {
        DB::beginTransaction();
        try{
            $company=Company::query()->findOrFail($company_id);
            $user=User::query()->where('userable_id',$company_id)->where('userable_type','App\Models\Company')->first();
            $stand=Stand::query()->findOrFail($stand_id);
            $exhibition=Exhibition::query()->findOrFail($stand['exhibition_id']);
            $standPrice=Company_stand::query()->where('stand_id',$stand_id)->where('company_id',$company_id)->first();
            $companiesStand=Company_stand::query()->where('stand_id',$stand_id)->where('company_id','!=',$company_id)->get();
            if($companiesStand){
                foreach ($companiesStand as $item){
                    $item->delete();
                }
            }
            $standCompany=Company_stand::query()->where('stand_id','!=',$stand_id)->where('company_id',$company_id)->get();
            if($standCompany){
                foreach ($standCompany as $item){
                    $item->delete();
                }
            }
            $exhibitionCompany=Exhibition_company::query()->where('user_id',$user['id'])->where('exhibition_id',$exhibition['id'])->first();
            $exhibitionCompany['status']=2;
            $exhibitionCompany->save();
            $payment=Payment::query()->where('user_id',$user['id'])->first();
            $payment['amount']-=$standPrice['stand_price'];
            $payment->save();
            $standPrice['status']=1;
            $standPrice->save();
            $qrCodeData = $exhibitionCompany->id . '-' . now()->timestamp;
            $qrCode = QrCode::format('png')->size(300)->generate($qrCodeData);
            $qrCodePath = 'qrcodes/' . $qrCodeData . '.png';
            Storage::disk('public')->put($qrCodePath, $qrCode);
            $qr=Qr::create([
                'user_id' => $user->id,
                'exhibition_id' => $exhibition->id,
                'url' => $qrCodeData,
                'img' => $qrCodePath
            ]);
            Mail::to($company['business_email'])->send(new AcceptCompanyRequest($company->company_name,$exhibition->title,$exhibition->location,$exhibition->start_date));
            DB::commit();
            $data=$exhibitionCompany;
            $message='company accepted successfully. ';
            $code = 200;
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during accepting company request. ';
            $code = 500;
        }
        return ['data'=>$data,'message'=>$message,'code'=>$code];
    }

    public function rejectCompanyRequest($company_id,$stand_id)
    {
        DB::beginTransaction();
        try{
            $company=Company::query()->findOrFail($company_id);
            $stand=Stand::query()->findOrFail($stand_id);
            $exhibition=Exhibition::query()->findOrFail($stand['exhibition_id']);
            $companyStand=Company_stand::query()->where('stand_id',$stand_id)->where('company_id',$company_id)->first();
            $companyStand->delete();
            Mail::to($company->business_email)->send(new RejectCompanyRequest($company->company_name,$exhibition->title));
            DB::commit();
            $data=[];
            $message='company rejected successfully. ';
            $code = 200;
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during rejected company request. ';
            $code = 500;
        }

        return ['data'=>$data,'message'=>$message,'code'=>$code];
    }

    public function addSchedule($exhibition_id, $request){

        DB::beginTransaction();
        try{
            $img = Str::random(32) . "." . time() . "." . $request->img->getClientOriginalExtension();
            $schedule=Scheduale::query()->create([

                'topic_name'=>$request['topic_name'],
                'speaker_name'=>$request['speaker_name'],
                'summary'=>$request['summary'],
                'body'=>$request['body'],
                'time'=>$request['time'],
                'date'=>$request['date'],
                'about_speaker'=>$request['about_speaker'],
                'img'=>$img,
                'speaker_email'=>$request['speaker_email'],
                'linkedin'=>$request['linkedin'],
                'facebook'=>$request['facebook'],
                'exhibition_id'=>$exhibition_id

            ]);
            Storage::disk('public')->put($img, file_get_contents($request['img']));
            $schedule->save();

            DB::commit();
            $data=$schedule;
            $message = ' schedule added successfully. ';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = $e->getMessage();
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function deleteSschedule($schedule_id){
        DB::beginTransaction();
        try {
            $schedule=Scheduale::query()->find($schedule_id);
            Storage::disk('public')->delete($schedule->img);
            $schedule->delete();
            DB::commit();
            $message=' schedule deleted successfully. ';
            $code = 200;
            return ['data' => [], 'message' => $message, 'code' => $code];

        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during deleting schedule. Please try again ';
            $code = 500;
            return ['data' => [], 'message' => $message, 'code' => $e->getCode()];
        }
    }

    public function updateSchedule($schedule_id, $request)
    {
        DB::beginTransaction();
        try {
            $schedule = Scheduale::query()->where('id','=',$schedule_id)->first();
            if ($request->hasFile('img')) {
                // Delete the old image
                Storage::disk('public')->delete($schedule->img);
                // Store the new image
                $img = Str::random(32) . "." . time() . '.' . $request->img->getClientOriginalExtension();
                Storage::disk('public')->put($img, file_get_contents($request->img));
                $schedule->img = $img;
            }


            $schedule->update([
                'topic_name'=>$request['topic_name'],
                'speaker_name'=>$request['speaker_name'],
                'summary'=>$request['summary'],
                'body'=>$request['body'],
                'time'=>$request['time'],
                'date'=>$request['date'],
                'about_speaker'=>$request['about_speaker'],
                'speaker_email'=>$request['speaker_email'],
                'linkedin'=>$request['linkedin'],
                'facebook'=>$request['facebook'],
                'exhibition_id' => $schedule['exhibition_id'],
            ]);

            DB::commit();
            $data = $schedule;
            $message ='Schedule updated successfully.';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();

            $data = [];
            $message = 'Error updating schedule: ' . $e->getMessage();
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];
        }
    }

    public function showScheduale($schedule_id){

        DB::beginTransaction();
        try {
            $schedule = Scheduale::query()->where('id','=',$schedule_id)->first();
            DB::commit();
            $data=$schedule;
            $message=' schedule has been successfully displayed. ';
            $code = 200;
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during showing schedule . Please try again ';
            $code = 500;
        }
        return ['data' => $data, 'message' => $message, 'code' => $code];

    }

    public function showExhibitionScheduale($exhibition_id){
        DB::beginTransaction();

        try {
            $scheduale = Scheduale::where('exhibition_id', $exhibition_id)->get();
            DB::commit();
            $data = $scheduale;
            $message = 'scheduale  have been successfully show.';
            $code = 200;
        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error . Please try again.';
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];

    }

    public function addStand($request,$exhibition_id): array
    {
        DB::beginTransaction();

        try {
            $stand = Stand::create([
                'name' => $request['name'],
                'size' =>  $request['size'],
                'price' =>  $request['price'],
                'status' => $request->input('status', 0),
                'exhibition_id' => $exhibition_id,
            ]);

            DB::commit();

            $data = $stand;
            $message = 'Stand added successfully.';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];
        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = $e->getMessage();
            $code = 500;

            return ['data' => $data, 'message' => $message, 'code' => $code];
        }
    }

    public function updateStand($request ,$stand_id){

        DB::beginTransaction();

        try {
            $stand = Stand::findOrFail($stand_id);
            $stand->update([
                'name' => $request['name'],
                'size' =>  $request['size'],
                'price' =>  $request['price'],
                'status' => $request->input('status', 0),
                'exhibition_id' => $stand['exhibition_id'],
            ]);

            DB::commit();

            $data = $stand;
            $message = 'Stand updated successfully.';
            $code = 200;

            return ['data' => $data, 'message' => $message, 'code' => $code];
        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = $e->getMessage();
            $code = 500;

            return ['data' => $data, 'message' => $message, 'code' => $code];
        }
    }

    public function deleteStand($stand_id){
        DB::beginTransaction();
        try {
            $stand=Stand::query()->find($stand_id);
            $stand->delete();
            DB::commit();
            $message=' stand deleted successfully. ';
            $code = 200;
            return ['data' => [], 'message' => $message, 'code' => $code];

        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during deleting stand. Please try again ';
            $code = 500;
            return ['data' => [], 'message' => $message, 'code' => $e->getCode()];
        }
    }

    public function showEmployeeExhibition()
    {
        DB::beginTransaction();
        try {
            $user = Auth::user()->id;
            $exhibition = [];
            $exhibitionEmployee = Exhibition_employee::query()->where('user_id', $user)
                ->orderBy('created_at','desc')->get();
            if($exhibitionEmployee) {
                foreach ($exhibitionEmployee as $item) {
                    $exhibitionID = $item->exhibition_id;
                    $exhibit = Exhibition::query()->where('id', $exhibitionID)->whereIn('status', [1, 2, 3])->first();
                    if (!is_null($exhibit)) {
                        $exhibition[] = $exhibit;
                    }
                }
            }
            DB::commit();
            $data = $exhibition;
            $code=200;
            if($data) $message = 'Exhibitions have been successfully displayed. ';
            else $message = 'There are no exhibition. ';
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing employee exhibition . Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function showExhibitionStands($exhibition_id): array
    {
        DB::beginTransaction();

        try {
            $stands = Stand::where('exhibition_id', $exhibition_id)->get();
            DB::commit();
            $data = $stands;
            $message = 'stands  have been successfully show.';
            $code = 200;
        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error . Please try again.';
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];

    }

    public function searchExhibition($request){

        DB::beginTransaction();
        try {
            $title = $request['title'];
            $user=Auth::user();
            if($user->hasRole('company')){
                $exhibition=Exhibition::query()->whereIn('status',[2,3,4])
                    ->where('title', 'LIKE', '%'.$title.'%')
                    ->with('sections.section:id,name')
                    ->orderBy('created_at','desc')->get();
                DB::commit();
                $data = $exhibition->toArray();
                if (!empty($data)) {
                    foreach ($data as &$exhibit) {
                        if (isset($exhibit['sections']) && is_array($exhibit['sections'])) {
                            foreach ($exhibit['sections'] as &$section) {
                                $section = $section['section'];
                            }
                        } else {
                            $exhibit['sections'] = [];
                        }
                    }
                }
            }
            else if($user->hasRole('visitor')){
                $exhibition=Exhibition::query()->where('status',3,4)
                    ->where('title', 'LIKE', '%'.$title.'%')
                    ->with('sections.section:id,name')
                    ->orderBy('created_at','desc')->get();
                DB::commit();
                $data = $exhibition->toArray();
                if (!empty($data)) {
                    foreach ($data as &$exhibit) {
                        if (isset($exhibit['sections']) && is_array($exhibit['sections'])) {
                            foreach ($exhibit['sections'] as &$section) {
                                $section = $section['section'];
                            }
                        } else {
                            $exhibit['sections'] = [];
                        }
                    }
                }
            }
            else if($user->hasRole('employee')){
                $employee_exhibitions=Exhibition_employee::query()->where('user_id',$user->id)->pluck('exhibition_id');
                $exhibition=Exhibition::query()->whereIn('id',$employee_exhibitions)
                    ->where('title', 'LIKE', '%'.$title.'%')
                    ->with('sections.section:id,name')
                    ->orderBy('created_at','desc')->get();
                DB::commit();
                $data = $exhibition->toArray();
                if (!empty($data)) {
                    foreach ($data as &$exhibit) {
                        if (isset($exhibit['sections']) && is_array($exhibit['sections'])) {
                            foreach ($exhibit['sections'] as &$section) {
                                $section = $section['section'];
                            }
                        } else {
                            $exhibit['sections'] = [];
                        }
                    }
                }
            }
            else if($user->hasRole('organizer')){
                $organizer_exhibitions=Exhibition_organizer::query()->where('user_id',$user->id)->pluck('exhibition_id');
                $exhibition=Exhibition::query()->whereIn('id',$organizer_exhibitions)
                    ->where('title', 'LIKE', '%'.$title.'%')
                    ->with('sections.section:id,name')
                    ->orderBy('created_at','desc')->get();
                DB::commit();
                $data = $exhibition->toArray();
                if (!empty($data)) {
                    foreach ($data as &$exhibit) {
                        if (isset($exhibit['sections']) && is_array($exhibit['sections'])) {
                            foreach ($exhibit['sections'] as &$section) {
                                $section = $section['section'];
                            }
                        } else {
                            $exhibit['sections'] = [];
                        }
                    }
                }
            }
            $message = 'The exhibition search was successfully';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing exhibition search. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function showExhibition($id) {
        DB::beginTransaction();
        try {
            $exhibition = Exhibition::query()
                ->with([
                    'sections.section:id,name',
                    'media',
                    'exhibition_sponser.sponser'
                ])
                ->find($id);

            if (!is_null($exhibition)) {
                DB::commit();

                $data = $exhibition->toArray();
                foreach ($data['sections'] as &$section) {
                    $section = $section['section'];
                }
                foreach ($data['exhibition_sponser'] as &$sponsor) {
                    $sponsor = $sponsor['sponser'];
                }

                $message = 'The exhibition was shown successfully.';
                $code = 200;
            } else {
                DB::commit();
                $data = [];
                $message = 'invalid id';
                $code = 400;
            }

            return ['data' => $data, 'message' => $message, 'code' => $code];
        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing exhibition. Please try again';
            $code = 500;
            return ['data' => $data, 'message' => $e->getMessage(), 'code' => $code];
        }
    }

    public function showEndExhibition(){

        DB::beginTransaction();
        try {
            $exhibition=Exhibition::query()->where('status',4)
                ->with('sections.section:id,name')
                ->orderBy('created_at','desc')->get();
            if($exhibition) {
                DB::commit();
                $data = $exhibition->toArray();
                if (!empty($data)) {
                    foreach ($data as &$exhibit) {
                        if (isset($exhibit['sections']) && is_array($exhibit['sections'])) {
                            foreach ($exhibit['sections'] as &$section) {
                                $section = $section['section'];
                            }
                        } else {
                            $exhibit['sections'] = [];
                        }
                    }
                }
                $message = 'The exhibitions was shown successfully.';
                $code = 200;
            }
            else{
                DB::commit();
                $data=[];
                $message = 'There are no avaliable exhibition. ';
                $code = 200;
            }
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing available exhibition. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function showAvailableExhibition(){

        DB::beginTransaction();
        try {
            $exhibition=Exhibition::query()->where('status',3)
                ->with('sections.section:id,name')
                ->orderBy('created_at','desc')->get();
            if($exhibition) {
                DB::commit();
                $data = $exhibition->toArray();
                if (!empty($data)) {
                    foreach ($data as &$exhibit) {
                        if (isset($exhibit['sections']) && is_array($exhibit['sections'])) {
                            foreach ($exhibit['sections'] as &$section) {
                                $section = $section['section'];
                            }
                        } else {
                            $exhibit['sections'] = [];
                        }
                    }
                }
                $message = 'The available exhibitions was shown successfully.';
                $code = 200;
            }
            else{
                DB::commit();
                $data=[];
                $message = 'There are no avaliable exhibition. ';
                $code = 200;
            }
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing available exhibition. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function showAvailableCompanyExhibition()
    {
        DB::beginTransaction();
        try {
            $exhibition = Exhibition::query()->where('status', 2)
                ->with('sections.section:id,name')
                ->orderBy('created_at','desc')->get();
            if($exhibition) {
                DB::commit();
                $data = $exhibition->toArray();
                if (!empty($data)) {
                    foreach ($data as &$exhibit) {
                        if (isset($exhibit['sections']) && is_array($exhibit['sections'])) {
                            foreach ($exhibit['sections'] as &$section) {
                                $section = $section['section'];
                            }
                        } else {
                            $exhibit['sections'] = [];
                        }
                    }
                }
                $message = 'The available companies exhibitions was shown successfully.';
                $code = 200;
            }
            else{
                DB::commit();
                $data=[];
                $message = 'There are no avaliable exhibition. ';
                $code = 200;
            }
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing available exhibition. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $e->getMessage(), 'code' => $code];
        }
    }

    public function changeExhibitionStatus($request,$id){
        DB::beginTransaction();
        try {
            $exhibition=Exhibition::query()->find($id);
            $status=$request['status'];
            if($status<0||$status>=5){
                DB::commit();
                $data = [];
                $message = 'Please enter a valid status. ';
                $code = 200;
            }
            else{
                if($status==4){
                    $exhibition['status']=$status;
                    $exhibition->save();
                    $exhibition_employee=Exhibition_employee::query()->where('exhibition_id',$id)->first();
                    $exhibition_employee->delete();
                }
                else{
                    $exhibition['status']=$status;
                    $exhibition->save();
                }
                DB::commit();
                $data = $exhibition;
                $message = 'The exhibition status changed successfully.';
                $code = 200;
            }
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during change exhibition status. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function changeEmployeeStatus($request){
        DB::beginTransaction();
        try {
            $userId=Auth::user()->id;
            $user=User::query()->where('id',$userId)->first();
            $employeeId=$user['userable_id'];
            $employee=Employee::query()->find($employeeId);
            $status=$request['is_available'];
            if($status<0||$status>1){
                DB::commit();
                $data = [];
                $message = 'Please enter a valid status. ';
                $code = 200;
            }
            else{
                $employee['is_available']=$status;
                $employee->save();
                DB::commit();
                $data = $employee;
                $message = 'Your status changed successfully.';
                $code = 200;
            }
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during change your status. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function addSponser($request, $exhibition_id): array
    {
        $data = [];
        try {
            $img = Str::random(32) . "." . time() . "." . $request->img->getClientOriginalExtension();
            $sponser = Sponser::create([
                'name' => $request['name'],
                'img' => $img,
            ]);

            // Create the association record
            $sponser_ex= Exhibition_sponser::query()->create([
                'sponser_id' => $sponser['id'],
                'exhibition_id' => $exhibition_id,
            ]);
            Storage::disk('public')->put($img, file_get_contents($request['img']));

            $data = $sponser;
            $message = 'Sponsor added successfully.';
            $code = 200;

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

    public function deleteSponsor($sponsor_id){
        DB::beginTransaction();
        try {
            $sponsor=Sponser::query()->find($sponsor_id);
            Storage::disk('public')->delete($sponsor->img);
            $sponsor->delete();
            DB::commit();
            $message=' sponsor deleted successfully. ';
            $code = 200;
            return ['data' => [], 'message' => $message, 'code' => $code];

        }catch (\Exception $e) {
            DB::rollback();
            $message = 'Error during deleting sponsor. Please try again ';
            $code = 500;
            return ['data' => [], 'message' => $message, 'code' => $e->getCode()];
        }
    }

    public function showExhibitionSponsors($exhibition_id){
        DB::beginTransaction();
        try {
            $sponsors = Exhibition_sponser::where('exhibition_id', $exhibition_id)->get();
            DB::commit();
            $data = $sponsors;
            $message = 'sponsors  have been successfully show.';
            $code = 200;
        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = $e->getMessage();
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];

    }

    public function filter_Exhibition_today()
    {
        DB::beginTransaction();
        try {
            $exhibition = Exhibition::query()
                ->where('status', 3)
                ->whereDate('start_date', Carbon::now()->toDateString()) // Filter for today's exhibitions
                ->with('sections')
                ->get();

            DB::commit();
            $data = $exhibition;
            $message = 'The available exhibitions for today was shown successfully.';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing available exhibition. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];
        }
    }

    public function filter_Exhibition_thisWeek()
    {
        DB::beginTransaction();
        try {
            $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
            $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
            $exhibition = Exhibition::query()
                ->where('status', 3)
                ->whereBetween('start_date', [$startOfWeek, $endOfWeek])
                ->with('sections')
                ->get();

            DB::commit();
            $data = $exhibition;
            $message = 'The available exhibitions for this week was shown successfully.';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing available exhibition. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];
        }
    }

    public function filter_Exhibition_later()
    {
        DB::beginTransaction();
        try {
            $exhibition = Exhibition::query()
                ->where('status', 3)
                ->whereDate('start_date', '>', Carbon::now()->toDateString()) // Filter for future exhibitions
                ->with('sections')
                ->get();

            DB::commit();
            $data = $exhibition;
            $message = 'The available future exhibitions was shown successfully.';
            $code = 200;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing available exhibition. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];
        }
    }

    public function showCompany($company_id){

        DB::beginTransaction();
        try {
            $company = Company::query()->where('id',$company_id)->get();
            DB::commit();
            $data=$company;
            $message='company has been successfully displayed. ';
            $code = 200;
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error during showing company . Please try again ';
            $code = 500;
        }
        return ['data' => $data, 'message' => $message, 'code' => $code];


    }

    public function showExhibitionCompany($exhibition_id){
        DB::beginTransaction();
        $data=[];
        try {
            $exhibition_company = Exhibition_company::where('exhibition_id', $exhibition_id)->get();
            foreach($exhibition_company as $item) {
                $user_id=$item->user_id;
                $c_id=User::query()->where('id',$user_id)->first();
                $data[]=Company::query()->where('id', $c_id['userable_id'])->first();
            }
            DB::commit();
            $message = 'company have been successfully show.';
            $code = 200;
        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = $e->getMessage();
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];

    }

    public function showRegisterExhibition(){
        DB::beginTransaction();
        try {
            $user=Auth::user();
            $exhibition_visitor=Exhibition_visitor::query()->where('user_id',$user->id)->get();
            $exhibitions=[];
            if($exhibition_visitor){
                foreach ($exhibition_visitor as $item){
                    $exhibition=Exhibition::query()->where('id',$item['exhibition_id'])->first();
                    $exhibitions[]=$exhibition;
                }
                DB::commit();
                $data = $exhibitions;
                $message = 'Register Exhibition has been shown successfully. ';
                $code = 200;
            }
            else{
                DB::commit();
                $data = [];
                $message = 'There are no Register exhibition yet. ';
                $code = 200;
            }

            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing register exhibition Request. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

}
