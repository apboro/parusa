<?php

namespace App\Models\Sails;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $pier_id
 * @property string $work_time
 * @property string $phone
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
        'phone',
        'address',
        'latitude',
        'longitude',
        'description',
        'way_to',
    ];
}
