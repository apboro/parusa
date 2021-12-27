<?php

namespace App\Models\Dictionaries;

use App\Models\Model;

/**
 * @property int $id
 * @property string $name
 */
abstract class AbstractDictionary extends Model
{
    /** @var array Attributes casting. */
    protected $casts = [
        'enabled' => 'boolean',
        'lock' => 'boolean',
        'order' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get dictionary item instance by id.
     *
     * @param int $id
     *
     * @return  Model|null
     */
    public static function get(int $id): ?Model
    {
        /** @var Model $model */
        $model = self::query()->where('id', $id)->first();

        return $model ?? null;
    }
}
