<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('contact_email')->nullable()->after('passenger_email');
            $table->string('contact_country')->nullable()->after('passenger_country');
            $table->string('contact_phone')->nullable()->after('passenger_phone');
        });

        // Copy data from old columns to new columns
        \DB::table('customers')->update([
            'contact_email' => \DB::raw('passenger_email'),
            'contact_country' => \DB::raw('passenger_country'),
            'contact_phone' => \DB::raw('passenger_phone'),
        ]);

        // Remove old columns
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('passenger_email');
            $table->dropColumn('passenger_country');
            $table->dropColumn('passenger_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert by creating old columns and copying data back
        Schema::table('customers', function (Blueprint $table) {
            $table->string('passenger_email')->nullable()->after('contact_email');
            $table->string('passenger_country')->nullable()->after('contact_country');
            $table->string('passenger_phone')->nullable()->after('contact_phone');
        });

        \DB::table('customers')->update([
            'passenger_email' => \DB::raw('contact_email'),
            'passenger_country' => \DB::raw('contact_country'),
            'passenger_phone' => \DB::raw('contact_phone'),
        ]);

        // Remove new columns
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('contact_email');
            $table->dropColumn('contact_country');
            $table->dropColumn('contact_phone');
        });
    }
};
