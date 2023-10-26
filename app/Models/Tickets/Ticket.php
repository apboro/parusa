<?php

namespace App\Models\Tickets;

use App\Exceptions\Account\AccountException;
use App\Exceptions\Tickets\WrongTicketStatusException;
use App\Helpers\PriceConverter;
use App\Interfaces\Statusable;
use App\Models\Account\AccountTransaction;
use App\Models\AdditionalDataTicket;
use App\Models\BackwardTicket;
use App\Models\Dictionaries\AccountTransactionStatus;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TerminalStatus;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Model;
use App\Models\Order\Order;
use App\Models\Positions\Position;
use App\Models\Sails\Trip;
use App\Models\WorkShift\WorkShift;
use App\Settings;
use App\Traits\HasStatus;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeNone;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use JsonException;

/**
 * @property int $id
 * @property int $status_id
 * @property int $order_id
 * @property int $trip_id
 * @property int $grade_id
 * @property float $base_price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property TicketStatus $status
 *
 * @property Order $order
 * @property Trip $trip
 * @property TicketGrade $grade
 * @property AccountTransaction $transaction
 * @property TicketReturn $return
 * @property BackwardTicket $backward
 * @property Boolean $IsBackward
 * @property AdditionalDataTicket $additionalData
 */
class Ticket extends Model implements Statusable
{
    use HasStatus;

    /** @var string[] Fillable attributes. */
    protected $fillable = ['trip_id', 'grade_id', 'status_id', 'base_price', 'neva_travel_ticket', 'provider_id'];

    /**
     * User's status.
     *
     * @return  HasOne
     */
    public function status(): HasOne
    {
        return $this->hasOne(TicketStatus::class, 'id', 'status_id');
    }

    /**
     * Check and set new status for user.
     *
     * @param int|TicketStatus $status
     * @param bool $save
     *
     * @return  void
     *
     * @throws WrongTicketStatusException
     */
    public function setStatus($status, bool $save = true): void
    {
        $isSingleTicket = $this->trip->excursion->is_single_ticket;
        if ($status === TicketStatus::terminal_paid && $isSingleTicket) {
            $this->checkAndSetStatus(TicketStatus::class, TicketStatus::terminal_paid_single, WrongTicketStatusException::class, $save);
        } elseif ($status === TicketStatus::partner_paid && $isSingleTicket) {
            $this->checkAndSetStatus(TicketStatus::class, TicketStatus::partner_paid_single, WrongTicketStatusException::class, $save);
        } elseif ($status === TicketStatus::showcase_paid && $isSingleTicket) {
            $this->checkAndSetStatus(TicketStatus::class, TicketStatus::showcase_paid_single, WrongTicketStatusException::class, $save);
        } else {
            $this->checkAndSetStatus(TicketStatus::class, $status, WrongTicketStatusException::class, $save);
        }
    }

    /**
     * Order relation.
     *
     * @return  BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Trip relation.
     *
     * @return  BelongsTo
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Grade of ticket.
     *
     * @return  BelongsTo
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(TicketGrade::class);
    }

    /**
     * Related transaction.
     *
     * @return  HasOne
     */
    public function transaction(): HasOne
    {
        return $this->hasOne(AccountTransaction::class, 'ticket_id', 'id');
    }

    /**
     * Related return.
     *
     * @return  HasOne
     */
    public function return(): HasOne
    {
        return $this->hasOne(TicketReturn::class, 'ticket_id', 'id');
    }

    /**
     * Convert base_price from store value to real price.
     *
     * @param int|null $value
     *
     * @return  float
     */
    public function getBasePriceAttribute(?int $value): ?float
    {
        return $value !== null ? PriceConverter::storeToPrice($value) : null;
    }

    /**
     * Convert base_price to store value.
     *
     * @param float|null $value
     *
     * @return  void
     */
    public function setBasePriceAttribute(?float $value): void
    {
        $this->attributes['base_price'] = $value !== null ? PriceConverter::priceToStore($value) : null;
    }

    /**
     * Get ticket current price.
     *
     * @return  float|null
     */
    public function getCurrentPrice(): ?float
    {
        $rateList = $this->trip->getRate();

        return $rateList?->rates()->where('grade_id', $this->grade_id)->value('base_price');
    }

    /**
     * Pay commission for this ticket.
     *
     * @return  void
     *
     * @throws AccountException
     */
    public function payCommission(): void
    {
        $partner = $this->order->partner;

        if ($partner === null) {
            return;
        }

        $rateList = $this->trip->getRate();

        /** @var TicketRate $rate */
        if ($rateList === null || null === ($rate = $rateList->rates()->where('grade_id', $this->grade_id)->first())) {
            return;
        }

        $rate = $rate->partnerRates()->where('partner_id', $partner->id)->first() ?? $rate;

        if ($partner->type_id === PartnerType::promoter) {
            if ($openedShift = $partner->getOpenedShift()) {
                $dataForTransaction = $this->countPromotersCommission($openedShift);
                $this->saveCommissionsInShift($openedShift, $dataForTransaction['promoter_amount']);
            }
        }

        $partner->account->attachTransaction(new AccountTransaction([
            'type_id' => AccountTransactionType::tickets_sell_commission,
            'status_id' => AccountTransactionStatus::accepted,
            'timestamp' => Carbon::now(),
            'amount' => $dataForTransaction['promoter_amount'] ?? $rate->commission_value * ($rate->commission_type === 'fixed' ? 1 : $this->base_price / 100),
            'ticket_id' => $this->id,
            'commission_type' => $rate->commission_type,
            'commission_value' => $dataForTransaction['promoter_commission'] ?? $rate->commission_value,
            'commission_delta' => $dataForTransaction['commission_delta'] ?? 0,
        ]));
    }

