<?php

namespace App\Models\PromoCode;

use App\Exceptions\Excursions\WrongExcursionStatusException;
use App\Helpers\PriceConverter;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\Interfaces\AsDictionary;
use App\Models\Dictionaries\PromoCodeStatus;
use App\Models\Dictionaries\PromoCodeType;
use App\Models\Excursions\Excursion;
use App\Models\Excursions\ExcursionAsDictionary;
use App\Models\Model;
use App\Models\Order\Order;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property float $amount
 * @property int $status_id
 * @property string $type
 *
 * @property Collection $excursions
 * @property Collection $orders
 * @property PromoCodeStatus $status
 */
class PromoCode extends Model implements Statusable, AsDictionary
{
    use HasStatus, HasFactory, ExcursionAsDictionary;

    /** @var array Default attributes. */
    protected $attributes = [
        'status_id' => PromoCodeStatus::default,
        'type_id' => PromoCodeType::default,
    ];


    /**
     * Excursions.
     *
     * @return  BelongsToMany
     */
    public function excursions(): BelongsToMany
    {
        return $this->belongsToMany(Excursion::class, 'promo_code_has_excursions', 'promo_code_id', 'excursion_id');
    }

    /**
     * Promo code status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(PromoCodeStatus::class, 'id', 'status_id');
    }

    /**
     * Promo code type.
     *
     * @return  HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(PromoCodeType::class, 'id', 'type_id');
    }

    /**
     * Check and set new status for promo code.
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
        $this->checkAndSetStatus(PromoCodeStatus::class, $status, WrongExcursionStatusException::class, $save);
    }

    /**
     * Convert amount from store value to real price.
     *
     * @param int|null $value
     *
     * @return  float
     */
    public function getAmountAttribute(?int $value): ?float
    {
        return $value !== null ? PriceConverter::storeToPrice($value) : null;
    }

    /**
     * Convert amount to store value.
     *
     * @param float|null $value
     *
     * @return  void
     */
    public function setAmountAttribute(?float $value): void
    {
        $this->attributes['amount'] = $value !== null ? PriceConverter::priceToStore($value) : null;
    }

    /**
     * The orders.
     *
     * @return  BelongsToMany
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'promo_code_has_orders', 'promo_code_id', 'order_id');
    }
}
