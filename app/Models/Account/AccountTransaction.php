<?php

namespace App\Models\Account;

use App\Exceptions\Account\WrongAccountTransactionStatusException;
use App\Exceptions\Account\WrongAccountTransactionTypeException;
use App\Models\Dictionaries\AccountTransactionStatus;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Traits\HasStatus;
use App\Models\Traits\HasType;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $account_id
 * @property int $type_id
 * @property int $status_id
 * @property int $amount
 * @property int $committed_by
 * @property string $comments
 *
 * @property AccountTransactionType $type
 * @property AccountTransactionStatus $status
 * @property User $committer
 */
class AccountTransaction extends Model
{
    use HasType, HasStatus;

    /** @var string[] Relations eager loading. */
    protected $with = ['status', 'type'];

    /**
     * Transaction status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(AccountTransactionStatus::class, 'status_id', 'id');
    }

    /**
     * Check and set new status for transaction.
     *
     * @param int $statusId
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongAccountTransactionStatusException
     */
    public function setStatus(int $statusId, bool $save = true): void
    {
        $this->checkAndSetStatus(AccountTransactionStatus::class, $statusId, WrongAccountTransactionStatusException::class, $save);
    }

    /**
     * Transaction type.
     *
     * @return  HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(AccountTransactionType::class, 'type_id', 'id');
    }

    /**
     * Check and set type of transaction.
     *
     * @param int $typeId
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongAccountTransactionTypeException
     */
    public function setType(int $typeId, bool $save = true): void
    {
        $this->checkAndSetType(AccountTransactionType::class, $typeId, WrongAccountTransactionTypeException::class, $save);
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
