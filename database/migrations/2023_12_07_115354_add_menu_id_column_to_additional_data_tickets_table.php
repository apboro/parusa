<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('additional_data_tickets', function (Blueprint $table) {
            $table->string('menu_id', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('additional_data_tickets', function (Blueprint $table) {
            $table->dropColumn('menu_id');
        });
    }
};
