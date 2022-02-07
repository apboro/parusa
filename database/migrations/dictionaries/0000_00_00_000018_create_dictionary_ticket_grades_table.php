<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDictionaryTicketGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('dictionary_ticket_grades', static function (Blueprint $table) {
            $table->unsignedSmallInteger('id', true)->from(1000);
            $table->string('name');
            $table->boolean('enabled')->nullable()->default(true);
            $table->integer('order')->nullable()->default(0);
            $table->boolean('locked')->nullable()->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('dictionary_ticket_grades');
    }
}
