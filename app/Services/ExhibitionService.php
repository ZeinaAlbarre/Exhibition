<?php

namespace App\Services;

use App\Mail\AcceptCompanyemail;
use App\Mail\AcceptExhibitionEmail;
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
use App\Models\Media;
use App\Models\Scheduale;
use App\Models\Section;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
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
                $randomEmployee = Employee::query()->inRandomOrder()->value('id');
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
            $exhibition=Exhibition::query()->find($id);
            $exhibitionRevision=Exhibition_revision::query()->find($id);
            $titleExists = Exhibition::query()->where('title', $request['title'])->where('id', '!=', $id)->exists();
            $titleRExists = Exhibition_revision::query()->where('title', $request['title'])->where('id', '!=', $id)->exists();
            if($exhibitionRevision){
                DB::commit();
                $data=[];
                $message='Please wait until your previous modification is accepted, then submit your new modification. ';
                $code = 400;
            }
            else if (($request['title'] != $exhibition['title']  && $titleExists ) ||  $titleRExists )
            {
                DB::commit();
                $data=[];
                $message='The title has already been taken.';
                $code = 400;
            }
            else if (($request['title'] != $exhibition['title'] && !$titleExists)|| $request['title'] == $exhibition['title']){

                $exhibitionR=Exhibition_revision::query()->create([
                    'id'=>$exhibition['id'],
                    'title'=>$request['title'],
                    'body'=>$request['body'],
                    'start_date'=>$request['start_date'],
                    'end_date'=>$request['end_date'],
                    'time'=>$request['time'],
                    'price'=>$request['price'],
                    'location'=>$request['location'],
                    'status'=>$exhibition['status']
                ]);
                if(request()->has('number_of_stands')){
                    $exhibitionR['number_of_stands'] = $request['number_of_stands'];
                    $exhibitionR->save();
                }
                if(request()->hasFile('cover_img')){
                    $img=Str::random(32).".".time().'.'.request()->cover_img->getClientOriginalExtension();
                    $exhibitionR['cover_img']=$img;
                    Storage::disk('public')->put($img, file_get_contents($request['cover_img']));
                    $exhibitionR->save();
                }
                if(request()->hasFile('exhibition_map')){
                    $img=Str::random(32).".".time().'.'.request()->cover_img->getClientOriginalExtension();
                    $exhibitionR['exhibition_map']=$img;
                    Storage::disk('public')->put($img, file_get_contents($request['exhibition_map']));
                    $exhibitionR->save();
                }
                DB::commit();
                $data=$exhibitionR;
                $message='Your amendment has been sent to the official in charge of the exhibition. Please wait for the modifications to be accepted. ';
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
            $employee=auth()->user();
            $employeeEx=Exhibition_employee::query()->where('user_id',$employee['id'])->get();
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
            $message='The modified exhibitions were successfully displayed. ';
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
                $dataTimeNow = now();
                $sectionsData = [];
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
                    else{
                        $sec = Section::query()->find($item['id']);
                        $message = 'You hava already entered this '.$sec['name'].' section';
                        $code = 400;
                        return ['data' => [], 'message' => $message, 'code' => $code];
                    }
                }
                DB::commit();
                $data[] = [$exhibition, $sections];
                $message = 'Exhibition section added successfully';
                $code = 200;
                return ['data' => $data, 'message' => $message, 'code' => $code];
            }
        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during adding exhibition section. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $e->getMessage(), 'code' => $code];
        }
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
            $exhibitionOrganizer=Exhibition_organizer::query()->where('user_id',$user)->get();
            if($exhibitionOrganizer) {
                foreach ($exhibitionOrganizer as $i) {
                    $exhibitionID = $i->exhibition_id;
                    $exhibit = Exhibition::query()->where('id', $exhibitionID)->where('status', 1)->first();
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

    public function showCompanyRequests($exhibition_id)
    {
        DB::beginTransaction();

        try {
            $companyRequests = Exhibition_company::where('exhibition_id', $exhibition_id)
                ->where('status', 0)
                ->get();

            DB::commit();

            $data = $companyRequests; // Return the collection of requests
            $message = 'Company requests have been successfully retrieved.';
            $code = 200;

        } catch (\Exception $e) {
            DB::rollback();

            $data = [];
            $message = 'Error retrieving company requests. Please try again.';
            $code = 500;
        }

        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

    public function acceptCompanyRequest($exhibition_id,$company_id):array
    {
        DB::beginTransaction();
        try{
            $companyRequest = Exhibition_company::where('exhibition_id', $exhibition_id)
                ->where('user_id', $company_id)
                ->get();

            $companyRequest['status']=1;
            $companyRequest->save();

            DB::commit();
            $data=$companyRequest;
            $message='company accepted successfully. ';
            $code = 200;
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error';
            $code = 500;
        }


        return ['user'=>$data,'message'=>$message,'code'=>$code];
    }

    public function rejectCompanyRequest($exhibition_id,$company_id):array
    {
        DB::beginTransaction();
        try{
            $companyRequest = Exhibition_company::where('exhibition_id', $exhibition_id)
                ->where('user_id', $company_id)
                ->delete();


            DB::commit();
            $data=[];
            $message='company rejected successfully. ';
            $code = 200;
        }catch (\Exception $e) {
            DB::rollback();
            $data=[];
            $message = 'Error';
            $code = 500;
        }


        return ['user'=>$data,'message'=>$message,'code'=>$code];
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

    public  function showScheduale($schedule_id){

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
            $exhibitionEmployee = Exhibition_employee::query()->where('user_id', $user)->get();
            if (!is_null($exhibitionEmployee)) {
                foreach ($exhibitionEmployee as $item) {
                    $exhibitionID = $item->exhibition_id;
                    $exhibit=Exhibition::query()->where('id', $exhibitionID)->where('status', 1)->first();
                    if(!is_null($exhibit)){
                        $exhibition[] = $exhibit;
                    }
                }
                DB::commit();
                $data = $exhibition;
                $message = 'Exhibitions have been successfully displayed. ';
                $code = 200;
                return ['data' => $data, 'message' => $message, 'code' => $code];
            } else {
                $message = 'There is no exhibition Request. ';
                $code = 200;
                return ['data' => [], 'message' => $message, 'code' => $code];
            }
        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing employee exhibition . Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];

        }
    }

    public function searchExhibition($request){

        DB::beginTransaction();
        try {
            $title = $request['title'];
            $exhibition=Exhibition::query()->where('title', 'LIKE', '%'.$title.'%')->with('sections.section')->get();
            DB::commit();
            $data = $exhibition;
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

    public function showExhibitions(){
        DB::beginTransaction();
        try {
            $exhibition=Exhibition::query()->with('sections.section')->get();
            if(!$exhibition){
                DB::commit();
                $data = [];
                $message = 'There are no exhibition.';
                $code = 200;
            }else{
                DB::commit();
                $data = [];
                $message = 'The exhibitions was shown successfully.';
                $code = 200;
            }
            return ['data' => $exhibition, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing exhibitions. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $message, 'code' => $code];
        }
    }

    public function showExhibition($id){
        DB::beginTransaction();
        try {
            $exhibition=Exhibition::query()->with('sections.section')->with('media')->with('exhibition_sponser.sponser')->find($id);
            if(!is_null($exhibition)){
                DB::commit();
                $data = $exhibition;
                $message = 'The exhibition was shown successfully.';
                $code = 200;
            }
            else{
                DB::commit();
                $data = [];
                $message = 'invalid id';
                $code = 400;
            }
            return ['data' => $data, 'message' => $message, 'code' => $code];

        } catch (\Exception $e) {
            DB::rollback();
            $data = [];
            $message = 'Error during showing exhibition. Please try again ';
            $code = 500;
            return ['data' => $data, 'message' => $e->getMessage(), 'code' => $code];
        }
    }

    public function showExhibitionSection($section_id){
        DB::beginTransaction();
        try {
            $exhibition=[];
            $exhibition_section=Exhibition_section::query()->where('section_id',$section_id)->get();
            if($exhibition_section){
                foreach($exhibition_section as $item){
                    $exhibition_id=$item->exhibition_id;
                    $exhibit=Exhibition::query()->where('id', $exhibition_id)->first();
                    if(!is_null($exhibit)){
                        $exhibition[] = $exhibit;
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

    public function showAvailableExhibition(){

        DB::beginTransaction();
        try {
            $exhibition=Exhibition::query()->where('status',3)->with('sections.section')->get();
            DB::commit();
            $data = $exhibition;
            $message = 'The available exhibitions was shown successfully.';
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

    public function showAvailableCompanyExhibition()
    {
        DB::beginTransaction();
        try {
            $exhibition = Exhibition::query()->where('status', 2)->with('sections.section')->get();
            DB::commit();
            $data = $exhibition;
            $message = 'The available companies exhibitions was shown successfully.';
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

    public function changeExhibitionStatus($request,$id){
        DB::beginTransaction();
        try {
            $exhibition=Exhibition::query()->find($id);
            $status=$request['status'];
            if($status<0||$status>3){
                DB::commit();
                $data = [];
                $message = 'Please enter a valid status. ';
                $code = 200;
            }
            else{
                $exhibition['status']=$status;
                $exhibition->save();
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

}
