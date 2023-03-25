<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property bool $is_visitt
 * @property bool $is_payment
 */
class QrCodesStatistic extends Model
{
    protected $guarded=[];
    protected $casts = [
        'is_visit' => 'bool',
        'is_payment' => 'bool',
    ];

    public function qr_code()
    {
        return $this->hasOne(QrCode::class, 'id', 'qr_code_id');
    }
}
