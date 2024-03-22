<?php
namespace App\Services\YagaAPI\Model;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;

/**
 * Class representing the Venue model.
 *
 * Площадка. Место проведения.  id               (*) - идентификатор площадки name             (*) - название площадки city_id          (*) - идентификатор города (City) address              - адрес площадки description          - произвольное описание площадки urls                 - images               - связанные изображения/фотографии/картинки videos               - связанные видео subway_stations      - список ближайших станций метро phones               - телефоны площадки work_times           - часы работы площадки coordinates          - географические координаты площадки synonyms             - список синонимов tags                 - произвольные теги types                - список подходящих типов cancel_allowance (*) - признак того, разрещена ли отмена билетов после на данной площзадке sale_opening         - за сколько времени до начала события открываются продажи sale_closing         - за сколько времени до начала события продажи закрываются sale_canceling       - за сколько времени до начала события возможна отмена купленных билетов (если она возможна) reservation_timeout  - сколько времени держится бронь во время покупки билета integrations         - идентификатор площадки в сторонних базах данных (например, Яндекс.Карты) additional           - служебное поле
 *
 * @package YagaSchedule\Server\Model
 * @author  Swagger Codegen team
 */
class Venue
{
        /**
     * @var string|null
     * @SerializedName("id")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $id;

    /**
     * @var string|null
     * @SerializedName("name")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $name;

    /**
     * @var string|null
     * @SerializedName("cityId")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $cityId;

    /**
     * @var string|null
     * @SerializedName("address")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $address;

    /**
     * @var string|null
     * @SerializedName("description")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $description;

    /**
     * @var string[]|null
     * @SerializedName("urls")
     * @Assert\All({
     *   @Assert\Type("string")
     * })
     * @Type("array<string>")
     */
    protected $urls;

    /**
     * @var YagaSchedule\Server\Model\Image[]|null
     * @SerializedName("images")
     * @Assert\All({
     *   @Assert\Type("YagaSchedule\Server\Model\Image")
     * })
     * @Type("array<YagaSchedule\Server\Model\Image>")
     */
    protected $images;

    /**
     * @var YagaSchedule\Server\Model\Video[]|null
     * @SerializedName("videos")
     * @Assert\All({
     *   @Assert\Type("YagaSchedule\Server\Model\Video")
     * })
     * @Type("array<YagaSchedule\Server\Model\Video>")
     */
    protected $videos;

    /**
     * @var string[]|null
     * @SerializedName("subwayStations")
     * @Assert\All({
     *   @Assert\Type("string")
     * })
     * @Type("array<string>")
     */
    protected $subwayStations;

    /**
     * @var YagaSchedule\Server\Model\Phone[]|null
     * @SerializedName("phones")
     * @Assert\All({
     *   @Assert\Type("YagaSchedule\Server\Model\Phone")
     * })
     * @Type("array<YagaSchedule\Server\Model\Phone>")
     */
    protected $phones;

    /**
     * @var YagaSchedule\Server\Model\WorkTime[]|null
     * @SerializedName("workTimes")
     * @Assert\All({
     *   @Assert\Type("YagaSchedule\Server\Model\WorkTime")
     * })
     * @Type("array<YagaSchedule\Server\Model\WorkTime>")
     */
    protected $workTimes;

    /**
     * @var YagaSchedule\Server\Model\Coordinates|null
     * @SerializedName("coordinates")
     * @Assert\Type("YagaSchedule\Server\Model\Coordinates")
     * @Type("YagaSchedule\Server\Model\Coordinates")
     */
    protected $coordinates;

    /**
     * @var string[]|null
     * @SerializedName("synonyms")
     * @Assert\All({
     *   @Assert\Type("string")
     * })
     * @Type("array<string>")
     */
    protected $synonyms;

    /**
     * @var YagaSchedule\Server\Model\Tag[]|null
     * @SerializedName("tags")
     * @Assert\All({
     *   @Assert\Type("YagaSchedule\Server\Model\Tag")
     * })
     * @Type("array<YagaSchedule\Server\Model\Tag>")
     */
    protected $tags;

    /**
     * @var YagaSchedule\Server\Model\VenueType[]|null
     * @SerializedName("types")
     * @Assert\All({
     *   @Assert\Type("YagaSchedule\Server\Model\VenueType")
     * })
     * @Type("array<YagaSchedule\Server\Model\VenueType>")
     */
    protected $types;

    /**
     * @var YagaSchedule\Server\Model\CancelAllowance|null
     * @SerializedName("cancelAllowance")
     * @Assert\Type("YagaSchedule\Server\Model\CancelAllowance")
     * @Type("YagaSchedule\Server\Model\CancelAllowance")
     */
    protected $cancelAllowance;

