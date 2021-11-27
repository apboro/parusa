<?php

namespace App\Models\Partner;

use App\Exceptions\Partner\WrongPositionStatusException;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Model;
use App\Traits\HasStatus;
use App\Models\User\User;
use App\Models\User\UserContact;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 *
 * @property User $user
 * @property Partner $partner
 */
class PartnerUserPosition extends Model implements Statusable
{
    use HasStatus, HasFactory;

    /** @var array Default attributes. */
    protected $attributes = [
        'status_id' => PositionStatus::default,
    ];

    /**
     * Position's status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(PositionStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for position.
     *
     * @param int|PositionStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongPositionStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(PositionStatus::class, $status, WrongPositionStatusException::class, $save);
    }

    /**
     * Position's partner.
     *
     * @return  HasOne
     */
    public function partner(): HasOne
    {
        return $this->hasOne(Partner::class, 'id', 'partner_id');
    }

    /**
     * Position's user.
     *
     * @return  HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * User related contacts.
     *
     * @return  BelongsToMany
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(UserContact::class, 'partner_position_has_contacts');
    }
}
