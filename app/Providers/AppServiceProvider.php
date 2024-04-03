<?php

namespace App\Providers;

use App\Models\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
//        Model::preventsLazyLoading();

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'common'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'dictionaries'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'user'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'positions'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'partner'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'account'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'sails'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'tickets'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'pos'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'payments'));
            $this->loadMigrationsFrom(database_path('migrations' . DIRECTORY_SEPARATOR . 'promoCode'));
        }
    }
}
