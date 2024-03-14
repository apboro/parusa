 y7<?php

 use App\Models\Dictionaries\Provider;
 use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::table('trips')->whereNull('provider_id')
            ->update(['provider_id' => Provider::scarlet_sails]);
        Schema::table('dictionary_providers', function (Blueprint $table) {
            $table->string('service')->nullable()->change();
            $table->boolean('locked')->default(false);
            $table->boolean('has_integration')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('dictionary_providers', function (Blueprint $table) {
            $table->dropColumn('locked');
            $table->dropColumn('has_integration');
        });
    }
};