    public function saveCommissionsInShift(WorkShift $shift, int $commission, $opened = true): void
    {
        if ($opened) {
            $shift->pay_commission = $shift->pay_commission + $commission;
            $shift->sales_total = $shift->sales_total + $this->base_price;
        } else {
            $shift->balance = $shift->balance - $commission;
            $shift->sales_total = $shift->sales_total - $this->base_price;
        }
        $shift->save();
    }

    public function countPromotersCommission(WorkShift $shift): array
    {
        $promoterCommission = ($this->provider_id === Provider::scarlet_sails || $this->provider_id === null)
            ? $shift->tariff->commission + $shift->commission_delta
            : Settings::get('promoters_commission_integrated_excursions', null, Settings::int);
        $promoterAmount = $promoterCommission * $this->base_price / 100;

        return [
            'promoter_commission' => $promoterCommission,
            'promoter_amount' => $promoterAmount,
            'commission_delta' => $shift->commission_delta
        ];
    }


    /**
     * Refund commission for this ticket.
     *
     * @param Position|null $committer
     * @param string|null $reason
     *
     * @return  void
     *
     * @throws AccountException
     */
    public function refundTicket(?Position $committer = null, string $reason = null): void
    {
        if (!in_array($this->status_id, TicketStatus::ticket_returnable_statuses, true)) {
            return;
        }

        $partner = $this->order->partner;

        if ($partner === null) {
            return;
        }

        $partner->account->attachTransaction(new AccountTransaction([
            'type_id' => AccountTransactionType::tickets_buy_return,
            'status_id' => AccountTransactionStatus::accepted,
            'timestamp' => Carbon::now(),
            'amount' => $this->base_price ?? 0,
            'ticket_id' => $this->id,
            'committer_id' => $committer->id ?? null,
            'comments' => $reason,
        ]));
    }

    /**
     * Refund commission for this ticket.
     *
     * @param Position|null $committer
     *
     * @return  void
     *
     * @throws AccountException
     */
    public function refundCommission(?Position $committer = null): void
    {
        if (!in_array($this->status_id, TicketStatus::ticket_returnable_statuses, true)) {
            return;
        }

        $partner = $this->order->partner;

        if ($partner === null) {
            return;
        }

        /** @var AccountTransaction $transaction */
        $transaction = $partner->account->transactions()
            ->where([
                'ticket_id' => $this->id,
                'type_id' => AccountTransactionType::tickets_sell_commission,
            ])
            ->first();

        if ($transaction === null) {
            throw new AccountException('Транзакция по зачислению средств не найдена.');
        }
        if ($partner->type_id === PartnerType::promoter) {
            if ($openedShift = $partner->getOpenedShift()) {
                $this->saveCommissionsInShift($openedShift, -$transaction->amount);
            } else {
                $lastShift = $partner->getLastShift();
                $this->saveCommissionsInShift($lastShift, $transaction->amount, false);
            }
        }

        $partner->account->attachTransaction(new AccountTransaction([
            'type_id' => AccountTransactionType::tickets_sell_commission_return,
            'status_id' => AccountTransactionStatus::accepted,
            'timestamp' => Carbon::now(),
            'amount' => $transaction->amount,
            'ticket_id' => $this->id,
            'commission_type' => $transaction->commission_type,
            'commission_value' => $transaction->commission_value,
        ]));
    }

    /**
     * Make qr-code for this ticket.
     *
     * @return string
     *
     * @throws JsonException
     */
    public function qr(): string
    {
        $payload = "1|t|$this->id";

        $signature = md5(config('app.key') . '|' . $payload);
        $payload .= '|' . $signature;

        return Builder::create()
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->size(200)
            ->margin(0)
            ->roundBlockSizeMode(new RoundBlockSizeModeNone())
            ->data(json_encode($payload, JSON_THROW_ON_ERROR))
            ->build()
            ->getDataUri();
    }

    public function provider_qr()
    {
        $this->loadMissing('additionalData');
        return Builder::create()
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->size(200)
            ->margin(0)
            ->roundBlockSizeMode(new RoundBlockSizeModeNone())
            ->data($this->additionalData->provider_qr_code)
            ->build()
            ->getDataUri();

    }

    public function isBackward(): bool
    {
        return $this->hasOne(BackwardTicket::class, 'backward_ticket_id', 'id')->exists();
    }

    public function backwardTicket()
    {
        return Ticket::find(BackwardTicket::where('main_ticket_id', $this->id)->first()?->backward_ticket_id);
    }

    public function mainTicket()
    {
        return Ticket::find(BackwardTicket::where('backward_ticket_id', $this->id)->first()?->main_ticket_id);
    }

    public function additionalData()
    {
        return $this->hasOne(AdditionalDataTicket::class, 'ticket_id', 'id');
    }

}
