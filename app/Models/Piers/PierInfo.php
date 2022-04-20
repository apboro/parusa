<?php

namespace App\Models\Piers;

use App\Models\Model;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeNone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;

/**
 * @property int $pier_id
 * @property string $work_time
 * @property string $phone
 * @property string $address
 * @property string $latitude
 * @property string $longitude
 * @property string $description
 * @property string $way_to
 * @property string $map_link
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

    /**
     * Make qr-code with pier map link.
     *
     * @return string
     */
    public function mapLinkQr(): string
    {
        return Builder::create()
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->size(200)
            ->margin(0)
            ->roundBlockSizeMode(new RoundBlockSizeModeNone())
            ->data($this->map_link ?? '')
            ->build()
            ->getDataUri();
    }
}
