<?php

namespace App\Services\AstraMarine;


class AstraMarineRepository
{
    private AstraMarineApiClientProvider $apiClient;

    public function __construct()
    {
        $this->apiClient = new AstraMarineApiClientProvider();
    }

    /**
     * Получает список услуг с заданными параметрами.
     *
     * @param array $query Массив параметров запроса.
     * Параметры:
     * - 'dateFrom' => string Начало периода.
     * - 'dateTo' => string Конец периода.
     * - 'email' => string Адрес электронной почты авторизованного пользователя. Необходим для ведения логов. При наличии обязателен для передачи.
     *
     * @return array Массив с данными об услугах.
     *  Возвращаемые ключи:
     *  - 'services' => array Массив услуг.
     *    Каждый элемент массива содержит следующие ключи:
     *      - 'serviceID' => string Идентификатор услуги.
     *      - 'serviceName' => string Наименование услуги.
     *      - 'serviceNoSeats' => bool Без мест.
     *      - 'serviceType' => string Тип услуги. Может принимать следующие значения: DateTimeSeat, DateTime, Date, Place.
     *      - 'statusRequestBuyer' => bool Показывает необходимость запроса у покупателя статуса «Резидент», «Не резидент».
     *      - 'formingBarCode' => bool Показывает, что при формировании продажи штрих-коды не формируются.
     *      - 'serviceDuration' => int Продолжительность в минутах.
     *      - 'barcodeType' => string Тип ШК для услуги (EAN_13, ITF, CODE_128A, CODE_128B, CODE_128C, CODE_39, QR).
     */
    public function getServices(array $query = []): array
    {
        return $this->apiClient->post('getServices', $query);
    }

    /**
     * Получает массив начальных и конечных точек маршрута.
     *
     * @param array $query Массив параметров запроса.
     * Параметры:
     * - 'pointID' => string Отбор по ID точки отправления\завершения маршрута
     * - 'email' => string Адрес электронной почты авторизованного пользователя. Необходим для ведения логов. При наличии обязателен для передачи.
     *
     * @return array Массив с данными.
     *  Возвращаемые ключи:
     *  - 'points' => array Массив точек отправления/завершения.
     *    Каждый элемент массива содержит следующие ключи:
     *      - 'pointID' => string Идентификатор точки.
     *      - 'pointName' => string Наименование точки.
     *      - 'pointAddress' => string Адрес точки.
     *      - 'pointLat' => string Широта.
     *      - 'pointLon' => string Долгота.
     *      - 'pointWheelchairBoarding' => string Ограничения для посадки для людей с ограниченными возможностями.
     */
    public function getRouteStartEndPoints(array $query = [])
    {
        return $this->apiClient->post('getRouteStartEndPoints', $query);
    }

