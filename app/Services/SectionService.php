<?php

namespace App\Services;

use App\Models\Section;
use Illuminate\Support\Facades\DB;

class SectionService
{
    public function addSection($request)
    {
        if (!Section::query()->where('name', $request->name)->exists()) {

            $section = Section::query()->create([
                'name' => $request['name'],
            ]);
            $message = 'add section successfully';
            $code = 200;
            $data = $section;
        }
        else {
            $code=400;
            $data= [];
            $message = 'the section  is already exists';
        }
        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

    public function deleteSection($section_id)
    {
        if (Section::query()->where('id', $section_id)->exists()) {

            $section=Section::query()->where('id', $section_id)->first();
            $section->delete();
            $message = 'delete section successfully';
            $code = 200;
            $data = $section;
        }
        else {
            $code=400;
            $data= [];
            $message = 'the section  is already exists';
        }
        return ['data' => $data, 'message' => $message, 'code' => $code];
    }

    public function showSections()
    {
        DB::beginTransaction();
        try {
            $sections=Section::query()->get();
            DB::commit();
            $message ='show  section successfully';
            $code=200;
        }
        catch (\Exception $e) {
            DB::rollback();
            $sections=[];
            $message = 'Error during show sections please try again';
            $code = 500;
        }

        return ['data'=>$sections,'message'=>$message,'code'=>$code];

    }


}
