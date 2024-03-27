<?php

namespace App\Services\YagaAPI\Model;

use App\Models\Sails\Trip;
use App\Models\Ships\Ship;
use Illuminate\Http\Request;

class Venue
{
    protected int $id;
    protected string $name;
    protected int $cityId;
    protected ?string $address;
    protected ?string $description;
    protected array $urls;
    protected array $images;
    protected array $videos;
    protected string $subwayStations;
    protected array $phones;
    protected array $workTimes;
    protected array $coordinates;
    protected array $synonyms;
    protected array $tags;
    protected array $types;
    protected ?string $cancelAllowance;
    protected string $saleOpening;
    protected ?string $saleClosing;
    protected ?string $saleCanceling;
    protected ?string $reservationTimeout;
    protected array $integrations;
    protected object $additional;

    public function __construct(Ship $ship)
    {
        $this->id = $ship->id;
        $this->name = $ship->name;
        $this->cityId = 1;
        $this->address = Trip::activeScarletSails()->where('ship_id', $ship->id)->first()->startPier->info->address;
        $this->description = $ship->description;
        $this->urls = [];
        $this->images = [];
        $this->videos = [];
        $this->subwayStations = '';
        $this->phones = [];
        $this->workTimes = [];
        $this->coordinates = [];
        $this->synonyms = [];
        $this->tags = [];
        $this->types = ['OTHER_VENUE'];
        $this->cancelAllowance = 'CANCEL_ALLOWED';
        $this->saleOpening = '';
        $this->saleClosing = '20 minutes';
        $this->saleCanceling = '60 minutes';
        $this->reservationTimeout = '15 minutes';
        $this->integrations = [];
        $this->additional = (object)[];
    }

    public function getResource()
    {
        return [
            "address" => $this->address,
            "cancelAllowance" => $this->cancelAllowance,
            "cityId" => $this->cityId,
//            "coordinates" => (object)$this->coordinates,
            "description" => $this->description,
            "id" => $this->id,
//            "images" => $this->images,
//            "integrations" => $this->integrations,
            "name" => $this->name,
//            "phones" => $this->phones,
            "reservationTimeout" => $this->reservationTimeout,
            "saleCanceling" => $this->saleCanceling,
            "saleClosing" => $this->saleClosing,
//            "saleOpening" => $this->saleOpening,
//            "subwayStations" => $this->subwayStations,
//            "synonyms" => $this->synonyms,
//            "tags" => $this->tags,
            "types" => $this->types,
//            "urls" => $this->urls,
//            "videos" => $this->videos,
//            "workTimes" => $this->workTimes,
        ];
    }

}


