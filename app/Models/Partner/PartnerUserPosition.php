<?php

namespace App\Models\Partner;

use App\Exceptions\Partner\WrongPositionStatusException;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Model;
use App\Models\Traits\HasStatus;
use App\Models\User\User;
use App\Models\User\UserContact;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PartnerUserPosition extends Model
{
    use HasStatus;

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
        return $this->hasOne(PositionStatus::class);
    }

    /**
     * Check and set new status for position.
     *
     * @param int $statusId
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongPositionStatusException
     */
    public function setStatus(int $statusId, bool $save = true): void
    {
        $this->checkAndSetStatus(PositionStatus::class, $statusId, WrongPositionStatusException::class, $save);
    }

    /**
     * Position's partner.
     *
     * @return  HasOne
     */
    public function partner(): HasOne
    {
        return $this->hasOne(Partner::class);
    }

    /**
     * Position's user.
     *
     * @return  HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
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
