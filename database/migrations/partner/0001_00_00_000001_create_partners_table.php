<?php

use App\Models\Dictionaries\PartnerStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->unsignedInteger('type_id');

            $table->unsignedInteger('status_id')->default(PartnerStatus::default);

            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('dictionary_partner_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('type_id')->references('id')->on('dictionary_partner_types')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partners');
    }
}
