<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->dropColumn('has_return_trip');
            $table->unsignedSmallInteger('reverse_excursion_id')->nullable();
            $table->foreign('reverse_excursion_id')->on('excursions')->references('id');
        });
    }

    public function down(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->boolean('has_return_trip')->nullable();
        });
    }
};
