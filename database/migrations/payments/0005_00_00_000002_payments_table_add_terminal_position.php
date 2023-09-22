<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PaymentsTableAddTerminalPosition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('payments', static function (Blueprint $table) {
            $table->unsignedSmallInteger('terminal_id')->nullable();
            $table->unsignedSmallInteger('position_id')->nullable();

            $table->foreign('terminal_id')->references('id')->on('terminals')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('position_id')->references('id')->on('positions')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumns('payments', ['terminal_id', 'position_id'])) {
            Schema::table('payments', static function (Blueprint $table) {
                $table->dropForeign(['terminal_id']);
                $table->dropForeign(['position_id']);

                $table->dropColumn('terminal_id');
                $table->dropColumn('position_id');
            });
        }
    }
}
