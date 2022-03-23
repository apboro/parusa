<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripChainHasTripTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('trip_chain_has_trip', static function (Blueprint $table) {
            $table->unsignedInteger('chain_id');
            $table->unsignedInteger('trip_id');

            $table->foreign('chain_id')->references('id')->on('trip_chains')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('trip_id')->references('id')->on('trips')->cascadeOnDelete()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_chain_has_trip');
    }
}
