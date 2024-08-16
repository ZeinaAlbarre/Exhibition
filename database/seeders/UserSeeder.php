<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $img=[
            "company/1.jpg", "company/2.jpg", "company/3.jpg","company/4.jpg",
            "company/5.jpg", "company/6.jpg", "company/7.jpg","company/8.jpg",
            "company/9.jpg", "company/10.jpg", "company/11.jpg", "company/12.jpg",
            "company/13.jpg",
        ];


        $l=1;
        for ($i = 0; $i < 40; $i++) {
            $eighteenYearsAgo = Carbon::now()->subYears(18);
            $nineteenYearsAgo = Carbon::now()->subYears(40);
            $birthDate = $faker->dateTimeBetween($nineteenYearsAgo, $eighteenYearsAgo);
            $user= User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'password' => bcrypt('password'),
                'password_confirmation' => bcrypt('password'),
                'code' => $faker->randomNumber(6),
                'expire_at' => now()->addMinutes(10),
                'code_attempts' => 0
            ]);
            $user['token']=$user->createToken("token")->plainTextToken;
            $user->save();
            if ($i % 2 == 0) {
                $visitor = Visitor::create([
                    'gender' => $faker->randomElement(['male', 'female']),
                    'birth_date' => $birthDate
                ]);

                $user->userable()->associate($visitor)->save();

            } else {
                $num=rand(0,12);
                $company = Company::create([
                    'company_name' => $faker->company,
                    'business_email' => $faker->companyEmail,
                    'website' => $faker->url,
                    'office_address' => $faker->address,
                    'summary' => $faker->text,
                    'body' => $faker->paragraph,
                    'commercial_register' => 'YXkJdDjSU4sQTkmsIW2HxF0TypFsT6DR.1722512725.png',
                    'number_of_employees' => $faker->randomNumber(3),
                    'img' =>  $img[$num]
                ]);
                if($l<=15){
                    $company->update([
                       'status'=>'1',
                    ]);
                }
                else{
                    $company->update([
                        'status'=>'0',
                    ]);
                    $user['token']=null;
                    $user->save();
                }
                $l+=1;
                $user->userable()->associate($company)->save();
            }
        }
    }
}
