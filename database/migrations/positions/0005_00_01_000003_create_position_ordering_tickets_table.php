<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionOrderingTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('position_ordering_tickets', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('position_id');

            $table->unsignedBigInteger('trip_id');
            $table->unsignedSmallInteger('grade_id');

            $table->unsignedSmallInteger('quantity');

            $table->timestamps();

            $table->foreign('position_id')->references('id')->on('positions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('trip_id')->references('id')->on('trips')->cascadeOnUpdate()->cascadeOnDelete();
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
        Schema::dropIfExists('position_ordering_tickets');
    }
}
