<?php

namespace App\Models\Positions;

use App\Exceptions\Partner\WrongPositionStatusException;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\Role;
use App\Models\Model;
use App\Models\User\User;
use App\Traits\HasStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $status_id
 * @property int $user_id
 * @property int $partner_id
 * @property string $title
 * @property bool $is_staff
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property PositionStatus $status
 * @property User $user
 * @property PositionInfo $info
 * @property StaffPositionInfo $staffInfo
 */
class Position extends Model implements Statusable
{
    use HasStatus, HasFactory;

    /** @var array Default attributes. */
    protected $attributes = [
        'status_id' => PositionStatus::default,
    ];

    /**
     * @var string[] Fillable attributes.
     */
    protected $fillable = [
        'position_title',
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
     * Position roles.
     *
     * @return  BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'position_has_role', 'position_id', 'role_id');
    }

    /**
     * Check if position has role.
     *
     * @param int $roleId
     * @param bool $fresh
     *
     * @return  bool
     */
    public function hasRole(int $roleId, bool $fresh = false): bool
    {
        if ($fresh && $this->relationLoaded('roles')) {
            $this->unsetRelation('roles');
        }

        $this->loadMissing('roles');

        foreach ($this->getRelation('roles') as $usersRole) {
            /** @var Role $usersRole */
            if ($usersRole->matches($roleId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Position related user.
     *
     * @return  HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Position info.
     *
     * @return  HasOne
     */
    public function info(): HasOne
    {
        return $this->hasOne(PositionInfo::class, 'position_id', 'id')->withDefault();
    }

    /**
     * Position info.
     *
     * @return  HasOne
     */
    public function staffInfo(): HasOne
    {
        return$this->hasOne(StaffPositionInfo::class, 'position_id', 'id')->withDefault();
    }
}
