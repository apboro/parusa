<?php

use App\Models\Dictionaries\ExcursionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExcursionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('excursions', static function (Blueprint $table) {
            $table->unsignedSmallInteger('id', true);

            $table->string('name');
            $table->unsignedTinyInteger('status_id')->default(ExcursionStatus::default);

            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('dictionary_excursion_statuses')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('excursions');
    }
}
