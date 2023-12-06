<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trip_has_seats', function (Blueprint $table) {
            $table->unsignedSmallInteger('id', true);
            $table->unsignedInteger('trip_id');
            $table->unsignedSmallInteger('seat_id');
            $table->unsignedTinyInteger('status_id');
            $table->timestamps();

            $table->foreign('seat_id')
                ->on('seats')
                ->references('id')->cascadeOnDelete();
            $table->foreign('trip_id')->on('trips')
                ->references('id')->cascadeOnDelete();
            $table->foreign('status_id')->on('dictionary_seat_statuses')
                ->references('id');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_has_seats');
    }
};
