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
        Schema::create('providers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('identifier')->unique();
            $table->string('type')->nullable();
            $table->string('namespace')->nullable();
            $table->unsignedBigInteger('vendor_id');
            $table->integer('balance')->default(0);
            $table->enum('status', ['0', '1'])->default('0');
            $table->timestamps();

            $table->index('status');
            $table->index('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
