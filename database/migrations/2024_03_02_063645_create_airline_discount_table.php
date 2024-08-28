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
        Schema::create('airline_discount', function (Blueprint $table) {
            $table->id();
            $table->string('airline');
            $table->integer('discount')->default(0);
            $table->string('provider')->nullable();
            $table->text('departure_codes')->nullable();
            $table->integer('status')->default(1);
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airline_discount');
    }
};
