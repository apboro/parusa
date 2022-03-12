<?php

use App\Models\Dictionaries\AccountTransactionStatus;
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
    public function up(): void
    {
        Schema::create('account_transactions', static function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('account_id')->index();

            $table->unsignedInteger('type_id');
            $table->unsignedInteger('status_id')->default(AccountTransactionStatus::default);

            $table->unsignedBigInteger('amount');

            $table->dateTime('timestamp');

            $table->string('reason')->nullable();
            $table->date('reason_date')->nullable();

            $table->unsignedBigInteger('committer_id')->nullable();

            $table->unsignedInteger('order_id')->nullable();
            $table->unsignedInteger('ticket_id')->nullable();
            $table->enum('commission_type', ['fixed', 'percents'])->nullable();
            $table->unsignedInteger('commission_value')->nullable();

            $table->text('comments')->nullable();

            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('type_id')->references('id')->on('dictionary_account_transaction_types')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('status_id')->references('id')->on('dictionary_account_transaction_statuses')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('committer_id')->references('id')->on('positions')->restrictOnDelete()->cascadeOnUpdate();

            $table->foreign('order_id')->references('id')->on('orders')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('ticket_id')->references('id')->on('tickets')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
}
