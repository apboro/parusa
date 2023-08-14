<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('position_ordering_tickets', function (Blueprint $table) {
            $table->unsignedInteger('parent_ticket_id')->nullable();

            $table->foreign('parent_ticket_id')
                ->on('position_ordering_tickets')
                ->references('id')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('position_ordering_tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_ticket_id');
        });
    }
};
