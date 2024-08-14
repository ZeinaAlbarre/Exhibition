<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\Company_stand;
use App\Models\Exhibition;
use App\Models\Exhibition_company;
use App\Models\Product;
use App\Models\Qr;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $t=[
            "product/1.jpg", "product/2.jpg", "product/3.jpg","product/4.jpg",
            "product/5.jpg",
        ];
        $n=[
            "Architect Aid","Camera","Laptop","Smart Glasses","Alutra HeadPhones"
        ];
        $f=[
            "product/6.jpg", "product/8.jpg","product/9.jpg", "product/13.jpg",
            "product/14.jpg", "product/15.jpg", "product/16.jpg","product/17.jpg",
            "product/18.png", "product/19.jpg"
        ];
        $k=[
            "Barbecue Nuts","Vanilla Cupcake","Crispy Biscuits", "Coffee biscuits",
            "Strawberry Jam","Apple Juice","Organic Orange Juice","Classic Chips",
            "Salt & Vinegar Chips","Hand Cooked Hot Chips"
        ];
        $company=Company::query()->where('status',1)->get();
        $l=1;
        foreach ($company as $item){
            $user=User::query()->where('userable_id',$item['id'])->where('userable_type','App\Models\Company')->first();
            if($l % 5 ==0){
                for($i=0;$i<5;$i++){
                    Product::query()->create([
                        'info'=>$n[$i],
                        'img' => $t[$i],
                        'user_id' => $user['id'],
                    ]);
                }
            }
            else if($l % 2==0){
                for($i=0;$i<5;$i++){
                    Product::query()->create([
                        'info'=>$k[$i],
                        'img' => $f[$i],
                        'user_id' => $user['id'],
                    ]);
                }
            }
            else{
                for($i=5;$i<10;$i++){
                    Product::query()->create([
                        'info'=>$k[$i],
                        'img' => $f[$i],
                        'user_id' => $user['id'],
                    ]);
                }
            }
            $l+=1;
        }
    }
}
