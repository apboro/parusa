<?php

namespace App\Models\QrCodes;

use Illuminate\Database\Eloquent\Model;

/**
 * @property bool $is_visitt
 * @property bool $is_payment
 */
class QrCodesStatistic extends Model
{
    protected $guarded=[];
    public $timestamps=false;
    protected $casts = [
        'is_visit' => 'bool',
        'is_payment' => 'bool',
    ];

    public function qr_code()
    {
        return $this->hasOne(QrCode::class, 'id', 'qr_code_id');
    }
}
