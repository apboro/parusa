<?php

use App\Models\Dictionaries\TripDiscountStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsRatesListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tickets_rates_list', static function (Blueprint $table) {

            $table->unsignedSmallInteger('id', true);
            $table->unsignedSmallInteger('excursion_id');

            $table->date('start_at');
            $table->date('end_at');

            $table->string('caption');

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
        Schema::dropIfExists('tickets_rates_list');
    }
}
