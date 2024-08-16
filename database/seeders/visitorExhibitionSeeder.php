<?php

namespace Database\Seeders;

use App\Models\Company_stand;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Exhibition_visitor;
use App\Models\Qr;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class visitorExhibitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = public_path('seeders/exhibition.json');
        $fileContent = file_get_contents($filePath);
        $exhibitions =json_decode($fileContent,true);
        DB::beginTransaction();
        try{
            foreach ($exhibitions as $exhibition)
            {
                if($exhibition['status']==3){
                    $j=7;
                    for($i=0;$i<rand(5,10);$i++){
                        $j+=2;
                        $exhibitionVisitor = Exhibition_visitor::query()->create([
                            'exhibition_id' => $exhibition['id'],
                            'user_id' => $j,
                        ]);
                        $qrCodeData = $exhibitionVisitor->id . '-' . now()->timestamp;
                        $qrCode = QrCode::format('png')->size(300)->generate($qrCodeData);
                        $qrCodePath = 'qrcodes/' . $qrCodeData . '.png';
                        Storage::disk('public')->put($qrCodePath, $qrCode);
                        Qr::create([
                            'user_id' => $j,
                            'exhibition_id' => $exhibition['id'],
                            'url' => $qrCodeData,
                            'img' => $qrCodePath,
                        ]);
                    }
                }
                else if($exhibition['status']==4){
                    $j=5;
                    for($i=0;$i<rand(10,20);$i++){
                        $j+=2;
                        $exhibitionVisitor = Exhibition_visitor::query()->create([
                            'exhibition_id' => $exhibition['id'],
                            'user_id' => $j,
                        ]);
                        $qrCodeData = $exhibitionVisitor->id . '-' . now()->timestamp;
                        $qrCode = QrCode::format('png')->size(300)->generate($qrCodeData);
                        $qrCodePath = 'qrcodes/' . $qrCodeData . '.png';
                        Storage::disk('public')->put($qrCodePath, $qrCode);
                        $qr=Qr::create([
                            'user_id' => $j,
                            'exhibition_id' => $exhibition['id'],
                            'url' => $qrCodeData,
                            'img' => $qrCodePath,
                            'Attended'=>'1'
                        ]);
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