    /**
     * Возвращает массив мероприятий за выбранный период. В случает отсутствия входных параметров возвращается массив мероприятий за период: начало дня ТекущейДаты по конец дня ТекущейДаты.
     *
     * @param array $query Массив параметров запроса.
     * Параметры:
     *  - 'dateFrom' => string Дата начала запрашиваемого периода. При пустом значении устанавливается текущая дата.
     *  - 'dateTo' => string Дата окончания запрашиваемого периода. При пустом значении устанавливается конец дня текущей даты.
     *  - 'serviceID' => string Идентификатор услуги. Устанавливает отбор по услуге.
     *  - 'eventID' => string Идентификатор мероприятия. Устанавливает отбор по мероприятию.
     *  - 'pointStartID' => string Отбор по точке отправления мероприятия.
     *  - 'seatsByGroups' => bool Запрашивать количество мест по группам (значительно увеличивает время обработки запроса). Применяйте ограничения.
     *  - 'getTicketType' => bool Запрашивать виды билетов (значительно увеличивает время обработки запроса).
     *  - 'email' => string Адрес электронной почты авторизованного пользователя. Необходим для ведения логов. При наличии обязателен для передачи.
     * @return array Массив с данными.
     *  Возвращаемые ключи:
     *  - 'events' => array Массив мероприятий.
     *    Каждый элемент массива содержит следующие ключи:
     *      - 'eventID' => string Идентификатор мероприятия.
     *      - 'eventName' => string Наименование мероприятия.
     *      - 'eventDateTime' => string Дата и время мероприятия. (Для типов услуг Date, Place может принимать значения: Дата и Пусто соответственно).
     *      - 'serviceID' => string Идентификатор услуги.
     *      - 'serviceName' => string Наименование услуги.
     *      - 'venueID' => string Идентификатор площадки.
     *      - 'venueName' => string Наименование площадки.
     *      - 'eventFreeSeating' => bool Свободная рассадка.
     *      - 'eventNoSeats' => bool Без мест.
     *      - 'ServiceGroupName' => string Наименование группы услуг.
     *      - 'pierID' => string Идентификатор места отправления (Места проведения).
     *      - 'pierName' => string Наименование места отправления (Места проведения).
     *      - 'eventDuration' => int Продолжительность мероприятия в минутах.
     *      - 'statusRequestBuyer' => bool Показывает необходимость запроса у покупателя статуса «Резидент», «Не резидент».
     *      - 'endPointID' => string ID конечной точки мероприятия.
     *      - 'endPointName' => string Наименование конечной точки мероприятия.
     *      - 'eventQuantityLimit' => bool Определяет необходимость запрос мест по мероприятию.
     *      - 'availableSeats' => int Доступное количество мест на момент запроса.
     *      - 'barcodeType' => string Тип ШК для услуги (EAN_13, ITF, CODE_128A, CODE_128B, CODE_128C, CODE_39, QR).
     *
     */
    public function getEvents(array $query = [])
    {
        return $this->apiClient->post('getEvents', $query);
    }

    /**
     * Возвращает массив групп мест площадки мероприятия. Имеет смысл запрашивать только если возвращаемый параметр eventNoSeats, метода getEvents, принимает значение false, то есть для площадки определена схема.
     *
     * @param array $query Массив параметров запроса.
     * Параметры:
     *  - 'eventID' => string Идентификатор мероприятия required
     *  - 'email' => string Адрес электронной почты авторизованного пользователя. Необходим для ведения логов. При наличии обязателен для передачи.
     * @return array Массив с данными.
     * Возвращаемые ключи:
     * - 'seatCategories' => array Массив групп мест.
     *   Каждый элемент массива содержит следующие ключи:
     *     - 'seatCategoryID' => string Идентификатор группы мест.
     *     - 'seatCategoryName' => string Наименование группы мест.
     *     - 'availableSeats' => int Свободные места в категории для мероприятия.
     */
    public function getSeatCategories(array $query = [])
    {
        return $this->apiClient->post('getSeatCategories', $query);
    }

    /**
     * Возвращает массив мест для мероприятия. Если мероприятие не доступно для продажи (отменено, закончена продажа через интернет) возвращает пустой массив. Метод предназначен для отрисовки карты площадки. Не использовать для подсчета доступного кол-ва мест к продаже.
     *
     * @param array $query Массив параметров запроса.
     * Параметры:
     *  - 'eventID' => string Идентификатор мероприятия required
     *  - 'seatCategoryID' => string Идентификатор категории мест площадки. Устанавливает отбор по категории мест
     *  - 'email' => string Адрес электронной почты авторизованного пользователя. Необходим для ведения логов. При наличии обязателен для передачи.
     * @return array Массив с данными.
     * Возвращаемые ключи:
     * - 'seats' => array Массив мест.
     *   Каждый элемент массива содержит следующие ключи:
     *     - 'seatID' => string Идентификатор посадочного места.
     *     - 'aliasSeat' => string Псевдоним места.
     *     - 'seatStatus' => string Статус посадочного места. Принимает значения: "Продано", "Бронь", "Выбрано" (выбрано другим пользователем), "Свободно".
     *     - 'seatCategoryName' => string Наименование категории места.
     *     - 'seatCategoryID' => string Идентификатор категории места.
     *     - 'numberOfTicketsPerSeat' => int Количество посадочных мест на выбранном месте (стол с несколькими посадочными местами).
     *     - 'availableSeats' => int Количество свободных мест для продажи.
     */
    public function getSeatsOnEvent(array $query = [])
    {
        return $this->apiClient->post('getSeatsOnEvent', $query);
    }

