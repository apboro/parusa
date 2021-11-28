<?php

namespace App\Models\Account;

use App\Exceptions\Account\WrongAccountTransactionStatusException;
use App\Exceptions\Account\WrongAccountTransactionTypeException;
use App\Interfaces\Statusable;
use App\Interfaces\Typeable;
use App\Models\Dictionaries\AccountTransactionStatus;
use App\Models\Dictionaries\AccountTransactionType;
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
 * @property int $amount
 * @property string $reason
 * @property Carbon $reason_date
 * @property int $committer_id
 * @property string $comments
 *
 * @property Account $account
 * @property AccountTransactionType $type
 * @property AccountTransactionStatus $status
 * @property User $committer
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
    public function account():BelongsTo
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
        return $this->belongsTo(User::class, 'committer_id', 'id');
    }
}
