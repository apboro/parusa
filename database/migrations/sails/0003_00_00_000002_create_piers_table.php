<?php

use App\Models\Dictionaries\PiersStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('piers', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->unsignedInteger('status_id')->default(PiersStatus::default);

            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('dictionary_pier_statuses')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('piers');
    }
}
