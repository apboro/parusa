<?php

namespace App\Jobs;

use App\Models\Order\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FakeLifePosNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Order $order)
    {
        $this->connection='database';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = [
            'type_of'=>'SalePayment',
            'guid' => Str::random(8),
            'total_sum' => ['value' => $this->order->total()],
            'sale' => ['guid' => $this->order->external_id],
            'sum_by_cash' => ['value' => rand(10, 1000)],
            'fiscal_document' => ['guid' => Str::random(8)]
            ];
        Http::timeout(10)->post(config('app.url').'/services/lifepos/notifications', $data);
    }
}
