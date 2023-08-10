<?php

namespace App\Models\Order;

use App\Exceptions\Account\AccountException;
use App\Exceptions\Tickets\WrongOrderException;
use App\Exceptions\Tickets\WrongOrderStatusException;
use App\Exceptions\Tickets\WrongOrderTypeException;
use App\Interfaces\Statusable;
use App\Interfaces\Typeable;
use App\Models\Dictionaries\AbstractDictionary;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Model;
use App\Models\Partner\Partner;
use App\Models\Payments\Payment;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\PromoCode\PromoCode;
use App\Models\Tickets\Ticket;
use App\Models\Tickets\TicketRate;

use App\Traits\HasStatus;
use App\Traits\HasType;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $status_id
 * @property int $type_id
 * @property int $partner_id
 * @property int|null $position_id
 * @property int|null $terminal_id
 * @property int|null $terminal_position_id
 * @property string|null $external_id
 * @property bool $payment_unconfirmed
 * @property string|null $name
 * @property string|null $email
 * @property string|null $phone
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $neva_travel_id
 *
 * @property OrderStatus $status
 * @property OrderType $type
 * @property Partner $partner
 * @property Position|null $position
 * @property Terminal|null $terminal
 * @property Position|null $cashier
 * @property Collection $tickets
 * @property Collection $payments
 * @property Collection<PromoCode> $promocode
 * @property string|null $neva_travel_order_number
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
        return $this->hasMany(Ticket::class, 'order_id', 'id')
            ->whereNotIn('status_id', TicketStatus::ticket_cancelled_statuses);
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
     * Order processed cashier position.
     *
     * @return  HasOne
     */
    public function cashier(): HasOne
    {
        return $this->hasOne(Position::class, 'id', 'terminal_position_id');
    }

    /**
     * Order processed terminal.
     *
     * @return  HasOne
     */
    public function terminal(): HasOne
    {
        return $this->hasOne(Terminal::class, 'id', 'terminal_id');
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
     * Order total.
     *
     * @return  float|null
     */
    public function total(): ?float
    {
        $this->loadMissing(['tickets', 'promocode']);

        $total = null;

        if ($this->promocode->count() > 0) {
            /** @var PromoCode|null $promocode */
            $promocode = $this->promocode->first();
            if ($promocode) {
                $tickets = $this->tickets->map(function (Ticket $ticket) {
                    return [
                        'trip_id' => $ticket->trip_id,
                        'grade_id' => $ticket->grade_id,
                        'quantity' => 1,
                    ];
                })->toArray();
                $calc = \App\Helpers\Promocode::calc($promocode->code, $tickets, $this->partner_id);
                if ($calc['status']) {
                    $total = $calc['discount_price'] ?? $calc['full_price'];
                }
            }
        }

        if ($total === null) {
            $total = 0;
            $this->tickets->map(function (Ticket $ticket) use (&$total) {
                // todo check ticket status
                $total += $ticket->base_price;
            });
        }

        return $total;
    }

    /**
     * Order factory.
     *
     * @param int $typeId Order initiator
     * @param Ticket $tickets Array of tickets to order
     * @param int $statusId Order initial status
     * @param int|null $partnerId Partner ID
     * @param int|null $positionId Position of partner made this order (or null)
     * @param int|null $terminalId Terminal this order made by
     * @param int|null $terminalPositionId
     * @param string|null $email Buyer details
     * @param string|null $name Buyer details
     * @param string|null $phone Buyer details
     * @param bool $strictPrice
     * @param string|null $promocode
     *
     * @return  Order
     * @see OrderType
     * @see Ticket
     * @see OrderStatus
     * @see Partner
     * @see Position
     */
    public static function make(
        int $typeId, array $tickets, int $statusId, ?int $partnerId, ?int $positionId, ?int $terminalId, ?int $terminalPositionId, ?string $email, ?string $name, ?string $phone,
        bool $strictPrice = true, ?string $promocode = null
    ): Order
    {
        if (empty($tickets)) {
            throw new WrongOrderException('Невозможно создать заказ без билетов.');
        }

        $now = Carbon::now();

        /** @var int[][] $available */
        $available = [];

        // check tickets
        foreach ($tickets as $ticket) {

            // check trip, rate, site sales only option
            $trip = $ticket->trip;

            if ($typeId !== OrderType::site && $trip->isOnlySite()) {
                throw new WrongOrderException('Невозможно оформить заказ с выбранными билетами. Их можно приобрести только на сайте.');
            }

            $rateList = $trip ? $trip->getRate() : null;
            /** @var TicketRate $rate */
            $rate = $rateList ? $rateList->rates()->where('grade_id', $ticket->grade_id)->first() : null;

            if (
                $trip === null
                || ($trip->start_at < $now && $trip->excursion->is_single_ticket == 0)
                || $rate === null
                || ($rate->base_price <= 0 && $rate->grade_id !== TicketGrade::guide)
                || !$trip->hasStatus(TripStatus::regular)
                || !$trip->hasStatus(TripSaleStatus::selling, 'sale_status_id')
            ) {
                throw new WrongOrderException('Невозможно добавить один или несколько билетов в заказ.');
            }

            // check quantity
            if (!isset($available[$trip->id])) {
                $available[$trip->id] = $trip->tickets_total - $trip->tickets()->whereIn('status_id', TicketStatus::ticket_countable_statuses)->count();
            }
            if ($available[$trip->id]-- <= 0 && $trip->excursion->is_single_ticket != 1) {
                throw new WrongOrderException('Невозможно добавить один или несколько билетов в заказ. Недостаточно свободных мест на рейсе.');
            }

            // calc base price if not set
            if ($ticket->base_price === null) {
                $ticket->base_price = $ticket->getCurrentPrice();
            } else if ($strictPrice && ($ticket->base_price < $rate->min_price || $ticket->base_price > $rate->max_price)) {
                throw new WrongOrderException('Невозможно добавить один или несколько билетов в заказ. Неверна указана цена билета.');
            }
        }

        // prepare order
        $order = new static();
        $order->setStatus($statusId, false);
        $order->setType($typeId, false);
        $order->partner_id = $partnerId;
        $order->position_id = $positionId;
        $order->terminal_id = $terminalId;
        $order->terminal_position_id = $terminalPositionId;
        $order->email = $email;
        $order->name = $name;
        $order->phone = $phone;

        $promocodeId = null;

        if ($promocode) {
            $calc = \App\Helpers\Promocode::calc($promocode, $tickets, $partnerId);
            if ($calc['status'] ?? false) {
                $promocodeId = PromoCode::query()->where('code', mb_strtolower($promocode))->value('id');
            }
        }
        try {
            DB::transaction(static function () use (&$order, $tickets, $promocodeId) {
                $order->save();
                foreach ($tickets as $ticket) {
                    $ticket->order_id = $order->id;
                    $ticket->save();
                }
                if ($promocodeId) {
                    $order->promocode()->sync([$promocodeId]);
                }
            });
        } catch (Exception $exception) {
            throw new WrongOrderException($exception->getMessage());
        }

        return $order;
    }

    /**
     * Pay commissions for this order.
     *
     * @return  void
     *
     * @throws AccountException
     */
    public function payCommissions(): void
    {
        if ($this->partner_id === null || !in_array($this->status_id, OrderStatus::partner_commission_pay_statuses)) {
            return;
        }

        foreach ($this->tickets as $ticket) {
            /** @var Ticket $ticket */
            $ticket->payCommission();
        }
    }

    /**
     * Payments list for this order.
     *
     * @return  HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id', 'id');
    }

    /**
     * The promocode owning this order.
     *
     * @return  BelongsToMany
     */
    public function promocode(): BelongsToMany
    {
        return $this->belongsToMany(PromoCode::class, 'promo_code_has_orders', 'order_id', 'promo_code_id');
    }
}
