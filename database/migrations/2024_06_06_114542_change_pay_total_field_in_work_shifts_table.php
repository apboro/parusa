<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('work_shifts', function (Blueprint $table) {
            $table->integer('pay_total')->nullable()->change();
            $table->integer('paid_out')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('work_shifts', function (Blueprint $table) {
            //
        });
    }
};
