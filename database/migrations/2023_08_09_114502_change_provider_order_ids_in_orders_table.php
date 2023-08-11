<?php

use App\Models\AdditionalDataOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $orders = DB::table('orders')
            ->whereNotNull('neva_travel_id')
            ->get(['id', 'neva_travel_order_number', 'neva_travel_id']);

        foreach ($orders as $order) {
            AdditionalDataOrder::create([
                'provider_id' => 10,
                'order_id' => $order->id,
                'provider_order_id' => $order->neva_travel_order_number,
                'provider_order_uuid' => $order->neva_travel_id,
            ]);
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['neva_travel_order_number', 'neva_travel_id']);
        });

    }

    public function down(): void
    {

    }

};
