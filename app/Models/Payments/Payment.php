<?php

namespace App\Models\Payments;

use App\Exceptions\Payments\WrongPaymentStatusException;
use App\Helpers\PriceConverter;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Model;
use App\Models\Order\Order;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Traits\HasStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $gate
 * @property int $order_id
 * @property int $status_id
 * @property ?int $parent_id
 * @property string|null $fiscal
 * @property int $total in minimal money units
 * @property int $by_card in minimal money units
 * @property int $by_cash in minimal money units
 * @property string $external_id
 * @property int|null $terminal_id
 * @property int|null $position_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property PaymentStatus $status
 * @property Order|null $order
 * @property Terminal|null $terminal
 * @property Position|null $position
 */
class Payment extends Model implements Statusable
{
    use HasStatus;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Payment status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(PaymentStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for payment.
     *
     * @param int|PaymentStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongPaymentStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(PaymentStatus::class, $status, WrongPaymentStatusException::class, $save);
    }

    /**
     * Order this payment assigned to.
     *
     * @return  HasOne
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    /**
     * Terminal this payment assigned to.
     *
     * @return  HasOne
     */
    public function terminal(): HasOne
    {
        return $this->hasOne(Terminal::class, 'id', 'terminal_id');
    }

    /**
     * Position this payment initiated by.
     *
     * @return  HasOne
     */
    public function position(): HasOne
    {
        return $this->hasOne(Position::class, 'id', 'position_id');
    }

    /**
     * Convert total from store value to real price.
     *
     * @param int|null $value
     *
     * @return  float
     */
    public function getTotalAttribute(?int $value): ?float
    {
        return $value !== null ? PriceConverter::storeToPrice($value) : null;
    }

    /**
     * Convert total to store value.
     *
     * @param float|null $value
     *
     * @return  void
     */
    public function setTotalAttribute(?float $value): void
    {
        $this->attributes['total'] = $value !== null ? PriceConverter::priceToStore($value) : null;
    }

    /**
     * Convert by_card from store value to real price.
     *
     * @param int|null $value
     *
     * @return  float
     */
    public function getByCardAttribute(?int $value): ?float
    {
        return $value !== null ? PriceConverter::storeToPrice($value) : null;
    }

    /**
     * Convert by_card to store value.
     *
     * @param float|null $value
     *
     * @return  void
     */
    public function setByCardAttribute(?float $value): void
    {
        $this->attributes['by_card'] = $value !== null ? PriceConverter::priceToStore($value) : null;
    }

    /**
     * Convert by_cash from store value to real price.
     *
     * @param int|null $value
     *
     * @return  float
     */
    public function getByCashAttribute(?int $value): ?float
    {
        return $value !== null ? PriceConverter::storeToPrice($value) : null;
    }

    /**
     * Convert by_cash to store value.
     *
     * @param float|null $value
     *
     * @return  void
     */
    public function setByCashAttribute(?float $value): void
    {
        $this->attributes['by_cash'] = $value !== null ? PriceConverter::priceToStore($value) : null;
    }
}
