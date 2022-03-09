<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrdersTerminalIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('orders', static function (Blueprint $table) {

            $table->unsignedBigInteger('terminal_id')->nullable();

            $table->foreign('terminal_id')->references('id')->on('terminals')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('orders', static function (Blueprint $table) {

            $table->dropColumn('terminal_id');
        });
    }
}
