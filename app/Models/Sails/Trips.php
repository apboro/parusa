<?php

namespace App\Models\Sails;

use App\Exceptions\Sails\WrongTripDiscountStatusException;
use App\Exceptions\Sails\WrongTripSaleStatusException;
use App\Exceptions\Sails\WrongTripStatusException;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\TripDiscountStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Model;
use App\Traits\HasStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property Carbon $start_at
 * @property Carbon $end_at
 * @property int $start_pier_id
 * @property int $end_pier_id
 * @property int $ship_id
 * @property int $excursion_id
 * @property int $status_id
 * @property int $sale_status_id
 * @property positive-int $tickets_count
 * @property int $discount_status_id
 * @property int $cancellation_time
 *
 * @property Pier $startPier
 * @property Pier $endPier
 * @property Ship $ship
 * @property Excursion $excursion
 * @property TripStatus $status
 * @property TripSaleStatus $saleStatus
 * @property TripDiscountStatus $discountStatus
 */
class Trips extends Model implements Statusable
{
    use HasStatus, HasFactory;

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
        return $this->hasOne(Pier::class, 'id', 'start_pier');
    }

    /**
     * Pier this trip ends.
     *
     * @return  HasOne
     */
    public function endPier(): HasOne
    {
        return $this->hasOne(Pier::class, 'id', 'end_pier');
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
}
