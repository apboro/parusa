<?php

namespace Database\Seeders;

use Database\Seeders\Dictionaries\AccountTransactionTypesSeeder;
use Database\Seeders\Dictionaries\ContactTypesSeeder;
use Database\Seeders\Dictionaries\OrderTypesSeeder;
use Database\Seeders\Dictionaries\StatusesSeeder;
use Database\Seeders\Dictionaries\UserRolesSeeder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    protected array $seeders = [
        UserRolesSeeder::class,
        StatusesSeeder::class,
        ContactTypesSeeder::class,
        AccountTransactionTypesSeeder::class,
        OrderTypesSeeder::class
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function run(): void
    {
        foreach ($this->seeders as $seederClass) {

            /** @var GenericSeeder $seeder */
            $seeder = $this->container->make($seederClass);

            $seeder->run();
        }
    }
}
