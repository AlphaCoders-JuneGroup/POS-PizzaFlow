<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CustomerExperienceSeeder::class,
            DeliveryDispatchSeeder::class,
            IngredientSeeder::class,
            PromotionSeeder::class,
            StaffNotificationSeeder::class,
        ]);
    }
}
