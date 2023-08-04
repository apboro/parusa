<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('backward_tickets', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('main_ticket_id')->nullable();
            $table->unsignedInteger('backward_ticket_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backward_tickets');
    }
};
