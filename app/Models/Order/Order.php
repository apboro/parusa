<?php

namespace App\Models\Order;

use App\Exceptions\Account\AccountException;
use App\Exceptions\Tickets\WrongOrderStatusException;
use App\Exceptions\Tickets\WrongOrderTypeException;
use App\Interfaces\Statusable;
use App\Interfaces\Typeable;
use App\Models\Dictionaries\AbstractDictionary;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Integration\AdditionalDataOrder;
use App\Models\Model;
use App\Models\Partner\Partner;
use App\Models\Payments\Payment;
use App\Models\POS\Terminal;
use App\Models\Positions\Position;
use App\Models\PromoCode\PromoCode;
use App\Models\Tickets\BackwardTicket;
use App\Models\Tickets\Ticket;
use App\Traits\HasStatus;
use App\Traits\HasType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $status_id
 * @property int $type_id
 * @property int $partner_id
 * @property string|null $hash
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
 *
 * @property OrderStatus $status
 * @property OrderType $type
 * @property Partner $partner
 * @property Position|null $position
 * @property Terminal|null $terminal
 * @property Position|null $cashier
 * @property Collection<Ticket> $tickets
 * @property Collection $payments
 * @property Collection<PromoCode> $promocode
 * @property AdditionalDataOrder $additionalData
 */
class Order extends Model implements Statusable, Typeable
{
    use HasStatus, HasType, HasFactory;

    /** @var string[] Fillable attributes */
    protected $fillable = ['partner_id', 'position_id', 'name', 'email', 'phone', 'hash'];

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

    protected static function boot()
    {
        parent::boot();
        static::creating(static function (self $approvement) {

        $hash = Str::random(16);

        while (Order::where('hash', $hash)->exists()) {
            $hash = Str::random(16);
        }
            $approvement->hash = $hash;
        });
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

    public function additionalData()
    {
        return $this->hasOne(AdditionalDataOrder::class, 'order_id', 'id');
    }

    public function backwards(): HasMany
    {
        return $this->hasMany(BackwardTicket::class);
    }
}
