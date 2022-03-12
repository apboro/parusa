<?php

namespace App\Models\Payments;

use App\Models\Model;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $gate
 * @property int $order_id
 * @property string $fiscal
 * @property int $total
 * @property int $by_card
 * @property int $by_cash
 * @property string $external_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Payment extends Model
{

}
