<?php

namespace App\Models\Staff;

use App\Exceptions\Partner\WrongPositionStatusException;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Model;
use App\Traits\HasStatus;
use App\Models\User\User;
use App\Models\User\UserContact;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $user_id
 * @property string $position_title
 * @property int $status_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property PositionStatus $status
 * @property User $user
 */
class StaffUserPosition extends Model implements Statusable
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
