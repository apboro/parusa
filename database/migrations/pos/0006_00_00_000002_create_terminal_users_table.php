<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTerminalUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('terminal_users', static function (Blueprint $table) {
            $table->unsignedBigInteger('terminal_id');
            $table->unsignedBigInteger('position_id');

            $table->foreign('terminal_id')->references('id')->on('terminals')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('position_id')->references('id')->on('positions')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('terminal_users');
    }
}
