<?php

namespace App\Models\Partner;

use App\Models\Dictionaries\PositionStatus;
use App\Models\Model;
use App\Models\User\User;
use App\Models\User\UserContact;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PartnerUserPosition extends Model
{
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
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(UserContact::class, 'partner_position_has_contacts');
    }
}
