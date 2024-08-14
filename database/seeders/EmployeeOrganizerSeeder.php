<?php

namespace Database\Seeders;

use App\Models\Company_stand;
use App\Models\Exhibition;
use App\Models\Exhibition_category;
use App\Models\Exhibition_company;
use App\Models\Exhibition_employee;
use App\Models\Exhibition_organizer;
use App\Models\Qr;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EmployeeOrganizerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('seeders/exhibition.json');
        $fileContent = file_get_contents($filePath);
        $exhibitions =json_decode($fileContent,true);
        foreach ($exhibitions as $exhibition){
            if($exhibition['status']==4){
                Exhibition_organizer::query()->create([
                    'exhibition_id'=>$exhibition['id'],
                    'user_id'=>rand(5,6),
                ]);
            }
            else{
                Exhibition_organizer::query()->create([
                    'exhibition_id'=>$exhibition['id'],
                    'user_id'=>rand(5,6),
                ]);
                Exhibition_employee::query()->create([
                    'exhibition_id'=>$exhibition['id'],
                    'user_id'=>rand(3,4),
                ]);
            }
        }
    }
}
