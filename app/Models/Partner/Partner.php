<?php

namespace App\Models\Partner;

use App\Exceptions\Partner\WrongPartnerStatusException;
use App\Exceptions\Partner\WrongPartnerTypeException;
use App\Interfaces\Statusable;
use App\Interfaces\Typeable;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PartnerType;
use App\Traits\HasStatus;
use App\Traits\HasType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 */
class Partner extends Model implements Statusable, Typeable
{
    use HasApiTokens, HasStatus, HasType, HasFactory;

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
        return $this->hasOne(PartnerStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for partner.
     *
     * @param int|PartnerStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongPartnerStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(PartnerStatus::class, $status, WrongPartnerStatusException::class, $save);
    }

    /**
     * Partner`s type.
     *
     * @return  HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(PartnerType::class, 'id', 'type_id');
    }

    /**
     * Check and set type of partner.
     *
     * @param int|PartnerType|null $type
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongPartnerTypeException
     */
    public function setType($type, bool $save = true): void
    {
        $this->checkAndSetType(PartnerType::class, $type, WrongPartnerTypeException::class, $save);
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
