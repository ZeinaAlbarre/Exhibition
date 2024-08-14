<?php

namespace Database\Seeders;

use App\Models\Company_stand;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Exhibition_sponser;
use App\Models\Qr;
use App\Models\Section;
use App\Models\Sponser;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ExhibitionSponserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('seeders/exhibition.json');
        $fileContent = file_get_contents($filePath);
        $exhibitions = json_decode($fileContent, true);
        $sponser=[1,2,3,4,5,6,7,8,9,10];
        foreach ($exhibitions as $exhibition) {
            if ($exhibition['status'] >= 2) {
                $num=rand(3,10);
                for ($i = 0; $i < $num; $i++) {
                    Exhibition_sponser::query()->create([
                        'exhibition_id' => $exhibition['id'],
                        'sponser_id' => $sponser[$i]
                    ]);
                }
            }
        }
    }
}

