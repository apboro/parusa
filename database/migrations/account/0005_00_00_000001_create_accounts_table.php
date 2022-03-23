<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('accounts', static function (Blueprint $table) {

            $table->unsignedSmallInteger('id', true);

            $table->unsignedSmallInteger('partner_id')->index();

            $table->bigInteger('amount')->default(0);
            $table->bigInteger('limit')->default(0);

            $table->timestamps();

            $table->foreign('partner_id')->references('id')->on('partners')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
}
