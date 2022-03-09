<?php

use App\Models\Dictionaries\PiersStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPositionOrderTerminalColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('position_ordering_tickets', static function (Blueprint $table) {
            $table->unsignedBigInteger('terminal_id')->nullable();

            $table->foreign('terminal_id')->references('id')->on('terminals')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('position_ordering_tickets', static function (Blueprint $table) {
            $table->dropColumn('terminal_id');
        });
    }
}
