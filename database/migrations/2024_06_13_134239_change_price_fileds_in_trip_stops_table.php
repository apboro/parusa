<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trip_stops', function (Blueprint $table) {
            $table->float('terminal_price_delta')->change();
            $table->float('site_price_delta')->change();
            $table->float('partner_price_delta')->change();
        });
    }

    public function down(): void
    {
        Schema::table('trip_stops', function (Blueprint $table) {
            //
        });
    }
};
