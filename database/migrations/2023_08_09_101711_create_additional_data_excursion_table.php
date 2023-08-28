<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('additional_data_excursions', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('provider_id');
            $table->unsignedSmallInteger('excursion_id');
            $table->boolean('provider_excursion_status')->nullable();
            $table->string('provider_excursion_id')->nullable();

            $table->foreign('excursion_id')
                ->on('excursions')
                ->references('id')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('provider_id')
                ->on('dictionary_providers')
                ->references('id')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('additional_data_excursions');
    }
};
