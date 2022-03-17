<?php

use App\Models\Dictionaries\TerminalStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTerminalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('terminals', static function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('status_id')->default(TerminalStatus::default);
            $table->unsignedBigInteger('pier_id');

            $table->string('workplace_id')->nullable();
            $table->string('outlet_id')->nullable();

            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('dictionary_terminal_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('pier_id')->references('id')->on('piers')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('terminals');
    }
}
