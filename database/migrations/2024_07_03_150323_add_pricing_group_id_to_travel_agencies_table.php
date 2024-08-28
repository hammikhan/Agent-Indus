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
        Schema::table('travel_agencies', function (Blueprint $table) {
            $table->unsignedBigInteger('pricing_group_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_agencies', function (Blueprint $table) {
            $table->dropColumn('pricing_group_id');
        });
    }
};
