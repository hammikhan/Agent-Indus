<?php

namespace Database\Seeders;

use App\Models\AirlineDiscount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AirlineDiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AirlineDiscount::updateOrCreate(
            ['id'=>1],
            [
                'provider_id' => 1,
                'airline' => 'UL',
                'discount'=> 5,
                'provider' => 'Sabre',
            ]
        );
        AirlineDiscount::updateOrCreate(
            ['id'=>2],
            [
                'provider_id' => 1,
                'airline' => 'OD',
                'discount'=> 3,
                'provider' => 'Sabre',
                'departure_codes' => 'KHI,ISB,LHE',
            ]
        );
    }
}
