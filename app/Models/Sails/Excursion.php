<?php

namespace App\Models\Sails;

use App\Exceptions\Sails\WrongExcursionStatusException;
use App\Interfaces\Statusable;
use App\Models\Common\Image;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\Interfaces\AsDictionary;
use App\Models\Model;
use App\Models\Tickets\TicketsRatesList;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property int $status_id
 *
 * @property ExcursionStatus $status
 * @property Collection $programs
 * @property ExcursionInfo $info
 * @property Collection $images
 * @property Collection $ratesLists
 */
class Excursion extends Model implements Statusable, AsDictionary
{
    use HasStatus, HasFactory, ExcursionAsDictionary;

    /** @var array Default attributes. */
    protected $attributes = [
        'status_id' => ExcursionStatus::default,
    ];

    /**
     * Excursion status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(ExcursionStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for excursion.
     *
     * @param int|ExcursionStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongExcursionStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(ExcursionStatus::class, $status, WrongExcursionStatusException::class, $save);
    }

    /**
     * Excursion programs.
     *
     * @return  BelongsToMany
     */
    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(ExcursionProgram::class, 'excursion_has_programs', 'excursion_id', 'program_id');
    }

    /**
     * Excursion info.
     *
     * @return  HasOne
     */
    public function info(): HasOne
    {
        return $this->hasOne(ExcursionInfo::class, 'excursion_id', 'id')->withDefault();
    }

    /**
     * Excursion images.
     *
     * @return  BelongsToMany
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'excursion_has_image', 'excursion_id', 'image_id');
    }

    /**
     * Excursion ticket rates.
     *
     * @return  HasMany
     */
    public function ratesLists(): HasMany
    {
        return $this->hasMany(TicketsRatesList::class, 'excursion_id', 'id');
    }
}
