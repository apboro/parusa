<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExcursionHasImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('excursion_has_image', function (Blueprint $table) {
            $table->unsignedBigInteger('excursion_id');
            $table->unsignedBigInteger('image_id');

            $table->foreign('excursion_id')->references('id')->on('excursions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('image_id')->references('id')->on('images')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('excursion_has_image');
    }
}
