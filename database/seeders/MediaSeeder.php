<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company_stand;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Media;
use App\Models\Qr;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('seeders/exhibition.json');
        $fileContent = file_get_contents($filePath);
        $exhibitions =json_decode($fileContent,true);
        $img=[
            "media/1.jpg", "media/2.jpg", "media/4.jpg","media/5.jpg",
            "media/7.jpg", "media/8.jpg",
        ];
        foreach($exhibitions as $exhibition){
            if($exhibition['status']>=3){
                for ($i=0;$i<5;$i++){
                    Media::query()->create([
                        'mediable_id' => $exhibition['id'],
                        'mediable_type' => 'App\Models\Exhibition',
                        'url' => $img[$i]
                    ]);
                }
            }
        }
    }
}
