<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDictionaryContactTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dictionary_contact_types', function (Blueprint $table) {

            $table->increments('id')->from(1000)->primary();

            $table->boolean('enabled')->nullable()->default(true);
            $table->boolean('lock')->nullable()->default(false);
            $table->string('type')->nullable();

            $table->string('name');
            $table->boolean('has_additional')->nullable()->default(false);
            $table->string('link_pattern')->nullable()->default(null);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dictionary_contact_types');
    }
}
