<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

abstract class GenericSeeder extends Seeder
{
    protected array $data = [];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(empty($this->data)) {
            return;
        }

        foreach ($this->data as $class => $sets) {
            /** @var \Illuminate\Database\Eloquent\Model $class */
            foreach ($sets as $id => $attributes) {
                $model = $class->query()->firstOrNew(['id' => $id]);
                $model->setRawAttributes($attributes);
                $model->save();
            }
        }
    }
}
