<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provider_trip', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('provider_id');
            $table->unsignedBigInteger('trip_id');
            $table->string('provider_trip_id', 50)->nullable();
            $table->string('provider_price_id', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_trip');
    }
};
