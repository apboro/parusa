<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedSmallInteger('start_pier_id')->nullable();

            $table->foreign('start_pier_id')->references('id')->on('piers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign('tickets_start_pier_id_foreign');
        });
    }
};
