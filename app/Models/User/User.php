<?php

namespace App\Models\User;

use App\Exceptions\User\WrongUserStatusException;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\AbstractDictionary;
use App\Models\Dictionaries\UserRole;
use App\Models\Dictionaries\UserStatus;
use App\Models\Partner\Partner;
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
    protected $with = ['roles'];

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
        return $this->hasOne(UserProfile::class);
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
     * All active agents of this partner.
     *
     * @return  BelongsToMany
     */
//    public function partners(): BelongsToMany
//    {
//        return $this->belongsToMany(Partner::class, 'user_belongs_to_partner', 'user_id', 'partner_id')
//            ->withPivot(['position', 'blocked_at'])
//            ->wherePivotNull('blocked_at', true);
//    }

    /**
     * All agents of this partner.
     *
     * @return  BelongsToMany
     */
//    public function allPartners(): BelongsToMany
//    {
//        return $this->belongsToMany(Partner::class, 'user_belongs_to_partner', 'user_id', 'partner_id')
//            ->withPivot(['position', 'blocked_at']);
//    }

}
