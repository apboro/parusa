<?php

namespace App\Models\Account;

use App\Models\Partner\Partner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $partner_id
 * @property int $amount
 * @property int $credit_limit
 *
 * @property Partner $partner
 * @property Collection $transactions
 */
class Account extends Model
{
    /** @var array Default attributes values */
    protected $attributes = [
        'amount' => 0,
        'credit_limit' => 0,
    ];

    /**
     * Transactions related to this account.
     *
     * @return  HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class, 'id', 'account_id');
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
     * Create transaction for this account
     */
    public function attachTransaction(AccountTransaction $transaction): void
    {
        // TODO: Check transaction is new, can be done, store and update amount.
    }

    /**
     * Recalculate amount for this account.
     *
     * @return  int
     */
    public function refreshAmount(): int
    {
        // TODO: Recalculate and store amount.
    }
}
