<?php

namespace App\Console\Commands;

use App\LifePos\LifePosSync;
use App\Models\Order\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncLifePos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lifepos:sync {--a}{--v}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync payments with lifepos';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $verbose = $this->option('v');

        $startTime = microtime(true);

        if ($this->option('a')) {
            $ordersUpdateFrom = 0;
        } else {
            $now = Carbon::now();
            $ordersUpdateFrom = Order::query()->whereDate('created_at', '>=', $now->addMonths(-1))->oldest('created_at')->value('id');
        }
        $ordersUpdateTo = Order::query()->max('id');

        if ($verbose) {
            $this->info("Updating orders from: $ordersUpdateFrom, to: $ordersUpdateTo");
        }

        while ($ordersUpdateFrom <= $ordersUpdateTo) {
            $localTo = min($ordersUpdateFrom + 100, $ordersUpdateTo);
            $this->info("Fetching payments for orders: $ordersUpdateFrom - $localTo");

            LifePosSync::syncMissingPayments($ordersUpdateFrom, $localTo);

            $ordersUpdateFrom += 100;
        }

        if ($verbose) {
            $this->info("Synchronizing returns");
        }

        LifePosSync::syncReturns();

        $endTime = (microtime(true) - $startTime);

        if ($verbose) {
            $this->info("Sync is done ($endTime seconds)");
        }

        return 0;
    }
}
