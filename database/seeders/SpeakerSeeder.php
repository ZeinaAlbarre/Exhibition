<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company_stand;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Qr;
use App\Models\Scheduale;
use App\Models\Sponser;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SpeakerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('seeders/speaker.json');
        $fileContent = file_get_contents($filePath);
        $scheduale =json_decode($fileContent,true);
        foreach($scheduale as $item){
            Scheduale::query()->create($item);
        }
        $s=[];
        $l=1;
        for($i=0;$i<20;$i++){
            $s[$i]=$l;
            $l+=1;
        }
        $filePath = public_path('seeders/exhibition.json');
        $fileContent = file_get_contents($filePath);
        $exhibitions =json_decode($fileContent,true);
        foreach ($exhibitions as $exhibition){
            if ($exhibition['status']>=2){
                $num=rand(5,10);
                for ($i=0;$i<$num;$i++){
                    $scheduale=Scheduale::query()->find($s[$i]);
                    $sch=Scheduale::query()->where('topic_name',$scheduale['topic_name'])
                        ->where('exhibition_id',$exhibition['id'])->first();
                    if(!$sch){
                        Scheduale::query()->create([
                            'topic_name'=>$scheduale['topic_name'],
                            'speaker_name'=>$scheduale['speaker_name'],
                            'summary'=>$scheduale['summary'],
                            'body'=>$scheduale['body'],
                            'time'=>$scheduale['time'],
                            'date'=>$scheduale['date'],
                            'about_speaker'=>$scheduale['about_speaker'],
                            'img'=>$scheduale['img'],
                            'speaker_email'=>$scheduale['speaker_email'],
                            'linkedin'=>$scheduale['linkedin'],
                            'facebook'=>$scheduale['facebook'],
                            'exhibition_id'=>$exhibition['id']
                        ]);
                    }
                }
            }
        }
    }

}
