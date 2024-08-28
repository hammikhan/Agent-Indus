<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvidersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Provider::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Sabre',
                'identifier' => 'sabre',
                'type' => 'GDS',
                'namespace' => 'App\\Http\\Traits\\SabreTrait',
                'vendor_id' => 1,
                'balance' => 0,
                'status' => 1,
                'created_at' => '2023-12-22 09:52:28',
                'updated_at' => '2023-12-22 09:52:28'
            ]
        );
        
        Provider::updateOrCreate(
            ['id' => 3],
            [
                'name' => 'Hitit',
                'identifier' => 'hitit',
                'type' => 'LCC',
                'namespace' => 'App\\Http\\Traits\\HititTrait',
                'vendor_id' => 1,
                'balance' => 0,
                'status' => 0,
                'created_at' => '2023-12-22 09:52:28',
                'updated_at' => '2023-12-22 09:52:28'
            ]
        );

    }
}
