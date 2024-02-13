<?php

namespace App\Models\QrCodes;

use App\Models\Partner\Partner;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property integer $id
 * @property string $name
 * @property string $link
 * @property string $hash
 * @property integer $partner_id
 * @property integer $visits_count
 * @property integer $payed_tickets_count
 * @property string $qrcode
 *
 * @property Partner $partner
 */
class QrCode extends Model
{
    protected $guarded=[];
    public function partner(): HasOne
    {
        return $this->hasOne(Partner::class, 'id', 'partner_id');
    }

    public function statistic(): HasMany
    {
        return $this->hasMany(QrCodesStatistic::class, 'qr_code_id', 'id');
    }

    public function statisticVisit(): HasMany
    {
        return $this->hasMany(QrCodesStatistic::class, 'qr_code_id', 'id')->where('is_visit', true);
    }

    public function statisticPayment(): HasMany
    {
        return $this->hasMany(QrCodesStatistic::class, 'qr_code_id', 'id')->where('is_payment', true);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(static function (self $qrCode) {
            $hash = null;
            while ($hash === null) {
                $hash = md5(Carbon::now()->toISOString());
                if (self::query()->where('hash', $hash)->count() > 0) {
                    $hash = null;
                }
            }
            $qrCode->hash = $hash;
        });
    }

    public static function makeLinkForQrCode($id)
    {
        return route('qrlink', self::find($id)->hash);
    }

}
