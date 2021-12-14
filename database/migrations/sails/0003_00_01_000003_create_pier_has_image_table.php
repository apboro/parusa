<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePierHasImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pier_has_image', function (Blueprint $table) {
            $table->unsignedBigInteger('pier_id');
            $table->unsignedBigInteger('image_id');

            $table->foreign('pier_id')->references('id')->on('piers')->cascadeOnDelete()->cascadeOnUpdate();
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
        Schema::dropIfExists('pier_has_image');
    }
}
