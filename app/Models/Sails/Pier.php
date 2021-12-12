<?php

namespace App\Models\Sails;

use App\Exceptions\Sails\WrongPierStatusException;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\Interfaces\AsDictionary;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Model;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property int $status_id
 *
 * @property PiersStatus $status
 */
class Pier extends Model implements Statusable, AsDictionary
{
    use HasStatus, HasFactory, PierAsDictionary;

    /** @var array Default attributes. */
    protected $attributes = [
        'status_id' => PiersStatus::default,
    ];

    /**
     * Ship status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(PiersStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for ship.
     *
     * @param int|PiersStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongPierStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(PiersStatus::class, $status, WrongPierStatusException::class, $save);
    }
}
