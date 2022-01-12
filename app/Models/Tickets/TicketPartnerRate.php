<?php

namespace App\Models\Tickets;

use App\Helpers\PriceConverter;
use App\Models\Model;
use App\Models\Partner\Partner;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $rate_id
 * @property int $partner_id
 * @property string $commission_type
 * @property int $commission_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TicketPartnerRate extends Model
{
    /** @var string Referenced table name. */
    protected $table = 'ticket_partner_rates';

    /** @var string[] Fillable attributes. */
    protected $fillable = [
        'partner_id',
        'commission_type',
        'commission_value',
    ];

    /**
     * Rate related partner.
     *
     * @return  HasOne
     */
    public function partner(): HasOne
    {
        return $this->hasOne(Partner::class, 'id', 'partner_id');
    }

    /**
     * Convert commission_value from store value to real price.
     *
     * @param int $value
     *
     * @return  float
     */
    public function getCommissionValueAttribute(int $value): float
    {
        return PriceConverter::storeToPrice($value);
    }

    /**
     * Convert commission_value to store value.
     *
     * @param float $value
     *
     * @return  void
     */
    public function setCommissionValueAttribute(float $value): void
    {
        $this->attributes['commission_value'] = PriceConverter::priceToStore($value);
    }
}
