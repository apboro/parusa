<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ship_seat_category_has_ticket_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('ship_id');
            $table->unsignedTinyInteger('seat_category_id');
            $table->unsignedSmallInteger('ticket_grade_id');

            $table->foreign('ship_id')->on('ships')
                ->references('id');
            $table->foreign('seat_category_id')->on('dictionary_seat_categories')
                ->references('id');
            $table->foreign('ticket_grade_id')->on('dictionary_ticket_grades')
                ->references('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ship_seat_category_has_ticket_grades');
    }
};
