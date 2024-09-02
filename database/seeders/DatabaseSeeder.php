<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            PermissionSeeder::class,
            SettingsTableSeeder::class,
            // AirportSeeder::class,
            // AirportSecoundSeeder::class,
            // AirportThirdSeeder::class,
            // AirlineDiscountSeeder::class,
            // ProvidersTableSeeder::class,
        ]);
    }
}
