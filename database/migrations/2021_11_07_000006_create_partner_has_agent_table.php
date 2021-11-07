<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerHasAgentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        Schema::create('partner_has_agent', function (Blueprint $table) {

            $table->uuid('partner_id')->index();
            $table->uuid('agent_id')->index();

            $table->boolean('active')->nullable()->default('true');
            $table->string('position')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->foreign('partner_id')->references('id')->on('partners')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('agent_id')->references('id')->on('agents')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('partner_has_agent');
    }
}