    /**
     * @var string|null
     * @SerializedName("saleOpening")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $saleOpening;

    /**
     * @var string|null
     * @SerializedName("saleClosing")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $saleClosing;

    /**
     * @var string|null
     * @SerializedName("saleCanceling")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $saleCanceling;

    /**
     * @var string|null
     * @SerializedName("reservationTimeout")
     * @Assert\Type("string")
     * @Type("string")
     */
    protected $reservationTimeout;

    /**
     * @var YagaSchedule\Server\Model\SourceRef[]|null
     * @SerializedName("integrations")
     * @Assert\All({
     *   @Assert\Type("YagaSchedule\Server\Model\SourceRef")
     * })
     * @Type("array<YagaSchedule\Server\Model\SourceRef>")
     */
    protected $integrations;

    /**
     * @var array|null
     * @SerializedName("additional")
     * @Assert\Type("array")
     * @Type("array")
     */
    protected $additional;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->cityId = isset($data['cityId']) ? $data['cityId'] : null;
        $this->address = isset($data['address']) ? $data['address'] : null;
        $this->description = isset($data['description']) ? $data['description'] : null;
        $this->urls = isset($data['urls']) ? $data['urls'] : null;
        $this->images = isset($data['images']) ? $data['images'] : null;
        $this->videos = isset($data['videos']) ? $data['videos'] : null;
        $this->subwayStations = isset($data['subwayStations']) ? $data['subwayStations'] : null;
        $this->phones = isset($data['phones']) ? $data['phones'] : null;
        $this->workTimes = isset($data['workTimes']) ? $data['workTimes'] : null;
        $this->coordinates = isset($data['coordinates']) ? $data['coordinates'] : null;
        $this->synonyms = isset($data['synonyms']) ? $data['synonyms'] : null;
        $this->tags = isset($data['tags']) ? $data['tags'] : null;
        $this->types = isset($data['types']) ? $data['types'] : null;
        $this->cancelAllowance = isset($data['cancelAllowance']) ? $data['cancelAllowance'] : null;
        $this->saleOpening = isset($data['saleOpening']) ? $data['saleOpening'] : null;
        $this->saleClosing = isset($data['saleClosing']) ? $data['saleClosing'] : null;
        $this->saleCanceling = isset($data['saleCanceling']) ? $data['saleCanceling'] : null;
        $this->reservationTimeout = isset($data['reservationTimeout']) ? $data['reservationTimeout'] : null;
        $this->integrations = isset($data['integrations']) ? $data['integrations'] : null;
        $this->additional = isset($data['additional']) ? $data['additional'] : null;
    }

    /**
     * Gets id.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets id.
     *
     * @param string|null $id
     *
     * @return $this
     */
    public function setId($id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets name.
     *
     * @param string|null $name
     *
     * @return $this
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets cityId.
     *
     * @return string|null
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * Sets cityId.
     *
     * @param string|null $cityId
     *
     * @return $this
     */
    public function setCityId($cityId = null)
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * Gets address.
     *
     * @return string|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets address.
     *
     * @param string|null $address
     *
     * @return $this
     */
    public function setAddress($address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Gets description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets description.
     *
     * @param string|null $description
     *
     * @return $this
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets urls.
     *
     * @return string[]|null
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * Sets urls.
     *
     * @param string[]|null $urls
     *
     * @return $this
     */
    public function setUrls($urls = null)
    {
        $this->urls = $urls;

        return $this;
    }

    /**
     * Gets images.
     *
     * @return \App\Services\YagaAPI\Model\Image[]|null
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Sets images.
     *
     * @param YagaSchedule\Server\Model\Image[]|null $images
     *
     * @return $this
     */
    public function setImages(Image $images = null)
    {
        $this->images = $images;

        return $this;
    }

    /**
     * Gets videos.
     *
     * @return \App\Services\YagaAPI\Model\Video[]|null
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Sets videos.
     *
     * @param YagaSchedule\Server\Model\Video[]|null $videos
     *
     * @return $this
     */
    public function setVideos(Video $videos = null)
    {
        $this->videos = $videos;

        return $this;
    }

    /**
     * Gets subwayStations.
     *
     * @return string[]|null
     */
    public function getSubwayStations()
    {
        return $this->subwayStations;
    }

    /**
     * Sets subwayStations.
     *
     * @param string[]|null $subwayStations
     *
     * @return $this
     */
    public function setSubwayStations($subwayStations = null)
    {
        $this->subwayStations = $subwayStations;

        return $this;
    }

    /**
     * Gets phones.
     *
     * @return \App\Services\YagaAPI\Model\Phone[]|null
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * Sets phones.
     *
     * @param YagaSchedule\Server\Model\Phone[]|null $phones
     *
     * @return $this
     */
    public function setPhones(Phone $phones = null)
    {
        $this->phones = $phones;

        return $this;
    }

    /**
     * Gets workTimes.
     *
     * @return \App\Services\YagaAPI\Model\WorkTime[]|null
     */
    public function getWorkTimes()
    {
        return $this->workTimes;
    }

    /**
     * Sets workTimes.
     *
     * @param YagaSchedule\Server\Model\WorkTime[]|null $workTimes
     *
     * @return $this
     */
    public function setWorkTimes(WorkTime $workTimes = null)
    {
        $this->workTimes = $workTimes;

        return $this;
    }

    /**
     * Gets coordinates.
     *
     * @return \App\Services\YagaAPI\Model\Coordinates|null
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * Sets coordinates.
     *
     * @param YagaSchedule\Server\Model\Coordinates|null $coordinates
     *
     * @return $this
     */
    public function setCoordinates(Coordinates $coordinates = null)
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    /**
     * Gets synonyms.
     *
     * @return string[]|null
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }

    /**
     * Sets synonyms.
     *
     * @param string[]|null $synonyms
     *
     * @return $this
     */
    public function setSynonyms($synonyms = null)
    {
        $this->synonyms = $synonyms;

        return $this;
    }

    /**
     * Gets tags.
     *
     * @return \App\Services\YagaAPI\Model\Tag[]|null
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Sets tags.
     *
     * @param YagaSchedule\Server\Model\Tag[]|null $tags
     *
     * @return $this
     */
    public function setTags(Tag $tags = null)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Gets types.
     *
     * @return \App\Services\YagaAPI\Model\VenueType[]|null
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Sets types.
     *
     * @param YagaSchedule\Server\Model\VenueType[]|null $types
     *
     * @return $this
     */
    public function setTypes(VenueType $types = null)
    {
        $this->types = $types;

        return $this;
    }

    /**
     * Gets cancelAllowance.
     *
     * @return \App\Services\YagaAPI\Model\CancelAllowance|null
     */
    public function getCancelAllowance()
    {
        return $this->cancelAllowance;
    }

    /**
     * Sets cancelAllowance.
     *
     * @param YagaSchedule\Server\Model\CancelAllowance|null $cancelAllowance
     *
     * @return $this
     */
    public function setCancelAllowance(CancelAllowance $cancelAllowance = null)
    {
        $this->cancelAllowance = $cancelAllowance;

        return $this;
    }

    /**
     * Gets saleOpening.
     *
     * @return string|null
     */
    public function getSaleOpening()
    {
        return $this->saleOpening;
    }

    /**
     * Sets saleOpening.
     *
     * @param string|null $saleOpening
     *
     * @return $this
     */
    public function setSaleOpening($saleOpening = null)
    {
        $this->saleOpening = $saleOpening;

        return $this;
    }

    /**
     * Gets saleClosing.
     *
     * @return string|null
     */
    public function getSaleClosing()
    {
        return $this->saleClosing;
    }

    /**
     * Sets saleClosing.
     *
     * @param string|null $saleClosing
     *
     * @return $this
     */
    public function setSaleClosing($saleClosing = null)
    {
        $this->saleClosing = $saleClosing;

        return $this;
    }

    /**
     * Gets saleCanceling.
     *
     * @return string|null
     */
    public function getSaleCanceling()
    {
        return $this->saleCanceling;
    }

    /**
     * Sets saleCanceling.
     *
     * @param string|null $saleCanceling
     *
     * @return $this
     */
    public function setSaleCanceling($saleCanceling = null)
    {
        $this->saleCanceling = $saleCanceling;

        return $this;
    }

    /**
     * Gets reservationTimeout.
     *
     * @return string|null
     */
    public function getReservationTimeout()
    {
        return $this->reservationTimeout;
    }

    /**
     * Sets reservationTimeout.
     *
     * @param string|null $reservationTimeout
     *
     * @return $this
     */
    public function setReservationTimeout($reservationTimeout = null)
    {
        $this->reservationTimeout = $reservationTimeout;

        return $this;
    }

    /**
     * Gets integrations.
     *
     * @return \App\Services\YagaAPI\Model\SourceRef[]|null
     */
    public function getIntegrations()
    {
        return $this->integrations;
    }

    /**
     * Sets integrations.
     *
     * @param YagaSchedule\Server\Model\SourceRef[]|null $integrations
     *
     * @return $this
     */
    public function setIntegrations(SourceRef $integrations = null)
    {
        $this->integrations = $integrations;

        return $this;
    }

    /**
     * Gets additional.
     *
     * @return array|null
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * Sets additional.
     *
     * @param array|null $additional
     *
     * @return $this
     */
    public function setAdditional(array $additional = null)
    {
        $this->additional = $additional;

        return $this;
    }
}


