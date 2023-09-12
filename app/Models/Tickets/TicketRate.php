<?php

namespace App\Models\Tickets;

use App\Helpers\PriceConverter;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $rate_id
 * @property int $grade_id
 * @property int $base_price
 * @property int $min_price
 * @property int $max_price
 * @property int|null $site_price
 * @property string $commission_type
 * @property int $commission_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property TicketGrade $grade
 * @property Collection $partnerRates
 * @property string $backward_price_type
 * @property int $backward_price_value
 * @property int $partner_price
 */
class TicketRate extends Model
{
    /** @var string Referenced table name. */
    protected $table = 'ticket_rates';

    /** @var string[] Fillable attributes. */
    protected $fillable = [
        'rate_id',
        'grade_id',
        'base_price',
        'min_price',
        'max_price',
        'site_price',
        'partner_price',
        'commission_type',
        'commission_value',
        'backward_price_type',
        'backward_price_value'
    ];

    /**
     * Ticket rate grade.
     *
     * @return  HasOne
     */
    public function grade(): HasOne
    {
        return $this->hasOne(TicketGrade::class, 'id', 'grade_id');
    }

    /**
     * Rate overrides for partners.
     *
     * @return  HasMany
     */
    public function partnerRates(): HasMany
    {
        return $this->hasMany(TicketPartnerRate::class, 'rate_id', 'id');
    }

    /**
     * Convert base_price from store value to real price.
     *
     * @param int $value
     *
     * @return  float
     */
    public function getBasePriceAttribute(int $value): float
    {
        return PriceConverter::storeToPrice($value);
    }

    /**
     * Convert base_price to store value.
     *
     * @param float $value
     *
     * @return  void
     */
    public function setBasePriceAttribute(float $value): void
    {
        $this->attributes['base_price'] = PriceConverter::priceToStore($value);
    }

    /**
     * Convert min_price from store value to real price.
     *
     * @param int $value
     *
     * @return  float
     */
    public function getMinPriceAttribute(int $value): float
    {
        return PriceConverter::storeToPrice($value);
    }

    /**
     * Convert min_price to store value.
     *
     * @param float $value
     *
     * @return  void
     */
    public function setMinPriceAttribute(float $value): void
    {
        $this->attributes['min_price'] = PriceConverter::priceToStore($value);
    }

    /**
     * Convert max_price from store value to real price.
     *
     * @param int $value
     *
     * @return  float
     */
    public function getMaxPriceAttribute(int $value): float
    {
        return PriceConverter::storeToPrice($value);
    }

    /**
     * Convert max_price to store value.
     *
     * @param float $value
     *
     * @return  void
     */
    public function setMaxPriceAttribute(float $value): void
    {
        $this->attributes['max_price'] = PriceConverter::priceToStore($value);
    }
    public function setBackwardPriceValueAttribute(float $value): void
    {
        $this->attributes['backward_price_value'] = PriceConverter::priceToStore($value);
    }

    /**
     * Convert site_price from store value to real price.
     *
     * @param int|null $value
     *
     * @return  float
     */
    public function getSitePriceAttribute(?int $value): ?float
    {
        return $value === null ? null : PriceConverter::storeToPrice($value);
    }

    /**
     * Convert site_price to store value.
     *
     * @param float|null $value
     *
     * @return  void
     */
    public function setSitePriceAttribute(?float $value): void
    {
        $this->attributes['site_price'] = $value === null ? null : PriceConverter::priceToStore($value);
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
    public function getBackwardPriceValueAttribute(int $value): float
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
