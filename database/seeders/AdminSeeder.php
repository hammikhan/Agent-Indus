<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['id' => 1],
            [
                'first_name' => 'Asad',
                'last_name' => 'khan',
                'type' => 'admin',
                'email' => "indus.igt@gmail.com",
                'password' => Hash::make("indus.igt@gmail.com"),
                'status' => 1,
                'email_verified_at' => now()
            ]
        );
        Admin::updateOrCreate(
            ['id' => 2],
            [
                'first_name' => 'Hamid',
                'last_name' => 'Afridi',
                'type' => 'admin',
                'email' => "hamid22401@gmail.com",
                'password' => Hash::make("hamid22401@gmail.com"),
                'status' => 1,
                'email_verified_at' => now()
            ]
        );

        // DB::table('travel_agencies')->update(['pricing_group_id' => 1]);
        
        
    }
}
