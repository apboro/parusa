<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->unsignedSmallInteger('id', true);
            $table->unsignedSmallInteger('ship_id');
            $table->unsignedSmallInteger('seat_number');
            $table->unsignedTinyInteger('seat_category_id')->nullable();

            $table->foreign('ship_id')->on('ships')->references('id');
            $table->foreign('seat_category_id')->on('dictionary_seat_categories')->references('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
