<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('additional_data_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('provider_id');
            $table->unsignedInteger('ticket_id');
            $table->string('provider_ticket_uuid', 60)->nullable();
            $table->unsignedBigInteger('provider_ticket_id')->nullable();

            $table->foreign('provider_id')
                ->on('dictionary_providers')
                ->references('id')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('ticket_id')
                ->on('tickets')
                ->references('id')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('additional_data_tickets');
    }
};
