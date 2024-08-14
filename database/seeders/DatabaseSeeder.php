<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesPremissionsSeeder::class,
            UserSeeder::class,
            ExhibitionSeeder::class,
            CategorySeeder::class,
            SectionSeeder::class,
            ExhibitionSectionSeeder::class,
            ExhibitionCategorySeeder::class,
            StandSeeder::class,
            visitorExhibitionSeeder::class,
            EmployeeOrganizerSeeder::class,
            SponserSeeder::class,
            ExhibitionSponserSeeder::class,
            FavSeeder::class,
            RateSeeder::class,
            PaymentSeeder::class,
            SpeakerSeeder::class,
            MediaSeeder::class,
            ProductSeeder::class
        ]);
    }
}
