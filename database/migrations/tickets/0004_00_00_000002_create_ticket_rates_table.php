<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('ticket_rates', static function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('rate_id');

            $table->unsignedSmallInteger('grade_id');

            $table->unsignedInteger('base_price');
            $table->unsignedInteger('min_price');
            $table->unsignedInteger('max_price');
            $table->enum('commission_type', ['fixed', 'percents']);
            $table->unsignedInteger('commission_value');

            $table->timestamps();

            $table->foreign('rate_id')->references('id')->on('tickets_rates_list')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('grade_id')->references('id')->on('dictionary_ticket_grades')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_rates');
    }
}
