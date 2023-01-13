<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class QueueRunOnce extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:once';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process sending queue';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->call('queue:work', ['connection' => 'database', '--stop-when-empty' => true]);

        return 0;
    }
}
