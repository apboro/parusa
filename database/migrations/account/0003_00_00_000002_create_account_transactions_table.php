<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_transactions', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('account_id')->index();

            $table->unsignedInteger('type_id');
            $table->unsignedInteger('status_id');

            $table->unsignedBigInteger('amount');

            $table->unsignedBigInteger('committed_by');

            $table->text('comments')->nullable();

            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('type_id')->references('id')->on('dictionary_account_transaction_types')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('status_id')->references('id')->on('dictionary_account_transaction_statuses')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('committed_by')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_transactions');
    }
}
