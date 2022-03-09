<?php

namespace App\Models\Tickets;

use App\Exceptions\Account\AccountException;
use App\Exceptions\Tickets\WrongTicketStatusException;
use App\Helpers\PriceConverter;
use App\Interfaces\Statusable;
use App\Models\Account\AccountTransaction;
use App\Models\Dictionaries\AccountTransactionStatus;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Model;
use App\Models\Sails\Trip;
use App\Traits\HasStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $status_id
 * @property int $order_id
 * @property int $trip_id
 * @property int $grade_id
 * @property float $base_price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property TicketStatus $status
 *
 * @property Order $order
 * @property Trip $trip
 * @property TicketGrade $grade
 * @property AccountTransaction $transaction
 */
class Ticket extends Model implements Statusable
{
    use HasStatus;

    /** @var string[] Fillable attributes. */
    protected $fillable = ['trip_id', 'grade_id', 'status_id', 'base_price'];

    /**
     * User's status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(TicketStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for user.
     *
     * @param int|TicketStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongTicketStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(TicketStatus::class, $status, WrongTicketStatusException::class, $save);
    }

    /**
     * Order relation.
     *
     * @return  BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

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
     * Relater transaction.
     *
     * @return  HasOne
     */
    public function transaction(): HasOne
    {
        return $this->hasOne(AccountTransaction::class, 'ticket_id', 'id');
    }

    /**
     * Convert base_price from store value to real price.
     *
     * @param int|null $value
     *
     * @return  float
     */
    public function getBasePriceAttribute(?int $value): ?float
    {
        return $value ? PriceConverter::storeToPrice($value) : null;
    }

    /**
     * Convert base_price to store value.
     *
     * @param float|null $value
     *
     * @return  void
     */
    public function setBasePriceAttribute(?float $value): void
    {
        $this->attributes['base_price'] = $value ? PriceConverter::priceToStore($value) : null;
    }

    /**
     * Get ticket current price.
     *
     * @return  float|null
     */
    public function getCurrentPrice(): ?float
    {
        $rateList = $this->trip->getRate();

        return $rateList ? $rateList->rates()->where('grade_id', $this->grade_id)->value('base_price') : null;
    }

    /**
     * Pay commission for this ticket.
     *
     * @return  void
     *
     * @throws AccountException
     */
    public function payCommission(): void
    {
        $partner = $this->order->partner;
        $rateList = $this->trip->getRate();

        /** @var TicketRate $rate */
        if ($rateList === null || null === ($rate = $rateList->rates()->where('grade_id', $this->grade_id)->first())) {
            return;
        }

        $rate = $rate->partnerRates()->where('partner_id', $partner->id)->first() ?? $rate;

        $partner->account->attachTransaction(new AccountTransaction([
            'type_id' => AccountTransactionType::tickets_sell_commission,
            'status_id' => AccountTransactionStatus::accepted,
            'timestamp' => Carbon::now(),
            'amount' => $rate->commission_value * ($rate->commission_type === 'fixed' ? 1 : $this->base_price / 100),
            'ticket_id' => $this->id,
            'commission_type' => $rate->commission_type,
            'commission_value' => $rate->commission_value,
        ]));
    }
}
