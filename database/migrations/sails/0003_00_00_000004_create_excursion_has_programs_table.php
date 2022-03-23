<?php

use App\Models\Dictionaries\ExcursionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExcursionHasProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('excursion_has_programs', static function (Blueprint $table) {

            $table->unsignedSmallInteger('excursion_id');
            $table->unsignedSmallInteger('program_id');

            $table->foreign('excursion_id')->references('id')->on('excursions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('program_id')->references('id')->on('dictionary_excursion_programs')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('excursion_has_programs');
    }
}
