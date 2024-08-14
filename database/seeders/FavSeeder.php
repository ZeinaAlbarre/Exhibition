<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company_stand;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Favorite;
use App\Models\Qr;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FavSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $visitor=[];
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
                $num=rand(5,20);
                for($i=0;$i<$num;$i++){
                    Favorite::query()->create([
                        'user_id'=>$visitor[$i],
                        'exhibition_id'=>$exhibition['id']
                    ]);
                }
            }
        }
    }
}
