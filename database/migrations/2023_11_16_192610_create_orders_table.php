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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('api');
            $table->string('ref_key')->nullable();
            $table->integer('pnr_id')->nullable();
            $table->string('pnrCode')->nullable();
            $table->string('customerEmail')->nullable();
            $table->integer('promo_id')->nullable();
            $table->integer('pricing_engine_id')->nullable();
            $table->tinyInteger('loyalty')->nullable();
            $table->double('discount')->nullable();
            $table->double('total', 8, 2)->nullable();
            $table->double('pricingEnginePrice', 8, 2)->nullable();
            $table->double('basePrice', 8, 2)->nullable();
            $table->double('customPrice', 8, 2)->nullable();
            $table->double('userPricingEnginePrice', 8, 2)->nullable();
            $table->string('paid')->nullable();
            $table->date('departDateTime')->nullable();
            $table->double('originalTotal')->nullable();
            $table->string('paymentTransaction')->nullable();
            $table->string('status')->nullable();
            $table->string('approvalStatus')->nullable();
            $table->string('approvedBy')->nullable();
            $table->longText('note')->nullable();
            $table->longText('apiResponse')->nullable();
            $table->longText('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
