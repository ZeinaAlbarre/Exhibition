<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company_stand;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Payment;
use App\Models\Qr;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user=[];
        $j=7;
        for($i=0;$i<=39;$i++){
            $user[$i]=$j;
            $j++;
        }
        for($i=0;$i<=39;$i++){
            Payment::query()->create([
                'user_id'=>$user[$i],
                'amount'=>10000
            ]);
        }
    }
}
