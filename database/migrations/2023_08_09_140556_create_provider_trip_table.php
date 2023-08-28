<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('additional_data_trips', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('provider_id');
            $table->unsignedInteger('trip_id');
            $table->string('provider_trip_id', 50)->nullable();
            $table->string('provider_price_id', 50)->nullable();

            $table->foreign('provider_id')
                ->on('dictionary_providers')
                ->references('id')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('trip_id')
                ->on('trips')
                ->references('id')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('additional_data_trips');
    }
};
