<?php

namespace App\Models\Sails;

use App\Exceptions\Trips\WrongTripDiscountStatusException;
use App\Exceptions\Trips\WrongTripSaleStatusException;
use App\Exceptions\Trips\WrongTripStatusException;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TripDiscountStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Excursions\Excursion;
use App\Models\Model;
use App\Models\Piers\Pier;
use App\Models\Ships\Ship;
use App\Models\Tickets\Ticket;
use App\Models\Tickets\TicketsRatesList;
use App\Settings;
use App\Traits\HasStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property Carbon $start_at
 * @property Carbon $end_at
 * @property int $start_pier_id
 * @property int $end_pier_id
 * @property int $ship_id
 * @property int $excursion_id
 * @property int $status_id
 * @property int $sale_status_id
 * @property int $tickets_total
 * @property int $discount_status_id
 * @property int $cancellation_time
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Pier $startPier
 * @property Pier $endPier
 * @property Ship $ship
 * @property Excursion $excursion
 * @property TripStatus $status
 * @property TripSaleStatus $saleStatus
 * @property TripDiscountStatus $discountStatus
 * @property Collection $chains
 */
class Trip extends Model implements Statusable
{
    use HasStatus, HasFactory;

    /** @var array Attributes casting. */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'tickets_total' => 'int',
        'cancellation_time' => 'int',
    ];

    /** @var array Default attributes. */
    protected $attributes = [
        'status_id' => TripStatus::default,
        'sale_status_id' => TripSaleStatus::default,
        'discount_status_id' => TripDiscountStatus::default,
        'cancellation_time' => null,
    ];

    /**
     * Cancellation time mutator. Returns default system value if tip is not exists.
     *
     * @param $value
     *
     * @return  int
     */
    public function getCancellationTimeAttribute($value): ?int
    {
        return $this->exists ? $value : Settings::get('default_cancellation_time', null, Settings::int);
    }

    /**
     * Accessor for name generation.
     *
     * @return  string|null
     */
    public function getNameAttribute(): ?string
    {
        return $this->exists ? 'Рейс №' . $this->id : null;
    }

    /**
     * Ship status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(TripStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for ship.
     *
     * @param int|TripStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongTripStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(TripStatus::class, $status, WrongTripStatusException::class, $save);
    }

    /**
     * Ship status.
     *
     * @return  HasOne
     */
    public function saleStatus(): HasOne
    {
        return $this->hasOne(TripSaleStatus::class, 'id', 'sale_status_id');
    }

    /**
     * Check and set new status for ship.
     *
     * @param int|TripSaleStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongTripSaleStatusException
     */
    public function setSaleStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(TripSaleStatus::class, $status, WrongTripSaleStatusException::class, $save, 'sale_status_id');
    }

    /**
     * Ship status.
     *
     * @return  HasOne
     */
    public function discountStatus(): HasOne
    {
        return $this->hasOne(TripDiscountStatus::class, 'id', 'discount_status_id');
    }

    /**
     * Check and set new status for ship.
     *
     * @param int|TripDiscountStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongTripDiscountStatusException
     */
    public function setDiscountStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(TripDiscountStatus::class, $status, WrongTripDiscountStatusException::class, $save, 'discount_status_id');
    }

    /**
     * Pier this trip starts.
     *
     * @return  HasOne
     */
    public function startPier(): HasOne
    {
        return $this->hasOne(Pier::class, 'id', 'start_pier_id');
    }

    /**
     * Pier this trip ends.
     *
     * @return  HasOne
     */
    public function endPier(): HasOne
    {
        return $this->hasOne(Pier::class, 'id', 'end_pier_id');
    }

    /**
     * Ship this trip at.
     *
     * @return  HasOne
     */
    public function ship(): HasOne
    {
        return $this->hasOne(Ship::class, 'id', 'ship_id');
    }

    /**
     * Excursion for this trip.
     *
     * @return  HasOne
     */
    public function excursion(): HasOne
    {
        return $this->hasOne(Excursion::class, 'id', 'excursion_id');
    }

    /**
     * Tickets for this trip.
     *
     * @return  HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Check if this trip has rate.
     *
     * @return bool
     */
    public function hasRate(): bool
    {
        $this->loadMissing('excursion');

        return $this->excursion->hasRateForDate($this->start_at);
    }

    /**
     * Is only AP site sales enabled.
     *
     * @return bool
     */
    public function isOnlySite(): bool
    {
        $this->loadMissing('excursion');

        return $this->excursion->only_site;
    }

    /**
     * Get rate list for this trip.
     *
     * @return TicketsRatesList|null
     */
    public function getRate(): ?TicketsRatesList
    {
        $this->loadMissing('excursion');

        return $this->excursion->rateForDate($this->start_at);
    }

    /**
     * Chained trips.
     *
     * @return  BelongsToMany
     */
    public function chains(): BelongsToMany
    {
        return $this->belongsToMany(TripChain::class, 'trip_chain_has_trip', 'trip_id', 'chain_id');
    }

    /**
     * Actual trips query for sale parts.
     *
     * @param bool $forRootSite
     *
     * @return  Builder
     */
    public static function saleTripQuery(bool $forRootSite = false): Builder
    {
        return Trip::query()
            ->where('start_at', '>', Carbon::now())
            ->whereIn('status_id', [TripStatus::regular])
            ->whereIn('sale_status_id', [TripSaleStatus::selling])
            ->whereHas('excursion.ratesLists', function (Builder $query) use ($forRootSite) {
                $query
                    ->whereRaw('DATE(tickets_rates_list.start_at) <= DATE(trips.start_at)')
                    ->whereRaw('DATE(tickets_rates_list.end_at) >= DATE(trips.end_at)')
                    ->whereHas('rates', function (Builder $query) use ($forRootSite) {
                        $query->where('grade_id', '!=', TicketGrade::guide);
                        if ($forRootSite) {
                            $query->where('site_price', '>', 0);
                        } else {
                            $query->where('base_price', '>', 0);
                        }
                    });
            })
            ->when(!$forRootSite, function (Builder $query) {
                $query->whereHas('excursion', function (Builder $query) {
                    $query->where('only_site', false);
                });
            });
    }
}
