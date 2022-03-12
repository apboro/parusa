<?php

namespace App\Console\Commands;

use App\LifePos\LifePosOrg;
use Illuminate\Console\Command;
use JsonException;

class LifePosInitCallbacks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lifepos:callback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set LifePos callbacks';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws JsonException
     */
    public function handle(): int
    {
        LifePosOrg::setCallBackUrl();

        return 0;
    }
}