    /**
     * Возвращает массив видов билетов для мероприятия.
     *
     * @param array $query Массив параметров запроса.
     * Параметры:
     *  - 'eventID' => string Идентификатор мероприятия required
     *  - 'email' => string Адрес электронной почты авторизованного пользователя. Необходим для ведения логов. При наличии обязателен для передачи.
     * @return array Массив с данными.
     * Возвращаемые ключи:
     * - 'ticketTypes' => array
     *   Каждый элемент массива содержит следующие ключи:
     *     - 'ticketTypeID' => string
     *     - 'ticketTypeName' => string
     */
    public function getTicketType(array $query = [])
    {
        return $this->apiClient->post('getTicketType', $query);
    }

    /**
     * Возвращает массив цен на мероприятие. При передачи  ticketTypeID, seatCategoryID не относящихся к переданному мероприятию метод вернет пустой массив.
     *
     * @param array $query Массив параметров запроса.
     * Обязательные параметры:
     *  - 'eventID' => string Идентификатор мероприятия.
     *  - 'ticketTypeID' => string Идентификатор вида билета.
     *  Опциональные параметры:
     *  - 'seatCategoryID' => string Категория посадочных мест. Пусто если возвращаемый параметр eventNoSeats, метода getEvents, принимает значение true. В противном случае обязателен для передачи.
     *  - 'paymentTypeID' => string Идентификатор вида оплаты (обязателен для передачи для агентов).
     *  - 'resident' => bool Указывает на статус покупателя. true – гражданин РФ, false – иностранный гражданин. Пусто если возвращаемый параметр statusRequestBuyer, метода getEvents, принимает значение true.
     *  - 'email' => string Определяет тип покупателя (агент или физ. лицо). В зависимости от типа покупателя передаются соответствующие цены.
     * @return array Массив с данными.
     * Возвращаемые ключи:
     * - 'seatPrices' => array Массив цен.
     *   Каждый элемент массива содержит следующие ключи:
     *     - 'priceTypeID' => string Идентификатор типа цены.
     *     - 'priceTypeName' => string Наименование типа цены.
     *     - 'priceTypeValue' => int Цена в рублях.
     *     - 'priceTypeValueRetail' => int Цена в кассах.
     *     - 'priceTypeValueEKP' => int Цена ЕКП (только для внутреннего пользования). Не доступен для агентов.
     *     - 'ticketTypeName' => string Наименование вида билета.
     *     - 'seatCategoryName' => string Наименование категории места.
     *     - 'hasMenu' => bool Указывает на наличие пакетного предложения меню у цены.
     *     - 'hasEKP' => bool Возможность применения карты Петербуржца.
     *     - 'menu' => array Массив пакетов меню.
     *       Каждый элемент массива содержит следующие ключи:
     *         - 'menuID' => string Идентификатор пакета меню.
     *         - 'menuName' => string Наименование пакета меню.
     */
    public function getSeatPrices(array $query = [])
    {
        return $this->apiClient->post('getSeatPrices', $query);
    }

