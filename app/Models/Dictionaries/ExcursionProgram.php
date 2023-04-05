<?php

namespace App\Models\Dictionaries;

use App\Models\Excursions\Excursion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
