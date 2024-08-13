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

        // Create users and associate them with either Visitor or Company models
        for ($i = 0; $i < 40; $i++) {
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
                $company = Company::create([
                    'company_name' => $faker->company,
                    'business_email' => $faker->companyEmail,
                    'website' => $faker->url,
                    'office_address' => $faker->address,
                    'summary' => $faker->text,
                    'body' => $faker->paragraph,
                    'status' => $faker->randomElement(['0', '1']),
                    'commercial_register' => 'YXkJdDjSU4sQTkmsIW2HxF0TypFsT6DR.1722512725.png',
                    'number_of_employees' => $faker->randomNumber(3),
                    'img' =>  'YXkJdDjSU4sQTkmsIW2HxF0TypFsT6DR.1722512725.png'
                ]);

                $user->userable()->associate($company)->save();
            }
        }
    }
}
