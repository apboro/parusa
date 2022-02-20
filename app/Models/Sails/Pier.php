<?php

namespace App\Models\Sails;

use App\Exceptions\Piers\WrongPierStatusException;
use App\Interfaces\Statusable;
use App\Models\Common\Image;
use App\Models\Dictionaries\Interfaces\AsDictionary;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Model;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property int $status_id
 *
 * @property PiersStatus $status
 * @property PierInfo $info
 * @property Collection $images
 */
class Pier extends Model implements Statusable, AsDictionary
{
    use HasStatus, HasFactory, PierAsDictionary;

    /** @var array Default attributes. */
    protected $attributes = [
        'status_id' => PiersStatus::default,
    ];

    /**
     * Pier status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(PiersStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for pier.
     *
     * @param int|PiersStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws \App\Exceptions\Piers\WrongPierStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(PiersStatus::class, $status, \App\Exceptions\Piers\WrongPierStatusException::class, $save);
    }

    /**
     * Pier info.
     *
     * @return  HasOne
     */
    public function info(): HasOne
    {
        return $this->hasOne(PierInfo::class, 'pier_id', 'id')->withDefault();
    }

    /**
     * Pier images.
     *
     * @return  BelongsToMany
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'pier_has_image', 'pier_id', 'image_id');
    }
}
