<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('position_ordering_tickets', function (Blueprint $table) {
            $table->unsignedSmallInteger('seat_id')->nullable();
            $table->foreign('seat_id')
                ->on('seats')
                ->references('id')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('position_ordering_tickets', function (Blueprint $table) {
            $table->dropColumn('seat_id');
        });
    }
};
