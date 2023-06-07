<?php

namespace App\Models\PromoCode;

use App\Exceptions\Excursions\WrongExcursionStatusException;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\Interfaces\AsDictionary;
use App\Models\Dictionaries\PromoCodeStatus;
use App\Models\Dictionaries\Role;
use App\Models\Excursions\ExcursionAsDictionary;
use App\Models\Model;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $amount
 * @property int $status_id
 * @property string $type
 *
 * @property Collection $excursions
 * @property PromoCodeStatus $status
 */
class PromoCode extends Model implements Statusable, AsDictionary
{
    use HasStatus, HasFactory, ExcursionAsDictionary;

    public const type_fixed = 'fixed';
    public const type_percents = 'percents';

    /**
     * Excursions.
     *
     * @return  BelongsToMany
     */
    public function excursions(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'promo_code_has_excursions', 'promo_code_id', 'excursion_id');
    }

    /**
     * Promo code status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(PromoCodeStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for promo code.
     *
     * @param int|ExcursionStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongExcursionStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(PromoCodeStatus::class, $status, WrongExcursionStatusException::class, $save);
    }
}
