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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('airline_pnr')->after('pnrCode')->nullable();
            $table->string('pnr_time_limit')->after('airline_pnr')->nullable();
            $table->enum('pnr_status', ['Confirmed', 'Cancelled'])->default('Confirmed');
            $table->longText('booking_response')->after('note')->nullable();
            $table->longText('ticket_response')->after('booking_response')->nullable();
            $table->longText('fetch_response')->after('ticket_response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('airline_pnr');
            $table->dropColumn('pnr_time_limit');
            $table->dropColumn('pnr_status');
            $table->dropColumn('booking_response');
            $table->dropColumn('ticket_response');
            $table->dropColumn('fetch_response');
        });
    }
};
