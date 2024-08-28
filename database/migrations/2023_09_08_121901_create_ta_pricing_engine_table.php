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
        Schema::create('ta_pricing_engine', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pricing_group_id')->nullable();
            $table->string('rule')->nullable();
            $table->string('airline')->nullable();
            $table->integer('api_id')->nullable();
            $table->string('type')->nullable();
            $table->string('amount')->nullable();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->longText('description')->nullable();
            $table->string('status')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ta_pricing_engine');
    }
};
