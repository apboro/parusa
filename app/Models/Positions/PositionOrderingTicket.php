<?php

namespace App\Models\Positions;

use App\Models\Dictionaries\TicketGrade;
use App\Models\Model;
use App\Models\Sails\Trip;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $position_id
 * @property int $terminal_id
 * @property int $trip_id
 * @property int $grade_id
 * @property int $quantity
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Trip $trip
 * @property TicketGrade $grade
 */
class PositionOrderingTicket extends Model
{
    /** @var int[] Default attributes. */
    protected $attributes = [
        'quantity' => 0,
    ];

    /** @var string[] Fillable attributes. */
    protected $fillable = ['trip_id', 'grade_id', 'terminal_id'];

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

        return $rateList ? $rateList->rates()->where('grade_id', $this->grade_id)->value('base_price') : null;
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
}
