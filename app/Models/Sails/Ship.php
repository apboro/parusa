<?php

namespace App\Models\Sails;

use App\Exceptions\Sails\WrongShipStatusException;
use App\Exceptions\Sails\WrongShipTypeException;
use App\Interfaces\Statusable;
use App\Interfaces\Typeable;
use App\Models\Dictionaries\Interfaces\AsDictionary;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Dictionaries\ShipType;
use App\Models\Model;
use App\Traits\HasStatus;
use App\Traits\HasType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string $owner
 * @property string $decks
 * @property int $capacity
 * @property int $status_id
 * @property int $type_id
 *
 * @property ShipStatus $status
 * @property ShipType $type
 */
class Ship extends Model implements Statusable, Typeable, AsDictionary
{
    use HasStatus, HasType, HasFactory, ShipAsDictionary;

    /** @var array Default attributes. */
    protected $attributes = [
        'status_id' => ShipStatus::default,
        'type_id' => null,
    ];

    /** @var bool Type can be null */
    protected bool $canHasNullType = true;

    /**
     * Ship status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(ShipStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for ship.
     *
     * @param int|ShipStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongShipStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(ShipStatus::class, $status, WrongShipStatusException::class, $save);
    }

    /**
     * Ship type.
     *
     * @return  HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(ShipType::class, 'id', 'type_id');
    }

    /**
     * Check and set type of ship.
     *
     * @param int|ShipType|null $type
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongShipTypeException
     */
    public function setType($type, bool $save = true): void
    {
        $this->checkAndSetType(ShipType::class, $type, WrongShipTypeException::class, $save);
    }
}
