<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromoCodeHasOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('promo_code_has_orders', static function (Blueprint $table) {
            $table->unsignedSmallInteger('promo_code_id')->index();
            $table->unsignedInteger('order_id');


            $table->foreign('promo_code_id')->references('id')->on('promo_codes')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('order_id')->references('id')->on('orders')->restrictOnDelete()->restrictOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_code_has_orders');
    }
}
