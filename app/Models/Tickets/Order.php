<?php

namespace App\Models\Tickets;

use App\Exceptions\Account\AccountException;
use App\Exceptions\Tickets\WrongOrderException;
use App\Exceptions\Tickets\WrongOrderStatusException;
use App\Exceptions\Tickets\WrongOrderTypeException;
use App\Interfaces\Statusable;
use App\Interfaces\Typeable;
use App\Models\Dictionaries\AbstractDictionary;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Model;
use App\Models\Partner\Partner;
use App\Models\Positions\Position;
use App\Traits\HasStatus;
use App\Traits\HasType;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $status_id
 * @property int $partner_id
 * @property int|null $position_id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $phone
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property OrderStatus $status
 * @property OrderType $type
 * @property Partner $partner
 * @property Position|null $position
 * @property Collection $tickets
 */
class Order extends Model implements Statusable, Typeable
{
    use HasStatus, HasType;

    /** @var string[] Fillable attributes */
    protected $fillable = ['partner_id', 'position_id', 'name', 'email', 'phone'];

    /**
     * Order status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(OrderStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for order.
     *
     * @param int|AbstractDictionary $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongOrderStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $this->checkAndSetStatus(OrderStatus::class, $status, WrongOrderStatusException::class, $save);
    }

    /**
     * Partner`s type.
     *
     * @return  HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(OrderType::class, 'id', 'type_id');
    }

    /**
     * Check and set type of partner.
     *
     * @param int|OrderType $type
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongOrderTypeException
     */
    public function setType($type, bool $save = true): void
    {
        $this->checkAndSetType(OrderType::class, $type, WrongOrderTypeException::class, $save);
    }

    /**
     * Tickets in this order.
     *
     * @return  HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'order_id', 'id');
    }

    /**
     * Order related partner.
     *
     * @return  HasOne
     */
    public function partner(): HasOne
    {
        return $this->hasOne(Partner::class, 'id', 'partner_id');
    }

    /**
     * Order processed position.
     *
     * @return  HasOne
     */
    public function position(): HasOne
    {
        return $this->hasOne(Position::class, 'id', 'position_id');
    }

    /**
     *
     * @return  Carbon|null
     */
    public function reserveValidUntil(): ?Carbon
    {
        if (!in_array($this->status_id, OrderStatus::order_reserved_statuses)) {
            return null;
        }

        $this->loadMissing(['tickets' => function (Builder $query) {
            $query->with('trip');
        }]);

        $closest = null;

        $this->tickets->map(function (Ticket $ticket) use (&$closest) {
            $validUntil = $ticket->trip->start_at->clone()->subMinutes($ticket->trip->cancellation_time);
            if ($closest === null || $validUntil < $closest) {
                $closest = $validUntil;
            }
        });

        return $closest;
    }

    /**
     * @param int $typeId
     * @param Ticket[] $tickets
     * @param int $statusId
     * @param int $partnerId
     * @param int|null $positionId
     * @param string|null $email
     * @param string|null $name
     * @param string|null $phone
     *
     * @return  Order
     */
    public static function make(int $typeId, array $tickets, int $statusId, int $partnerId, ?int $positionId, ?string $email, ?string $name, ?string $phone): Order
    {
        if (empty($tickets)) {
            throw new WrongOrderException('Невозможно создать заказ без билетов.');
        }

        $now = Carbon::now();

        /** @var int[][] $available */
        $available = [];

        // check tickets
        foreach ($tickets as $ticket) {
            // check trip and rate
            if (
                ($trip = $ticket->trip) === null ||
                $trip->start_at < $now ||
                !$trip->hasStatus(TripSaleStatus::selling, 'sale_status_id') ||
                ($rate = $trip->getRate()) === null ||
                $rate->rates()->where('grade_id', $ticket->grade_id)->count() === 0
            ) {
                throw new WrongOrderException('Невозможно добавить один или несколько билетов в заказ.');
            }

            // check quantity
            if (!isset($available[$trip->id])) {
                $available[$trip->id] = $trip->tickets_total - $trip->tickets()->count();
            }
            if ($available[$trip->id]-- <= 0) {
                throw new WrongOrderException('Невозможно добавить один или несколько билетов в заказ. Недостаточно свободных мест на рейсе.');
            }

            // calc base price
            $ticket->base_price = $ticket->getCurrentPrice();
        }

        // prepare order
        $order = new static;
        $order->setStatus($statusId, false);
        $order->setType($typeId, false);
        $order->partner_id = $partnerId;
        $order->position_id = $positionId;
        $order->email = $email;
        $order->name = $name;
        $order->phone = $phone;


        try {
            DB::transaction(static function () use (&$order, $tickets) {
                $order->save();
                foreach ($tickets as $ticket) {
                    $ticket->order_id = $order->id;
                    $ticket->save();
                }
            });
        } catch (Exception $exception) {
            throw new WrongOrderException($exception->getMessage());
        }

        // todo check order for collisions

        return $order;
    }

    /**
     * Run commission pay for this order.
     *
     * @return  void
     *
     * @throws AccountException
     */
    public function payCommissions(): void
    {
        if (!in_array($this->status_id, OrderStatus::partner_commission_pay_statuses)) {
            return;
        }

        foreach ($this->tickets as $ticket) {
            /** @var Ticket $ticket */
            $ticket->payCommission();
        }
    }
}
