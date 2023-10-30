<?php

use App\Models\Integration\AdditionalDataTicket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tickets = DB::table('tickets')
            ->where('neva_travel_ticket', '!=', 0)
            ->orderBy('id')
            ->chunk(100, function ($tickets) {
                foreach ($tickets as $ticket) {
                    AdditionalDataTicket::create([
                        'provider_id' => 10,
                        'ticket_id' => $ticket->id,
                    ]);
                }
            });
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedSmallInteger('provider_id')->nullable();
        });

        DB::table('tickets')->where('neva_travel_ticket', '!=', 0)->update(['provider_id' => 10]);

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('neva_travel_ticket');
            $table->foreign('provider_id')
                ->on('dictionary_providers')
                ->references('id')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            //
        });
    }
};
