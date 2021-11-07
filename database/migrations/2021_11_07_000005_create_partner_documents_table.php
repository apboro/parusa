<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_documents', function (Blueprint $table) {
            $table->id();

            $table->uuid('partner_id')->index();

            $table->string('original_filename');
            $table->string('local_filename');

            $table->timestamps();

            $table->foreign('partner_id')->references('id')->on('partners')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_documents');
    }
}
