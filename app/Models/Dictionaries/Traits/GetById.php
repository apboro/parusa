<?php

namespace App\Models\Dictionaries\Traits;

use App\Models\Model;

trait GetById
{
    /**
     * Get dictionary item instance by id.
     *
     * @param int $id
     *
     * @return  \App\Models\Model|null
     */
    public static function get(int $id): ?Model
    {
        /** @var Model $model */
        $model = self::query()->where('id', $id)->first();

        return $model ?? null;
    }
}