    /**
     * Возвращает массив цен на мероприятие. При передачи  ticketTypeID, seatCategoryID не относящихся к переданному мероприятию метод вернет пустой массив.
     *
     * @param array $query Массив параметров запроса.
     * Обязательные параметры:
     *  - 'sessionID' => string Идентификатор сессии пользователя на сайте. Нужен для того, чтобы найти и очистить выбранные ранее места.
     *  - 'orderID' => string Номер заказа.
     *  - 'email' => string Тип покупателя (агент или физическое лицо). В зависимости от типа покупателя передаются соответствующие цены.
     *  - 'Order' => array Массив заказа.
     *    Каждый элемент массива содержит следующие ключи:
     *      - 'eventID' => string Идентификатор мероприятия.
     *      - 'ticketTypeID' => string Идентификатор вида билета.
     *      - 'priceTypeID' => string Идентификатор типа цены.
     *      - 'quantityOfTickets' => int Количество билетов.
     *      - 'seatID' => string Идентификатор посадочного места (обязателен в определенных условиях, см. комментарий выше).
     *      - 'seatCategoryID' => string Идентификатор группы мест (обязателен в определенных условиях, см. комментарий выше).
     *      - 'menuID' => string Идентификатор типа меню (обязателен в определенных условиях, см. комментарий выше).
     *      - 'resident' => bool Указывает на статус покупателя (обязателен в определенных условиях, см. комментарий выше).
     *  - 'paymentTypeID' => string Идентификатор Вида оплаты (обязателен для агентов).
     *  - 'cardsSPB' => array Массив валидных карт ЕКП (обязателен в определенных условиях, см. комментарий выше).
     * @return array Массив с данными.
     * Возвращаемые ключи:
     * - 'isOrderRegistered' => bool Успешная продажа (true) или ошибка продажи (false).
     * - 'descriptionRegisteredOrder' => string Описание ответа о статусе регистрации.
     * - 'orderAmount' => int Сумма заказа.
     * - 'amountCertificate' => int Сумма оплаты сертификатом (для внутреннего сайта).
     * - 'amountToBePaid' => int Сумма к оплате (для внутреннего сайта).
     * - 'orderedSeats' => array Массив мест (массив структур).
     *   Каждая структура содержит следующие ключи:
     *     - 'eventID' => string Идентификатор мероприятия на сайте.
     *     - 'seatID' => string Идентификатор места.
     *     - 'seatAlias' => string Псевдоним места.
     *     - 'price' => string Цена места.
     *     - 'discount' => int Скидка.
     *     - 'priceFull' => int Полная цена.
     *     - 'quantityOfTickets' => int Количество билетов (возвращается в случае определенных условий, см. комментарий выше).
     *     - 'vat' => int Код налога НДС для ККТ (для внутреннего сайта).
     *     - 'taxMode' => int Код системы налогообложения ККТ (для внутреннего сайта).
     *     - 'barCodes' => array Массив штрихкодов.
     *       Каждый элемент массива содержит штрихкоды для соответствующего места (см. комментарий выше для деталей).
     */
    public function registerOrder(array $query = [])
    {
        return $this->apiClient->post('registerOrder', $query);
    }

    /**
     * Подтверждение оплаты, подтверждение заказа.
     *
     * @param array $query Массив параметров запроса.
     * Параметры:
     *  - 'orderID' => string Номер заказа required
     *  - 'orderConfirm' => boolean Подтверждение оплаты/подтверждение заказа. • true – подтверждение оплаты/ подтверждение заказа. • false – отказ от подтверждения оплаты/ подтверждение заказа
     *  - 'email' => string Адрес электронной почты авторизованного пользователя. Необходим для ведения логов. При наличии обязателен для передачи.
     * @return array Массив с данными.
     * Возвращаемые ключи:
     * - 'orderPaymentConfirmed' => boolean Результат выполнения: - true – заказ зафиксирован в системе - false - заказ не найден в системе
     * - 'descriptionOrderPayment' => string Описание результата выполнения.
     */
    public function confirmPayment(array $query = [])
    {
        return $this->apiClient->post('confirmPayment', $query);
    }

