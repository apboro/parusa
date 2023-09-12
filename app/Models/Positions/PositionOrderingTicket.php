<?php

namespace App\Models\Positions;

use App\Models\Dictionaries\TicketGrade;
use App\Models\Model;
use App\Models\Sails\Trip;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $position_id
 * @property int $terminal_id
 * @property int $trip_id
 * @property int $grade_id
 * @property int $quantity
 * @property int|null $parent_ticket_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Trip $trip
 * @property TicketGrade $grade
 * @property hasOne $backwardTicket
 * @property hasOne $parentTicket
 */
class PositionOrderingTicket extends Model
{
    /** @var int[] Default attributes. */
    protected $attributes = [
        'quantity' => 0,
    ];

    /** @var string[] Fillable attributes. */
    protected $fillable = ['trip_id', 'grade_id', 'terminal_id', 'parent_ticket_id', 'quantity'];

    /**
     * Trip relation.
     *
     * @return  BelongsTo
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Grade of ticket.
     *
     * @return  BelongsTo
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(TicketGrade::class);
    }

    /**
     * Get ticket price.
     *
     * @return  float|null
     */
    public function getPrice(): ?float
    {
        $rateList = $this->trip->getRate();

        return $rateList?->rates()->where('grade_id', $this->grade_id)->value('base_price');
    }
    /**
     * Get ticket partner price.
     *
     * @return  float|null
     */
    public function getPartnerPrice(): ?float
    {
        $rateList = $this->trip->getRate();

        return $rateList?->rates()->where('grade_id', $this->grade_id)->value('partner_price');
    }

    public function getBackwardPrice(): ?float
    {
        $trip = $this->parentTicket->trip;

        $rateList = $trip->getRate();

        $rate = $rateList?->rates()->where('grade_id', $this->grade_id)->first();

        return $rate->backward_price_type === 'fixed' ? $rate->backward_price_value : $rate->base_price * ($rate->backward_price_value/100);
    }

    /**
     * Get ticket min price.
     *
     * @return  float|null
     */
    public function getMinPrice(): ?float
    {
        $rateList = $this->trip->getRate();

        return $rateList ? $rateList->rates()->where('grade_id', $this->grade_id)->value('min_price') : null;
    }

    /**
     * Get ticket max price.
     *
     * @return  float|null
     */
    public function getMaxPrice(): ?float
    {
        $rateList = $this->trip->getRate();

        return $rateList ? $rateList->rates()->where('grade_id', $this->grade_id)->value('max_price') : null;
    }

    public function parentTicket()
    {
        return $this->hasOne(PositionOrderingTicket::class, 'id', 'parent_ticket_id');
    }
    public function backwardTicket()
    {
        return $this->hasOne(PositionOrderingTicket::class, 'parent_ticket_id', 'id');
    }
}
