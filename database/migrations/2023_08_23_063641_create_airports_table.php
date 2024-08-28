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
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->decimal('lat', 10, 7);
            $table->decimal('lon', 10, 7);
            $table->string('name');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country');
            $table->string('flag')->nullable();
            $table->string('woeid')->nullable();
            $table->string('tz')->nullable();
            $table->string('phone')->nullable();
            $table->string('type')->nullable();
            $table->string('email')->nullable();
            $table->string('url')->nullable();
            $table->integer('runway_length')->nullable();
            $table->integer('elev')->nullable();
            $table->string('icao')->nullable();
            $table->integer('direct_flights')->nullable();
            $table->integer('carriers')->nullable();
            $table->timestamps();
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airports');
    }
};
