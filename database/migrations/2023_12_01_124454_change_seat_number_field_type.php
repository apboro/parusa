<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trip_has_seats', function (Blueprint $table) {
            $table->string('seat_number', 7)->change();
        });
        Schema::table('seats', function (Blueprint $table) {
            $table->string('seat_number',7)->change();
        });
    }

    public function down(): void
    {
        Schema::table('trip_has_seats', function (Blueprint $table) {
            $table->unsignedSmallInteger('seat_number')->change();
        });
        Schema::table('seats', function (Blueprint $table) {
            $table->unsignedSmallInteger('seat_number')->change();
        });
    }
};
