<?php

namespace Database\Seeders;

use Database\Seeders\Initial\PartnerTypesSeeder;
use Database\Seeders\Initial\ExcursionProgramsSeeder;
use Database\Seeders\Initial\TicketGradesSeeder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Seeder;

class InitialSeeder extends Seeder
{
    protected array $seeders = [
        TicketGradesSeeder::class,
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
