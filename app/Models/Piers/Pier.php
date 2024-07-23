<?php

namespace App\Models\Piers;

use App\Exceptions\Piers\WrongPierStatusException;
use App\Interfaces\Statusable;
use App\Models\Common\Image;
use App\Models\Dictionaries\Interfaces\AsDictionary;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Dictionaries\Provider;
use App\Models\Model;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property Collection $mapImages
 * @property string|null $external_id
 * @property string|null $external_parent_id
 * @property int $provider_id
 * @property string|null $source
 * @property BelongsTo $provider
 */
class Pier extends Model implements Statusable, AsDictionary
{
    use HasStatus, HasFactory, PierAsDictionary;

    protected $guarded = [];
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
     * @throws WrongPierStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(PiersStatus::class, $status, WrongPierStatusException::class, $save);
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

    /**
     * Pier map images.
     *
     * @return  BelongsToMany
     */
    public function mapImages(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'pier_has_map_image', 'pier_id', 'image_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status_id', PiersStatus::active);
    }

}
