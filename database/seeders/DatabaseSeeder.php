<?php

namespace Database\Seeders;

use Database\Seeders\Dictionaries\AccountTransactionTypesSeeder;
use Database\Seeders\Dictionaries\ContactTypesSeeder;
use Database\Seeders\Dictionaries\ExcursionTypesSeeder;
use Database\Seeders\Dictionaries\HitSeeder;
use Database\Seeders\Dictionaries\OrderTypesSeeder;
use Database\Seeders\Dictionaries\PartnerTypesSeeder;
use Database\Seeders\Dictionaries\ProviderTypesSeeder;
use Database\Seeders\Dictionaries\StatusesSeeder;
use Database\Seeders\Dictionaries\TypesSeeder;
use Database\Seeders\Dictionaries\UserRolesSeeder;
use Database\Seeders\Dictionaries\WorkShiftStatusesSeeder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    protected array $seeders = [
        UserRolesSeeder::class,
        HitSeeder::class,
        StatusesSeeder::class,
        TypesSeeder::class,
        ProviderTypesSeeder::class,
        ExcursionTypesSeeder::class,
        ContactTypesSeeder::class,
        AccountTransactionTypesSeeder::class,
        OrderTypesSeeder::class,
        PartnerTypesSeeder::class,
        WorkShiftStatusesSeeder::class,
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
