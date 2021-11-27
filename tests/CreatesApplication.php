<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;

trait CreatesApplication
{
    /**
     * If true, setup has run at least once.
     *
     * @var boolean
     */
    private static bool $setUpHasRunOnce = false;

    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        /** @var Application $app */
        $app->make(Kernel::class)->bootstrap();

        if (!static::$setUpHasRunOnce) {

            Artisan::call('migrate:refresh');
            Artisan::call('db:seed');

            static::$setUpHasRunOnce = true;
        }

        return $app;
    }
}
