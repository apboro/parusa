<?php

namespace App\Models\Partner;

use App\Exceptions\Partner\WrongPartnerStatusException;
use App\Exceptions\Partner\WrongPartnerTypeException;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PartnerType;
use App\Models\Traits\HasStatus;
use App\Models\Traits\HasType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Partner extends Model
{
    use HasStatus, HasType;

    /** @var array Default attributes. */
    protected $attributes = [
        'status_id' => PartnerStatus::default,
    ];

    /**
     * Partner's status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(PartnerStatus::class);
    }

    /**
     * Check and set new status for partner.
     *
     * @param int $statusId
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongPartnerStatusException
     */
    public function setStatus(int $statusId, bool $save = true): void
    {
        $this->checkAndSetStatus(PartnerStatus::class, $statusId, WrongPartnerStatusException::class, $save);
    }

    /**
     * Partner`s type.
     *
     * @return  HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(PartnerType::class);
    }

    /**
     * Check and set type of partner.
     *
     * @param int $typeId
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongPartnerTypeException
     */
    public function setType(int $typeId, bool $save = true): void
    {
        $this->checkAndSetType(PartnerType::class, $typeId, WrongPartnerTypeException::class, $save);
    }

    /**
     * Partner`s profile.
     *
     * @return  HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(PartnerProfile::class);
    }

    /**
     * Loaded partner's documents.
     *
     * @return  HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(PartnerDocuments::class);
    }

    /**
     * All active agents of this partner (users that belongs to this partner).
     *
     * @return  BelongsToMany
     */
//    public function positions(): BelongsToMany
//    {
//        return $this->belongsToMany(User::class, 'user_belongs_to_partner', 'partner_id', 'user_id')
//            ->withPivot(['position', 'blocked_at'])
//            ->wherePivotNull('blocked_at', true);
//    }

    /**
     * All agents of this partner.
     *
     * @return  BelongsToMany
     */
//    public function allPositions(): BelongsToMany
//    {
//        return $this->belongsToMany(User::class, 'user_belongs_to_partner', 'partner_id', 'user_id')
//            ->withPivot(['position', 'blocked_at']);
//    }
}
