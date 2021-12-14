<?php

namespace App\Models\Sails;

use App\Exceptions\Sails\WrongPierStatusException;
use App\Interfaces\Statusable;
use App\Models\Dictionaries\Interfaces\AsDictionary;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Model;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $pier_id
 * @property string $work_time
 * @property string $address
 * @property string $latitude
 * @property string $longitude
 * @property string $description
 * @property string $way_to
 *
 */
class PierInfo extends Model
{
    use HasFactory;

    /** @var string Referenced table name. */
    protected $table = 'pier_info';

    /** @var string The primary key associated with the table. */
    protected $primaryKey = 'pier_id';

    /** @var bool Disable auto-incrementing on model. */
    public $incrementing = false;

    /** @var string[] Fillable attributes. */
    protected $fillable = [
        'work_time',
        'address',
        'latitude',
        'longitude',
        'description',
        'way_to',
    ];
}
