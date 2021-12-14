<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'common'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'dictionaries'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'user'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'staff'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'partner'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'account'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'sails'));
        }
    }
}
