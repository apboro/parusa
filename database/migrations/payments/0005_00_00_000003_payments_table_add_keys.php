<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PaymentsTableAddKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('payments', static function (Blueprint $table) {
            $table->unique(['gate', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('payments', static function (Blueprint $table) {
            $table->dropUnique(['gate', 'external_id']);
        });
    }
}
