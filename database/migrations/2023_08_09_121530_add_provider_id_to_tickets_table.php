<?php

use App\Models\ProviderTicket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tickets = DB::table('tickets')->where('neva_travel_ticket','!=', 0)->get();
        foreach ($tickets as $ticket) {
            ProviderTicket::create([
                'provider_id' => 10,
                'ticket_id' => $ticket->id,
            ]);
        }
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedSmallInteger('provider_id')->nullable();
        });

        DB::table('tickets')->where('neva_travel_ticket','!=', 0)->update(['provider_id' => 10]);

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('neva_travel_ticket');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            //
        });
    }
};