    /**
     * Возврат заказа.
     *
     * @param array $query Массив параметров запроса.
     * Параметры:
     *  - 'orderID' => string Номер заказа required
     *  - 'email' => string Адрес электронной почты авторизованного пользователя. Необходим для ведения логов. При наличии обязателен для передачи.
     * @return array Массив с данными.
     * Возвращаемые ключи:
     * - 'isOrderReturned' => boolean
     * - 'descriptionReturnedOrder' => string Описание результата выполнения.
     * - 'fineAmount' => int Сумма штрафа.
     */
    public function returnOrder(array $query = [])
    {
        return $this->apiClient->post('returnOrder', $query);
    }

    /**
     * Получить информацию о статусе заказа.
     *
     * @param array $query Массив параметров запроса.
     * Параметры:
     *  - 'orderID' => string Номер заказа required
     *  - 'email' => string Адрес электронной почты авторизованного пользователя. Необходим для ведения логов. При наличии обязателен для передачи.
     * @return array Массив с данными.
     * Возвращаемые ключи:
     * - 'result' => bool Результат выполнения запроса (true - успешно, false - ошибка).
     * - 'descriptionGetOrder' => string Описание результата выполнения запроса.
     * - 'sessionID' => string Идентификатор сессии пользователя на сайте.
     * - 'paymentTypeID' => string Идентификатор Вида оплаты (Депозит, Предоплата, Постоплата).
     * - 'status' => string Статус заказа (принимает значения: 'pending' - ожидает подтверждения, 'success' – подтверждён, 'reject' - отменен, 'expired' - истёк срок подтверждения).
     * - 'order' => array Массив с информацией о заказе.
     *   Ключи в массиве:
     *     - 'eventID' => string Идентификатор мероприятия.
     *     - 'seatID' => string Идентификатор посадочного места (пусто, если тип услуги DateTimeSeat+Свободная рассадка, DateTime).
     *     - 'priceTypeID' => string Идентификатор типа цены.
     *     - 'seatCategoryID' => string Идентификатор группы мест.
     *     - 'ticketTypeID' => string Идентификатор вида билета.
     *     - 'quantityOfTickets' => int Количество билетов.
     *     - 'resident' => bool Статус покупателя.
     */
    public function getOrder(array $query = [])
    {
        return $this->apiClient->post('getOrder', $query);
    }

    /**
     * Получить информацию о заказах. Период ограничен месяцем.
     *
     * @param array $query Массив параметров запроса.
     * Параметры:
     *  - 'orderID' => string Номер заказа required
     *  - 'email' => string Адрес электронной почты авторизованного пользователя. Необходим для ведения логов. При наличии обязателен для передачи.
     * @return array Массив с данными.
     *
     *{
     * "rezult": "true",
     * "descriptionGetCustomerOrder": "OK",
     * "orders": [
     * {
     * "orderID": "432423557",
     * "status": "return",
     * "amount": 2500,
     * "seats": [
     * {
     * "eventID": "000068152",
     * "eventName": "Парадный Петербург с выходом в Финский залив",
     * "eventDate": "2020-04-09T00:00:00",
     * "eventTime": "0001-01-01T16:00:00",
     * "seatID": "",
     * "seatCategoryID": "000000002",
     * "seatCategoryName": "Standard",
     * "priceTypeID": "000000001",
     * "priceTypeName": "Полный | Adult",
     * "quantityOfTickets": 5,
     * "ticketTypeID": "000000002",
     * "ticketTypeName": "Стандарт | Standard",
     * "resident": "",
     * "paymentTypeID": "000000002",
     * "amountSeats": 2500
     * }
     * ]
     * }
     * ]
     * }
     *
     */
    public function getCustomerOrders(array $query = []): array
    {
        return $this->apiClient->post('getCustomerOrders', $query);
    }

    public function bookingSeat(array $query = [])
    {
        return $this->apiClient->post('bookingSeat', $query);
    }


}

