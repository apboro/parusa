<?php

namespace App\Models\Sails;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $excursion_id
 * @property integer $duration
 * @property string $announce
 * @property string $description
 *
 */
class ExcursionInfo extends Model
{
    use HasFactory;

    /** @var string Referenced table name. */
    protected $table = 'excursion_info';

    /** @var string The primary key associated with the table. */
    protected $primaryKey = 'excursion_id';

    /** @var bool Disable auto-incrementing on model. */
    public $incrementing = false;

    /** @var string[] Fillable attributes. */
    protected $fillable = [
        'announce',
        'description',
    ];
}
