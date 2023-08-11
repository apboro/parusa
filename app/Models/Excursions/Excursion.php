<?php

namespace App\Models\Excursions;

use App\Exceptions\Excursions\WrongExcursionStatusException;
use App\Interfaces\Statusable;
use App\Models\Common\Image;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\Interfaces\AsDictionary;
use App\Models\Dictionaries\Provider;
use App\Models\Model;
use App\Models\Partner\Partner;
use App\Models\AdditionalDataExcursion;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketsRatesList;
use App\Traits\HasStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string $name_receipt
 * @property int $status_id
 * @property int $provider_id
 * @property bool $only_site
 * @property bool $is_single_ticket
 * @property int $reverse_excursion_id
 *
 * @property ExcursionStatus $status
 * @property Collection $programs
 * @property ExcursionInfo $info
 * @property Collection $images
 * @property Collection $tripImages
 * @property Collection $ratesLists
 * @property Collection $partnerShowcaseHide
 * @property Collection<Trip> $trips
 * @property hasOne $reverseExcursion
 * @property AdditionalDataExcursion $additionalData
 */
class Excursion extends Model implements Statusable, AsDictionary
{
    use HasStatus, HasFactory, ExcursionAsDictionary;

    protected $guarded = [];

    protected $casts = [
        'only_site' => 'bool',
        'is_single_ticket' => 'bool',
    ];

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
     * All trips for this excursion
     *
     * @return HasMany
     */
    public function trips(): hasMany
    {
        return $this->hasMany(Trip::class, 'excursion_id', 'id');
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
     * Excursion trip images.
     *
     * @return  BelongsToMany
     */
    public function tripImages(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'excursion_has_trip_image', 'excursion_id', 'image_id');
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

    /**
     * Check if excursion has tickets rates for given date or today if no date given.
     *
     * @param Carbon|null $date
     *
     * @return  bool
     */
    public function hasRateForDate(?Carbon $date): bool
    {
        $date = $date ?? new Carbon();

        $count = $this->ratesLists()
            ->whereDate('start_at', '<=', $date)
            ->whereDate('end_at', '>=', $date)
            ->count();

        return $count > 0;
    }

    /**
     * Get tickets rates for given date or today if no date given.
     * P.S. Only one (or null) rate list exists for any date (software controlled).
     *
     * @param Carbon|null $date
     *
     * @return  TicketsRatesList|null
     */
    public function rateForDate(?Carbon $date): ?TicketsRatesList
    {
        $date = $date ?? new Carbon();

        /** @var TicketsRatesList $rate */
        $rate = $this->ratesLists()
            ->whereDate('start_at', '<=', $date)
            ->whereDate('end_at', '>=', $date)
            ->first();

        return $rate ?? null;
    }

    /**
     * Partners this excursion showing disabled in a showcase.
     *
     * @return  BelongsToMany
     */
    public function partnerShowcaseHide(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'partner_excursion_showcase_disabling', 'excursion_id','partner_id');
    }

    public function reverseExcursion()
    {
        return $this->hasOne(Excursion::class, 'id', 'reverse_excursion_id');
    }

    public function additionalData(): hasOne
    {
        return $this->hasOne(AdditionalDataExcursion::class, 'excursion_id', 'id');
    }
}
