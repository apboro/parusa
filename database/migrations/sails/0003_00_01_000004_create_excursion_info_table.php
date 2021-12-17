<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExcursionInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('excursion_info', function (Blueprint $table) {
            $table->unsignedBigInteger('excursion_id')->primary();

            $table->integer('duration');
            $table->text('announce')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->foreign('excursion_id')->references('id')->on('excursions')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('excursion_info');
    }
}
