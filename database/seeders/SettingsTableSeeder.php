<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $apiData = [
                'ApiUrl' => 'https://api.havail.sabre.com',
                'UserId' => '9999',
                'Group' => 'asdfsaf', // Encrypt the "Group" field
                'Domain' => 'AA',
                'Password' => 'asdfadsf', // Encrypt the "Password" field
            ];
    
            $api = Setting::updateOrCreate(
                ['id' => 1],
                [
                    'name' => 'sabre',
                    'type' => 'api',
                    'data' => $apiData, // Store the JSON data with encrypted fields
                    'status' => 1,
                ]
            );
        } catch (\Exception $e) {
            
            // Log or dump the exception message for debugging.
            dd($e->getMessage());
        }
    }
}
