<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ticket_rates', function (Blueprint $table) {
            $table->enum('backward_price_type', ['fixed', 'percents'])->default('fixed');
            $table->unsignedInteger('backward_price_value')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('ticket_rates', function (Blueprint $table) {
            $table->dropColumn(['backward_price_type', 'backward_price_value']);
        });
    }
};
