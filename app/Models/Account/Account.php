<?php

namespace App\Models\Account;

use App\Exceptions\Account\AccountException;
use App\Helpers\PriceConverter;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Partner\Partner;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $partner_id
 * @property int $amount
 * @property int $limit
 *
 * @property Partner $partner
 * @property Collection $transactions
 */
class Account extends Model
{
    /** @var array Default attributes values */
    protected $attributes = [
        'amount' => 0,
        'limit' => 0,
    ];

    /**
     * Transactions related to this account.
     *
     * @return  HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class, 'account_id', 'id');
    }

    /**
     * The partner owning this account.
     *
     * @return  BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id', 'id');
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
     * Convert limit to store value.
     *
     * @param float $value
     *
     * @return  void
     */
    public function setLimitAttribute(float $value): void
    {
        $this->attributes['limit'] = PriceConverter::priceToStore($value);
    }

    /**
     * Convert limit from store value to real currency.
     *
     * @param int $value
     *
     * @return  float
     */
    public function getLimitAttribute(int $value): float
    {
        return PriceConverter::storeToPrice($value);
    }

    /**
     * Create transaction for this account
     *
     * @param AccountTransaction $transaction
     *
     * @return  AccountTransaction
     *
     * @throws AccountException
     */
    public function attachTransaction(AccountTransaction $transaction): AccountTransaction
    {
        if (!$this->exists) {
            throw new AccountException('Лицевой счёт не присоединен к партнёру. Невозможно добавить операцию.');
        }
        if ($transaction->exists) {
            throw new AccountException('Повторное прикрепление операции к лицевому счёту невозможно.');
        }

        /** @var  AccountTransactionType $type */
        $type = AccountTransactionType::get($transaction->type_id);
        if (!$type->final) {
            throw new AccountException('Не возможно создать операцию с таким типом.');
        }

        if ($type->sign === -1 && ($transaction->amount > ($this->amount - $this->limit))) {
            throw new AccountException('Недостаточно средств на счетё для совершения операции.');
        }

        $account = $this;

        DB::transaction(static function () use (&$account, &$transaction, $type) {
            $transaction->account_id = $account->id;
            $transaction->save();
            $account->amount += $type->sign * $transaction->amount;
            $account->save();
        });

        return $transaction;
    }

    /**
     * Recalculate amount for this account.
     *
     * @param Carbon|null $upToDate
     *
     * @return  int
     */
    public function calcAmount(Carbon $upToDate = null): int
    {
        $refill = AccountTransactionType::query()->where('sign', 1)->pluck('id')->toArray();
        $withdrawal = AccountTransactionType::query()->where('sign', -1)->pluck('id')->toArray();

        $total = $this->transactions()
            ->whereIn('type_id', $refill)
            ->when($upToDate, function (Builder $query) use ($upToDate) {
                $query->where('created_at', '<=', $upToDate);
            })
            ->sum('amount');

        $total -= $this->transactions()
            ->whereIn('type_id', $withdrawal)
            ->when($upToDate, function (Builder $query) use ($upToDate) {
                $query->where('created_at', '<=', $upToDate);
            })
            ->sum('amount');

        return PriceConverter::storeToPrice($total);
    }
}
