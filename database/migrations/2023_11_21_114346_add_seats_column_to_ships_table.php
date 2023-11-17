<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ships', function (Blueprint $table) {
            $table->boolean('ship_has_seats_scheme')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('ships', function (Blueprint $table) {
            $table->dropColumn('ship_has_seats_scheme');
        });
    }
};
