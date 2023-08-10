<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provider_excursion', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('provider_id');
            $table->unsignedBigInteger('excursion_id');
            $table->boolean('provider_excursion_status')->nullable();
            $table->string('provider_excursion_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_excursion');
    }
};
