<?php

namespace App\Http\Controllers\API\Dictionary;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\AbstractDictionary;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\UserContactType;
use App\Models\Dictionaries\UserRole;
use App\Models\Dictionaries\UserStatus;
use App\Models\Partner\Partner;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DictionaryListController extends ApiController
{
    protected array $dictionaries = [
        'user_roles' => UserRole::class,
        'user_statuses' => UserStatus::class,
        'user_contact_types' => UserContactType::class,
        'partner_types' => PartnerType::class,
        'partner_statuses' => PartnerStatus::class,
        'position_statuses' => PositionStatus::class,
        'partners' => Partner::class,
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
            return APIResponse::notFound();
        }

        /** @var AbstractDictionary $class */
        $class = $this->dictionaries[$name];

        if(method_exists($class, 'asDictionary')) {
            $query = $class::asDictionary();
        }else{
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

        $dictionary = $query->get();

        return APIResponse::list($dictionary, null, null, $actual);
    }
}
