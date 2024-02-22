<?php

namespace App\Models\Ships;

use App\Exceptions\Sails\WrongShipStatusException;
use App\Exceptions\Sails\WrongShipTypeException;
use App\Interfaces\Statusable;
use App\Interfaces\Typeable;
use App\Models\Dictionaries\Interfaces\AsDictionary;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Dictionaries\ShipType;
use App\Models\Model;
use App\Models\Partner\Partner;
use App\Models\Sails\Trip;
use App\Models\Ships\Seats\Seat;
use App\Models\Ships\Seats\ShipSeatCategoryTicketGrade;
use App\Traits\HasStatus;
use App\Traits\HasType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string $owner
 * @property int $capacity
 * @property string $description
 * @property int $status_id
 * @property int $type_id
 * @property string|null $scheme_name
 *
 * @property ShipStatus $status
 * @property ShipType $type
 */
class Ship extends Model implements Statusable, Typeable, AsDictionary
{
    use HasStatus, HasType, HasFactory, ShipAsDictionary;

    protected $guarded = [];
    /** @var array Default attributes. */
    protected $attributes = [
        'status_id' => ShipStatus::default,
        'type_id' => null,
        'enabled' => true,
    ];

    /** @var array Attributes casting. */
    protected $casts = [
        'enabled' => 'bool',
        'order' => 'int',
        'capacity' => 'int',
        'ship_has_seats_scheme' => 'bool',
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

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $saved = parent::save($options);

        if ($saved) {
            Trip::query()
                ->where('start_at', '>=', Carbon::now())
                ->where('ship_id', $this->id)
                ->update(['tickets_total' => $this->capacity]);
        }

        return $saved;
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function seat_categories_ticket_grades()
    {
        return $this->hasMany(ShipSeatCategoryTicketGrade::class, 'ship_id', 'id');
    }

    public function partner()
    {
        return $this->hasOne(Partner::class, 'id', 'partner_id');
    }
}
