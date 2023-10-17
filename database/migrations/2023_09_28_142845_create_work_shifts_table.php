<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('tariff_id');
            $table->unsignedSmallInteger('partner_id');
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->unsignedTinyInteger('status_id')->nullable();
            $table->timestamps();

            $table->foreign('partner_id')->on('partners')->references('id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('tariff_id')->on('dictionary_tariffs')->references('id')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('status_id')->on('dictionary_work_shift_statuses')->references('id')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_shifts');
    }
}
