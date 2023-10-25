<?php

namespace App\Models\Partner;

use App\Exceptions\Partner\WrongPartnerStatusException;
use App\Exceptions\Partner\WrongPartnerTypeException;
use App\Interfaces\Statusable;
use App\Interfaces\Typeable;
use App\Models\Account\Account;
use App\Models\Common\File;
use App\Models\Dictionaries\Interfaces\AsDictionary;
use App\Models\Dictionaries\Inventory;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PartnerType;
use App\Models\Excursions\Excursion;
use App\Models\Model;
use App\Models\Positions\Position;
use App\Models\QrCode;
use App\Models\Tickets\TicketPartnerRate;
use App\Models\WorkShift\WorkShift;
use App\Traits\HasStatus;
use App\Traits\HasType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property int $status_id
 * @property int $type_id
 * @property Carbon $created_at
 *
 * @property PartnerStatus $status
 * @property PartnerType $type
 * @property PartnerProfile $profile
 * @property Account $account
 * @property Collection $positions
 * @property Collection $files
 * @property Collection $excursionsShowcaseHide
 */
class Partner extends Model implements Statusable, Typeable, AsDictionary
{
    use HasApiTokens, HasStatus, HasType, HasFactory, PartnerAsDictionary;

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
     * Partners account.
     *
     * @return  HasOne
     */
    public function account(): HasOne
    {
        return $this->hasOne(Account::class, 'partner_id', 'id')->withDefault();
    }

    /**
     * Partner`s profile.
     *
     * @return  HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(PartnerProfile::class, 'partner_id', 'id')->withDefault();
    }

    /**
     * A loaded partner files.
     *
     * @return  BelongsToMany
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'partner_has_file', 'partner_id', 'file_id');
    }

    /**
     * All positions of this partner.
     *
     * @return  HasMany
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'partner_id', 'id')->where('is_staff', false);
    }

    /**
     * Excursions this partner showing disabled in a showcase.
     *
     * @return  BelongsToMany
     */
    public function excursionsShowcaseHide(): BelongsToMany
    {
        return $this->belongsToMany(Excursion::class, 'partner_excursion_showcase_disabling', 'partner_id', 'excursion_id');
    }

    public function qrCodes():hasMany
    {
        return $this->hasMany(QrCode::class, 'partner_id', 'id');

    }

    public function createMassRates()
    {
        $partnerMassRates = TicketPartnerRate::where('mass_assignment', 1)->groupBy('rate_id')->get();
        foreach ($partnerMassRates as $rate){
            $newPartnerRate = new TicketPartnerRate();
            $newPartnerRate->partner_id = $this->id;
            $newPartnerRate->rate_id = $rate->rate_id;
            $newPartnerRate->commission_type = $rate->commission_type;
            $newPartnerRate->commission_value = $rate->commission_value ?? 0;
            $newPartnerRate->mass_assignment = 1;
            $newPartnerRate->save();
        }
    }

    public function workShifts()
    {
        return $this->hasMany(WorkShift::class);
    }

    public function getOpenedShift()
    {
        return $this->workShifts()->with('tariff')->whereNull('end_at')->first();
    }

    public function inventory()
    {
        return $this->hasMany(PromoterInventory::class, 'promoter_id', 'id')->with('dictionary');
    }

    public function getLastShift()
    {
        return $this->workShifts()->whereNotNull('end_at')->orderBy('created_at', 'desc')->first();
    }

}
