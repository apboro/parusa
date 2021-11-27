<?php

namespace Database\Seeders;

use App\Models\User\User;
use Database\Factories\User\UserFactory;
use Database\Seeders\Dictionaries\ContactTypesSeeder;
use Database\Seeders\Dictionaries\PartnerTypesSeeder;
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
        PartnerTypesSeeder::class,
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
