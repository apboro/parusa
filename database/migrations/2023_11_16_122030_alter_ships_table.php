<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::table('ships')->whereNull('provider_id')->update(['provider_id'=>5]);
    }

    public function down(): void
    {
        Schema::table('ships', function (Blueprint $table) {
            //
        });
    }
};
