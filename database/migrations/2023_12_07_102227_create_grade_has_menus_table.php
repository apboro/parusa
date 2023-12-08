<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grade_has_menus', function (Blueprint $table) {
            $table->unsignedSmallInteger('grade_id');
            $table->unsignedSmallInteger('menu_id');

            $table->foreign('grade_id')->on('dictionary_ticket_grades')
                ->references('id')->cascadeOnDelete();
            $table->foreign('menu_id')->on('menus')
                ->references('id')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_has_menus');
    }
};
