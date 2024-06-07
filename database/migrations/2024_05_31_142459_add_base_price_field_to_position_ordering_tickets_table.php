<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('position_ordering_tickets', function (Blueprint $table) {
            $table->unsignedInteger('base_price')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('position_ordering_tickets', function (Blueprint $table) {
            $table->dropColumn('base_price');
        });
    }
};
