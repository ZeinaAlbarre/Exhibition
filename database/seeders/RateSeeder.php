<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company_stand;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Exhibition_organizer;
use App\Models\Exhibition_visitor;
use App\Models\Favorite;
use App\Models\Qr;
use App\Models\Rate;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $visitor[]=[];
        $l=7;
        for($i=0;$i<20;$i++){
            $visitor[$i]=$l;
            $l+=2;
        }
        $filePath = public_path('seeders/exhibition.json');
        $fileContent = file_get_contents($filePath);
        $exhibitions =json_decode($fileContent,true);
        foreach ($exhibitions as $exhibition){
            if($exhibition['status']>=3){
                $num=rand(9,20);
                for($i=0;$i<$num;$i++){
                    $exhibition_visitor=Exhibition_visitor::query()->where('exhibition_id',$exhibition['id'])->where('user_id',$visitor[$i])->first();
                    if($exhibition_visitor){
                        Rate::query()->create([
                            'rate'=>rand(2,5),
                            'user_id'=>$visitor[$i],
                            'exhibition_id'=>$exhibition['id']
                        ]);
                    }
                }
            }
        }
    }
}
