<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tickets', static function (Blueprint $table) {

            $table->unsignedInteger('id', true);
            $table->unsignedTinyInteger('status_id');
            $table->unsignedInteger('order_id');

            $table->unsignedInteger('trip_id');
            $table->unsignedSmallInteger('grade_id');
            $table->unsignedInteger('base_price');

            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('dictionary_ticket_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('order_id')->references('id')->on('orders')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('trip_id')->references('id')->on('trips')->restrictOnDelete()->restrictOnUpdate();
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
        Schema::dropIfExists('tickets');
    }
}
