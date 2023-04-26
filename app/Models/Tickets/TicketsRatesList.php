<?php

namespace App\Models\Tickets;

use App\Models\Dictionaries\TicketGrade;
use App\Models\Excursions\Excursion;
use App\Models\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $excursion_id
 * @property Carbon $start_at
 * @property Carbon $end_at
 * @property string $caption
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Collection $rates
 * @property Excursion $excursion
 */
class TicketsRatesList extends Model
{
    protected $guarded = [];
    /** @var string Referenced table name. */
    protected $table = 'tickets_rates_list';

    /** @var array Attributes casting */
    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
    ];

    /**
     * All grades for this rate.
     *
     * @return  HasMany
     */
    public function rates(): HasMany
    {
        return $this->hasMany(TicketRate::class, 'rate_id', 'id')->with('grade');
    }

    /**
     * All grades for this rate.
     *
     * @return  BelongsTo
     */
    public function excursion(): BelongsTo
    {
        return $this->belongsTo(Excursion::class);
    }

    public function getShowcasePrice(?int $partnerId): int
    {
        /**@var TicketRate $adultPrice*/
        $adultPrice = $this->rates()->whereIn('grade_id', TicketGrade::showcaseDisplayPrice)->first();
        if(!$adultPrice) {
            $adultPrice = $this->rates->max(function (TicketRate $rate) {
                return $rate->base_price;
            });
        }
        return $partnerId === null ? $adultPrice->site_price : $adultPrice->base_price;
    }
}
