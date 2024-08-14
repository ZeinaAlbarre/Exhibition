<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\Visitor;
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
        // Create users and associate them with either Visitor or Company models
        for ($i = 0; $i < 60; $i++) {
            $user = User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'phone' => $faker->phoneNumber,
                'password' => bcrypt('password'),
                'password_confirmation' => bcrypt('password'),
                'token' => Str::random(60),
                'code' => $faker->randomNumber(6),
                'expire_at' => now()->addMinutes(10),
                'code_attempts' => 0
            ]);

            if ($i % 2 == 0) {
                // Create Visitor and associate with User
                $visitor = Visitor::create([
                    'gender' => $faker->randomElement(['male', 'female']),
                    'birth_date' => $faker->date
                ]);

                $user->userable()->associate($visitor)->save();

            } else {
                // Create Company and associate with User
                $num=rand(0,12);
                $company = Company::create([
                    'company_name' => $faker->company,
                    'business_email' => $faker->companyEmail,
                    'website' => $faker->url,
                    'office_address' => $faker->address,
                    'summary' => $faker->text,
                    'body' => $faker->paragraph,
                    'status' => '0',
                    'commercial_register' => 'YXkJdDjSU4sQTkmsIW2HxF0TypFsT6DR.1722512725.png',
                    'number_of_employees' => $faker->randomNumber(3),
                    'img' =>  $img[$num]
                ]);
                if($l<=20){
                    $company->update([
                       'status'=>'1',
                    ]);
                }
                else{
                    $company->update([
                        'status'=>'0',
                    ]);
                }
                $l+=1;
                $user->userable()->associate($company)->save();
            }
        }
    }
}
