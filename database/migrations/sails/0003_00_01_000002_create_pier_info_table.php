<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePierInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('pier_info', static function (Blueprint $table) {
            $table->unsignedSmallInteger('pier_id')->primary();

            $table->string('work_time')->nullable();
            $table->string('phone')->nullable();

            $table->string('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();

            $table->text('description')->nullable();
            $table->text('way_to')->nullable();

            $table->timestamps();

            $table->foreign('pier_id')->references('id')->on('piers')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pier_info');
    }
}
