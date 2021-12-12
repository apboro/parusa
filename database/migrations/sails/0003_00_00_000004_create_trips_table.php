<?php

use App\Models\Dictionaries\TripDiscountStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();

            $table->timestamp('start_at');
            $table->timestamp('end_at');

            $table->unsignedBigInteger('start_pier_id');
            $table->unsignedBigInteger('end_pier_id');

            $table->unsignedBigInteger('ship_id');
            $table->unsignedBigInteger('excursion_id');

            $table->unsignedInteger('status_id')->default(TripStatus::default);
            $table->unsignedInteger('sale_status_id')->default(TripSaleStatus::default);

            $table->unsignedInteger('tickets_count');

            $table->unsignedInteger('discount_status_id')->default(TripDiscountStatus::default);

            $table->unsignedInteger('cancellation_time');

            $table->timestamps();

            $table->foreign('start_pier_id')->references('id')->on('')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('end_pier_id')->references('id')->on('')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('ship_id')->references('id')->on('')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('excursion_id')->references('id')->on('')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('status_id')->references('id')->on('')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('sale_status_id')->references('id')->on('')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('discount_status_id')->references('id')->on('')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trips');
    }
}
