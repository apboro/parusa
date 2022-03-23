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
    public function up(): void
    {
        Schema::create('dictionary_account_transaction_types', static function (Blueprint $table) {

            $table->unsignedTinyInteger('id', true);
            $table->string('name');
            $table->boolean('enabled')->nullable()->default(true);
            $table->unsignedTinyInteger('order')->nullable()->default(0);

            $table->tinyInteger('sign');

            $table->unsignedTinyInteger('parent_type_id')->nullable();
            $table->boolean('final')->nullable()->default(true);
            $table->string('next_title')->nullable()->default(null);

            $table->boolean('has_reason')->nullable()->default(false);
            $table->string('reason_title')->nullable()->default(null);
            $table->boolean('has_reason_date')->nullable()->default(false);
            $table->string('reason_date_title')->nullable()->default(null);

            $table->boolean('editable')->nullable()->default(false);
            $table->boolean('deletable')->nullable()->default(false);

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
        Schema::dropIfExists('dictionary_account_transaction_types');
    }
}
