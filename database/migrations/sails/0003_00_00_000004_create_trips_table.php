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
    public function up(): void
    {
        Schema::create('trips', static function (Blueprint $table) {
            $table->unsignedInteger('id', true);

            $table->dateTime('start_at');
            $table->dateTime('end_at');

            $table->unsignedSmallInteger('start_pier_id');
            $table->unsignedSmallInteger('end_pier_id');

            $table->unsignedSmallInteger('ship_id');
            $table->unsignedSmallInteger('excursion_id');

            $table->unsignedTinyInteger('status_id')->default(TripStatus::default);
            $table->unsignedTinyInteger('sale_status_id')->default(TripSaleStatus::default);

            $table->unsignedSmallInteger('tickets_total');

            $table->unsignedTinyInteger('discount_status_id')->default(TripDiscountStatus::default);

            $table->unsignedSmallInteger('cancellation_time');

            $table->timestamps();

            $table->foreign('start_pier_id')->references('id')->on('piers')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('end_pier_id')->references('id')->on('piers')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('ship_id')->references('id')->on('ships')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('excursion_id')->references('id')->on('excursions')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('status_id')->references('id')->on('dictionary_trip_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('sale_status_id')->references('id')->on('dictionary_trip_sale_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('discount_status_id')->references('id')->on('dictionary_trip_discount_statuses')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
}
