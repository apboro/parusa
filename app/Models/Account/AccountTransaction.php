<?php

namespace App\Models\Account;

use App\Exceptions\Account\WrongAccountTransactionStatusException;
use App\Exceptions\Account\WrongAccountTransactionTypeException;
use App\Helpers\PriceConverter;
use App\Interfaces\Statusable;
use App\Interfaces\Typeable;
use App\Models\Dictionaries\AccountTransactionStatus;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Positions\Position;
use App\Traits\HasStatus;
use App\Traits\HasType;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $account_id
 * @property int $type_id
 * @property int $status_id
 * @property Carbon $timestamp
 * @property int $amount
 * @property string $reason
 * @property Carbon $reason_date
 * @property int $committer_id
 * @property string $comments
 * @property int $order_id
 * @property int $ticket_id
 * @property string $commission_type
 * @property int $commission_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Account $account
 * @property AccountTransactionType $type
 * @property AccountTransactionStatus $status
 * @property Position|null $committer
 *
 * @property-read string|null $reasonTitle
 * @property-read array|null $reasonRaw
 */
class AccountTransaction extends Model implements Statusable, Typeable
{
    use HasType, HasStatus;

    /** @var string[] Relations eager loading. */
    protected $with = ['status', 'type'];

    /** @var array Default attributes. */
    protected $attributes = [
        'status_id' => AccountTransactionStatus::default,
    ];

    /** @var array Attribute casting */
    protected $casts = [
        'timestamp' => 'datetime',
        'reason_date' => 'date',
    ];

    /** @var array Fillable attributes. */
    protected $fillable = [
        'type_id',
        'status_id',
        'timestamp',
        'amount',
        'reason',
        'reason_date',
        'committer_id',
        'comments',
        'order_id',
        'ticket_id',
        'commission_type',
        'commission_value',
    ];

    /** @var array Append attributes */
    protected $appends = [
        'reasonTitle',
    ];

    /**
     * Transaction status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(AccountTransactionStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for transaction.
     *
     * @param int|AccountTransactionStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongAccountTransactionStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(AccountTransactionStatus::class, $status, WrongAccountTransactionStatusException::class, $save);
    }

    /**
     * Transaction type.
     *
     * @return  HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(AccountTransactionType::class, 'id', 'type_id');
    }

    /**
     * Check and set type of transaction.
     *
     * @param int|AccountTransactionType $type
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongAccountTransactionTypeException
     */
    public function setType($type, bool $save = true): void
    {
        $this->checkAndSetType(AccountTransactionType::class, $type, WrongAccountTransactionTypeException::class, $save);
    }

    /**
     * Account this transaction belongs to.
     *
     * @return  BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    /**
     * Transaction status.
     *
     * @return  BelongsTo
     */
    public function committer(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'committer_id', 'id');
    }

    /**
     * Convert amount to store value.
     *
     * @param float $value
     *
     * @return  void
     */
    public function setAmountAttribute(float $value): void
    {
        $this->attributes['amount'] = PriceConverter::priceToStore($value);
    }

    /**
     * Convert amount from store value to real currency.
     *
     * @param int $value
     *
     * @return  float
     */
    public function getAmountAttribute(int $value): float
    {
        return PriceConverter::storeToPrice($value);
    }

    /**
     * Get reason full title.
     *
     * @return  string|null
     */
    public function getReasonTitleAttribute(): ?string
    {
        switch ($this->type_id) {
            case AccountTransactionType::account_refill_invoice:
                return "Пополнение лицевого счёта банковским переводом по счёту №$this->reason от " . $this->reason_date->format('d.m.Y');
            case AccountTransactionType::account_refill_cash:
                return 'Пополнение лицевого счёта наличными';
            case AccountTransactionType::tickets_buy:
                return 'Оплата заказа №' . $this->order_id;
            case AccountTransactionType::tickets_buy_return:
                return 'Возврат билета №' . $this->ticket_id;
            case AccountTransactionType::tickets_sell_commission:
                return 'Начисление комисионных за продажу билета №' . $this->ticket_id;
            case AccountTransactionType::tickets_sell_commission_return:
                return 'Возврат комисионных за продажу билета №' . $this->ticket_id;
        }

        return null;
    }

    /**
     * Get reason full title.
     *
     * @return  array|null
     */
    public function getReasonRawAttribute(): ?array
    {
        switch ($this->type_id) {
            case AccountTransactionType::account_refill_invoice:
                return [
                    'title' => "Пополнение лицевого счёта банковским переводом по счёту №$this->reason от " . $this->reason_date->format('d.m.Y'),
                    'caption' => null,
                    'object' => null,
                    'object_id' => null,
                ];
            case AccountTransactionType::account_refill_cash:
                return [
                    'title' => 'Пополнение лицевого счёта наличными',
                    'caption' => null,
                    'object' => null,
                    'object_id' => null,
                ];
            case AccountTransactionType::tickets_buy:
                return [
                    'title' => 'Оплата',
                    'caption' => 'заказа №' . $this->order_id,
                    'object' => 'order',
                    'object_id' => $this->order_id,
                ];
            case AccountTransactionType::tickets_buy_return:
                return [
                    'title' => 'Возврат',
                    'caption' => 'билета №' . $this->ticket_id,
                    'object' => 'ticket',
                    'object_id' => $this->ticket_id,
                ];
            case AccountTransactionType::tickets_sell_commission:
                return [
                    'title' => 'Начисление комисионных за продажу',
                    'caption' => 'билета №' . $this->ticket_id,
                    'object' => 'ticket',
                    'object_id' => $this->ticket_id,
                ];
            case AccountTransactionType::tickets_sell_commission_return:
                return [
                    'title' => 'Возврат комисионных за продажу',
                    'caption' => 'билета №' . $this->ticket_id,
                    'object' => 'ticket',
                    'object_id' => $this->ticket_id,
                ];
        }

        return null;
    }
}
