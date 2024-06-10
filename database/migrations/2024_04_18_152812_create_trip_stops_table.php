<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripStopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trip_stops', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('trip_id');
            $table->unsignedSmallInteger('stop_pier_id');
            $table->dateTime('stop_at')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->unsignedInteger('terminal_price_delta');
            $table->unsignedInteger('partner_price_delta');
            $table->unsignedInteger('site_price_delta');
            $table->timestamps();

            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trip_stops');
    }
}
