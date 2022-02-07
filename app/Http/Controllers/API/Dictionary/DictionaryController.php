<?php

namespace App\Http\Controllers\API\Dictionary;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\AbstractDictionary;
use App\Models\Dictionaries\AccountTransactionTypePrimary;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Dictionaries\AccountTransactionTypeRefill;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TripDiscountStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Dictionaries\UserContactType;
use App\Models\Dictionaries\Role;
use App\Models\Dictionaries\UserStatus;
use App\Models\Partner\Partner;
use App\Models\Sails\Excursion;
use App\Models\Sails\Pier;
use App\Models\Sails\Ship;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DictionaryController extends ApiController
{
    protected array $dictionaries = [
        'user_statuses' => UserStatus::class,
        'user_contact_types' => UserContactType::class,

        'partner_types' => PartnerType::class,
        'partner_statuses' => PartnerStatus::class,

        'position_statuses' => PositionStatus::class,
        'position_access_statuses' => PositionAccessStatus::class,
        'roles' => Role::class,

        'partners' => Partner::class,
        'representatives' => User::class,

        'ships' => Ship::class,
        'ships_statuses' => ShipStatus::class,

        'piers' => Pier::class,
        'pier_statuses' => PiersStatus::class,

        'excursions' => Excursion::class,
        'excursion_statuses' => ExcursionStatus::class,

        'excursion_programs' => ExcursionProgram::class,

        'trip_statuses' => TripStatus::class,
        'trip_sale_statuses' => TripSaleStatus::class,
        'trip_discount_statuses' => TripDiscountStatus::class,

        'ticket_grades' => TicketGrade::class,

        'transaction_types' => AccountTransactionType::class,
        'transaction_primary_types' => AccountTransactionTypePrimary::class,
        'transaction_refill_types' => AccountTransactionTypeRefill::class,

        'order_types' => OrderType::class,
    ];

    /**
     * Get dictionary.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function getDictionary(Request $request): JsonResponse
    {
        $name = $request->input('dictionary');

        if ($name === null || !array_key_exists($name, $this->dictionaries)) {
            return APIResponse::notFound("Справочник {$name} не найден");
        }

        /** @var AbstractDictionary $class */
        $class = $this->dictionaries[$name];

        if (method_exists($class, 'asDictionary')) {
            $query = $class::asDictionary();
        } else {
            $query = $class::query();
        }
        $actual = $query->clone()->latest('updated_at')->value('updated_at');
        $actual = Carbon::parse($actual)->setTimezone('GMT');

        $requested = $request->hasHeader('If-Modified-Since') ?
            Carbon::createFromFormat('D\, d M Y H:i:s \G\M\T', $request->header('If-Modified-Since'), 'GMT')
            : null;

        if ($requested >= $actual) {
            return APIResponse::notModified();
        }

        $dictionary = $query->orderBy('order')->orderBy('name')->get();

        return APIResponse::listOld($dictionary, null, null, $actual);
    }
}
