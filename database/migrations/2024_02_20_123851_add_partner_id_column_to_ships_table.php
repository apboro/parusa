<?php

use App\Models\Partner\Partner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ships', function (Blueprint $table) {
            $table->unsignedSmallInteger('partner_id')->nullable();
            $table->foreign('partner_id')
                ->on('partners')
                ->references('id')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('ships', function (Blueprint $table) {
            $table->dropConstrainedForeignId('partner_id');
        });
    }
};
