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
    public function up(): void
    {
        Schema::create('excursion_info', static function (Blueprint $table) {
            $table->unsignedSmallInteger('excursion_id')->primary();

            $table->unsignedSmallInteger('duration');
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
    public function down(): void
    {
        Schema::dropIfExists('excursion_info');
    }
}
