<?php

namespace App\Models\Positions;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $position_id
 * @property string $external_id
 * @property string $email
 * @property string $work_phone
 * @property string $work_phone_additional
 * @property string $mobile_phone
 * @property string $vkontakte
 * @property string $facebook
 * @property string $telegram
 * @property string $skype
 * @property string $whatsapp
 * @property string $notes
 */
class StaffPositionInfo extends Model
{
    use HasFactory;

    /** @var string The primary key associated with the table. */
    protected $primaryKey = 'position_id';

    /** @var bool Disable auto-incrementing on model. */
    public $incrementing = false;

    /** @var string Referenced table. */
    protected $table = 'staff_position_info';
}
