<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->boolean('use_seat_scheme')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('excursions', function (Blueprint $table) {
            $table->dropColumn('use_seat_scheme');
        });
    }
};
