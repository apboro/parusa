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
    public function up()
    {
        Schema::create('excursion_has_programs', function (Blueprint $table) {

            $table->unsignedInteger('excursion_id');
            $table->unsignedInteger('program_id');

            $table->foreign('excursion_id')->references('id')->on('excursions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('program_id')->references('id')->on('dictionary_excursion_programs')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('excursion_has_programs');
    }
}
