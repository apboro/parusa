<?php

namespace App\Models\Dictionaries;

use App\Models\Excursions\Excursion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property int $id
 * @property string $name
 * @property bool $enabled
 * @property int $order
 * @property Collection<Excursion> $excursions
 */
class ExcursionProgram extends AbstractDictionary
{
    /** @var string Referenced table name. */
    protected $table = 'dictionary_excursion_programs';

    /**
     * All excursions having this program
     *
     * @return BelongsToMany
     */
    public function excursions(): BelongsToMany
    {
        return $this->belongsToMany(Excursion::class, 'excursion_has_programs', 'program_id', 'excursion_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }
}
