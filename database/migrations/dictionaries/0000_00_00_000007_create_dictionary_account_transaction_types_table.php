<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDictionaryAccountTransactionTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dictionary_account_transaction_types', function (Blueprint $table) {

            $table->increments('id');
            $table->string('name');
            $table->boolean('enabled')->nullable()->default(true);
            $table->integer('order')->nullable()->default(0);

            $table->smallInteger('sign');

            $table->unsignedInteger('parent_type_id')->nullable();
            $table->boolean('final')->nullable()->default(true);
            $table->string('next_title')->nullable()->default(null);

            $table->boolean('has_reason')->nullable()->default(false);
            $table->string('reason_title')->nullable()->default(null);
            $table->boolean('has_reason_date')->nullable()->default(false);
            $table->timestamp('reason_date')->nullable()->default(null);

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
        Schema::dropIfExists('dictionary_account_transaction_types');
    }
}
