<?php

namespace App\Http\Controllers\API\Dictionary;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\AbstractDictionary;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Dictionaries\AccountTransactionTypePrimary;
use App\Models\Dictionaries\AccountTransactionTypeRefill;
use App\Models\Dictionaries\ExcursionProgram;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\Role;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Dictionaries\TerminalStatus;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TripDiscountStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Dictionaries\UserContactType;
use App\Models\Dictionaries\UserStatus;
use App\Models\Excursions\Excursion;
use App\Models\Partner\Partner;
use App\Models\Piers\Pier;
use App\Models\POS\TerminalPositions;
use App\Models\Ships\Ship;
use App\Models\User\Helpers\Currents;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DictionaryController extends ApiController
{
    protected array $dictionaries = [
        'excursion_programs' => ['class' => ExcursionProgram::class, 'allow' => 'staff_admin,partner'],
        'excursion_statuses' => ['class' => ExcursionStatus::class, 'allow' => 'staff_admin'],
        'excursions' => ['class' => Excursion::class, 'allow' => 'staff_admin,partner'],
        'order_types' => ['class' => OrderType::class, 'allow' => 'staff_admin,partner'],
        'partner_statuses' => ['class' => PartnerStatus::class, 'allow' => 'staff_admin'],
        'partner_types' => ['class' => PartnerType::class, 'allow' => 'staff_admin'],
        'partners' => ['class' => Partner::class, 'allow' => 'staff_admin'],
        'pier_statuses' => ['class' => PiersStatus::class, 'allow' => 'staff_admin'],
        'piers' => ['class' => Pier::class, 'allow' => 'staff_admin,partner'],
        'position_access_statuses' => ['class' => PositionAccessStatus::class, 'allow' => 'staff_admin'],
        'position_statuses' => ['class' => PositionStatus::class, 'allow' => 'staff_admin'],
        'representatives' => ['class' => User::class, 'allow' => 'staff_admin'],
        'roles' => ['class' => Role::class, 'allow' => 'staff_admin'],
        'ships' => ['class' => Ship::class, 'allow' => 'staff_admin'],
        'ships_statuses' => ['class' => ShipStatus::class, 'allow' => 'staff_admin'],
        'terminal_positions' => ['class' => TerminalPositions::class, 'allow' => 'staff_admin'],
        'terminal_statuses' => ['class' => TerminalStatus::class, 'allow' => 'staff_admin'],
        'ticket_grades' => ['class' => TicketGrade::class, 'allow' => 'staff_admin,partner'],
        'transaction_primary_types' => ['class' => AccountTransactionTypePrimary::class, 'allow' => 'staff_admin,partner'],
        'transaction_refill_types' => ['class' => AccountTransactionTypeRefill::class, 'allow' => 'staff_admin'],
        'transaction_types' => ['class' => AccountTransactionType::class, 'allow' => 'staff_admin'],
        'trip_discount_statuses' => ['class' => TripDiscountStatus::class, 'allow' => 'staff_admin'],
        'trip_sale_statuses' => ['class' => TripSaleStatus::class, 'allow' => 'staff_admin'],
        'trip_statuses' => ['class' => TripStatus::class, 'allow' => 'staff_admin'],
        'user_contact_types' => ['class' => UserContactType::class, 'allow' => 'staff_admin'],
        'user_statuses' => ['class' => UserStatus::class, 'allow' => 'staff_admin'],
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
            return APIResponse::notFound("Справочник $name не найден");
        }

        $dictionary = $this->dictionaries[$name];

        if (!empty($dictionary['allow'])) {
            $current = Currents::get($request);
            if (!$this->isAllowed($current, $dictionary['allow'])) {
                return APIResponse::forbidden("Нет прав на просмотр справочника $name");
            }
        }

        /** @var AbstractDictionary $class */
        $class = $dictionary['class'];

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

        return APIResponse::response($dictionary, null, null, $actual);
    }

    /**
     * Check ability to view dictionary.
     *
     * @param Currents $current
     * @param string|null $abilities
     *
     * @return  bool
     */
    public function isAllowed(Currents $current, ?string $abilities): bool
    {
        if (empty($abilities)) {
            return true;
        }

        $rules = explode(',', $abilities);

        foreach ($rules as $rule) {
            $set = explode('_', $rule);
            if ($set[0] === 'partner' && $current->isRepresentative()) {
                return true;
            }
            if ($set[0] === 'staff' && !isset($set[1]) && $current->isStaff()) {
                return true;
            }
            if ($set[0] === 'staff' && isset($set[1]) && $set[1] === 'admin' && $current->isStaffAdmin()) {
                return true;
            }
            if ($set[0] === 'staff' && isset($set[1]) && $set[1] === 'terminal' && $current->isStaffTerminal()) {
                return true;
            }
        }

        return false;
    }
}
