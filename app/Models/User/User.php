<?php

namespace App\Models\User;

use App\Exceptions\User\WrongUserStatusException;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\AbstractDictionary;
use App\Models\Dictionaries\UserRole;
use App\Models\Dictionaries\UserStatus;
use App\Models\Partner\PartnerUserPosition;
use App\Models\Staff\StaffUserPosition;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property int $status_id
 * @property bool $is_staff
 *
 * @property UserStatus $status
 * @property UserProfile $profile
 * @property Collection $roles
 * @property Collection $contacts
 * @property Collection $positions
 * @property StaffUserPosition $staffPosition
 */
class User extends Authenticatable implements Statusable
{
    use HasApiTokens, HasFactory, HasStatus;

    /** @var string Referenced table. */
    protected $table = 'users';

    /** @var string[] The attributes that are mass assignable. */
    protected $fillable = [
        'login',
        'password',
    ];

    /** @var array The attributes that should be hidden for serialization. */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @var array Default attributes. */
    protected $attributes = [
        'status_id' => UserStatus::default,
    ];

    /** @var string[] Relations eager loading. */
    // no need yet
    // protected $with = ['roles'];

    /**
     * User's status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(UserStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for user.
     *
     * @param int|AbstractDictionary $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongUserStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(UserStatus::class, $status, WrongUserStatusException::class, $save);
    }

    /**
     * User's roles.
     *
     * @return  BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(UserRole::class, 'user_has_role', 'user_id', 'user_role_id');
    }

    /**
     * Check if user has role.
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
            /** @var UserRole $usersRole */
            if ($usersRole->matches($roleId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * User's profile.
     *
     * @return  HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id')->withDefault();
    }

    /**
     * User related contacts.
     *
     * @return  HasMany
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(UserContact::class);
    }

    /**
     * All partner positions of user.
     *
     * @return  HasMany
     */
    public function positions(): HasMany
    {
        return $this->hasMany(PartnerUserPosition::class, 'user_id', 'id');
    }

    /**
     * All partner positions of user.
     *
     * @return  HasOne
     */
    public function staffPosition(): HasOne
    {
        return $this->hasOne(StaffUserPosition::class, 'user_id', 'id');
    }
}
