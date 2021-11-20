<?php

namespace App\Models\User;

use App\Models\Partner\Partner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'login',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * User's roles.
     *
     * @return  BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(UserRole::class, 'user_has_role');
    }

    /**
     * Check if user has role.
     *
     * @param int $roleId
     *
     * @return  bool
     */
    public function in(int $roleId): bool
    {
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
     * @return  \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * All active agents of this partner.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'user_belongs_to_partner', 'user_id', 'partner_id')
            ->withPivot(['position', 'blocked_at'])
            ->wherePivotNull('blocked_at', true);
    }

    /**
     * All agents of this partner.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function allPartners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'user_belongs_to_partner', 'user_id', 'partner_id')
            ->withPivot(['position', 'blocked_at']);
    }

    /**
     * User related contacts.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(UserContact::class);
    }
}
