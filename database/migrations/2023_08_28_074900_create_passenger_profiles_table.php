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
        Schema::create('passenger_profiles', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->nullable();
            $table->string('type')->nullable();
            $table->string('title')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('gender')->nullable();
            $table->string('dob')->nullable();
            $table->string('region')->nullable();
            $table->string('phone')->nullable();
            $table->string('identity')->nullable();
            $table->string('passportNumber')->nullable();
            $table->string('passportExpiry')->nullable();
            $table->string('identityNumber')->nullable();
            $table->string('issueCountry')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passenger_profiles');
    }
};
