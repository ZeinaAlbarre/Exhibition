<?php

namespace Database\Seeders;

use App\Models\Company_stand;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Qr;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('seeders/exhibition.json');
        $fileContent = file_get_contents($filePath);
        $exhibitions =json_decode($fileContent,true);
        $standNames = [
            "3K-32", "3K-31", "3K-30", "3K-29", "3K-28", "3K-27", "3K-26", "3K-25", "3K-24", "3K-23", "3L-21",
            "3K-21", "3K-20", "3K-19", "3K-18", "3K-17", "3K-16", "3K-15", "3K-14", "3K-13", "3K-12", "3K-11", "3K-10",
            "3J-21", "3J-20", "3J-19", "3J-18", "3J-17", "3J-16", "3J-15", "3J-14", "3J-13", "3J-12", "3J-11", "3J-10",
            "3H-19", "3H-18", "3H-17", "3H-16", "3H-15", "3H-14", "3H-13", "3H-12",
            "3G-09", "3G-01",
            "3F-09", "3F-01",
            "3E-17", "3E-01",
            "3D-19", "3D-18", "3D-17", "3D-16", "3D-15", "3D-14", "3D-13", "3D-12",
            "3C-21", "3C-20", "3C-19", "3C-18", "3C-17", "3C-16", "3C-15",
            "3B-09", "3B-03", "3B-02", "3B-01"
        ];
        $standSize = [
            "3m x 3m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 6m",
            "3m x 6m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 3m", "3K-13", "3m x 3m", "3m x 3m", "3m x 3m",
            "6m X 3m", "3m x 3m", "3m x 6m", "3m x 6m", "3m x 3m", "3m x 3m", "6m x 3m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 3m", "6 x 6",
            "3m x 6m", "3m x 3m", "3m x 3m", "6m x 3m", "6m x 3m", "3m x 3m", "3m x 3m", "3m x 3m",
            "6m x 6m", "9m x 6m",
            "18m x 6m", "12m x 9m",
            "9m x 6m", "12m x 6m",
            "3m x 6m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 6m", "6m x 3m", "3m x 3m", "3m x 3m",
            "3m x 3m", "3m x 3m", "3m x 6m", "3m x 3m", "3m x 3m", "3m x 3m", "3m x 6m",
            "3m x 3m", "3m x 3m", "3m x 3m", "3m x 3m",
            "3m x 3m", "6m x 3m", "3m x 3m", "6m x 6m"
        ];
        $company=[];
        $l=1;
        for($i=0;$i<15;$i++){
            $company[$i]=$l;
            $l+=1;
        }
        DB::beginTransaction();
        try{
            foreach ($exhibitions as $exhibition){
                $j=1;
                $k=1;
                for($i=0 ; $i<$exhibition['number_of_stands'];$i++){
                    $stand=Stand::query()->create([
                        'name' => $standNames[$i],
                        'size' => $standSize[$i],
                        'price' => rand(500, 1000),
                        'status' => ($exhibition['status'] === 2) ? 0 : 1,
                        'exhibition_id' => $exhibition['id'],
                        'company_num' => rand(5,13),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    if($exhibition['status']==3 || $exhibition['status']==4){
                        $company_stand=Company_stand::query()->create([
                            'company_id' => rand(1,15),
                            'stand_id' => $stand['id'],
                            'stand_price' => rand($stand['price'], $stand['price'] + 1000),
                            'status' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $user=User::query()->where('userable_id',$company_stand['company_id'])->where('userable_type','App\Models\Company')->first();
                        Exhibition_company::query()->create([
                            'user_id'=>$user['id'],
                            'exhibition_id'=>$exhibition['id'],
                            'status'=>2,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $qrCodeData = $j . '-' . now()->timestamp;
                        $qrCode = QrCode::format('png')->size(300)->generate($qrCodeData);
                        $qrCodePath = 'qrcodes/' . $qrCodeData . '.png';
                        Storage::disk('public')->put($qrCodePath, $qrCode);
                        $qr=Qr::create([
                            'user_id' => $user['id'],
                            'exhibition_id' => $exhibition['id'],
                            'url' => $qrCodeData,
                            'img' => $qrCodePath,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        if($exhibition['status']==4){
                            $qr->update([
                                'Attended'=>1,
                            ]);
                        }
                        $j+=1;
                    }
                    else if($exhibition['status']==2){
                        for($i=0;$i<$stand['company_num'];$i++){
                            Company_stand::query()->create([
                                'company_id' => $company[$i],
                                'stand_id' => $stand['id'],
                                'stand_price' => rand($stand['price'], $stand['price'] + 50),
                                'status' => 0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $user=User::query()->where('userable_id',$company[$i])->where('userable_type','App\Models\Company')->first();
                            Exhibition_company::query()->create([
                                'user_id'=>$user['id'],
                                'exhibition_id'=>$exhibition['id'],
                                'status'=>1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                    $k+=1;
                }
                if($exhibition['status']==2){
                    for($i=0;$i<14;$i++){
                        $user=User::query()->where('userable_id',$company[$i])->where('userable_type','App\Models\Company')->first();
                        $exhibition_company=Exhibition_company::query()->where('user_id',$user['id'])
                            ->where('exhibition_id',$exhibition['id'])->first();
                        if(!$exhibition_company){
                            Exhibition_company::query()->create([
                                'user_id'=>$user['id'],
                                'exhibition_id'=>$exhibition['id'],
                                'status'=>0,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
            DB::commit();
        }
        catch (Exception $e){
            DB::rollBack();
            echo "Error: " . $e->getMessage();
        }
    }
}
