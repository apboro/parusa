<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromoCodeHasExcursionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('promo_code_has_excursions', static function (Blueprint $table) {
            $table->unsignedSmallInteger('promo_code_id')->index();
            $table->unsignedSmallInteger('excursion_id')->index();

            $table->foreign('promo_code_id')->references('id')->on('promo_codes')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('excursion_id')->references('id')->on('excursions')->restrictOnUpdate()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_code_has_excursions');
    }
}
