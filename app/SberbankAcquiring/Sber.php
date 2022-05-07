<?php

namespace App\SberbankAcquiring;

use App\SberbankAcquiring\Exception\ResponseParsingException;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

/**
 * Client for Sberbank acquiring REST API.
 *
 * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:start#%D0%B8%D0%BD%D1%82%D0%B5%D1%80%D1%84%D0%B5%D0%B9%D1%81_rest
 */
class Sber
{
    /** @var Options Acquiring options */
    protected Options $options;

    /** @var Connection Connection instance */
    protected Connection $connection;

    /**
     * Constructor.
     *
     * @param Connection $connection
     * @param Options $options
     */
    public function __construct(Connection $connection, Options $options)
    {
        if (!extension_loaded('json')) {
            throw new RuntimeException('JSON extension is not loaded.');
        }

        $this->connection = $connection;
        $this->options = $options;
    }

    /**
     * Register a new order.
     *
     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:register
     *
     * @param int|string $orderId An order identifier
     * @param int $amount An order amount
     * @param int $lifeTime
     * @param string $returnUrl An url for redirecting a user after successfully order handling
     * @param array $data Additional data
     *
     * @return Response A server's response
     * @throws Exception\NetworkException
     * @throws JsonException
     * @throws ResponseParsingException
     */
    public function registerOrder($orderId, int $amount, int $lifeTime, string $returnUrl, array $data = []): Response
    {
        return $this->doRegisterOrder($orderId, $amount, $lifeTime, $returnUrl, $data, Connection::ENDPOINT_DEFAULT, 'register.do');
    }

//    /**
//     * Register a new order using a 2-step payment process.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:registerpreauth
//     *
//     * @param int|string $orderId An order identifier
//     * @param int $amount An order amount
//     * @param string $returnUrl An url for redirecting a user after successfull order handling
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function registerOrderPreAuth($orderId, int $amount, string $returnUrl, array $data = []): array
//    {
//        return $this->doRegisterOrder($orderId, $amount, $returnUrl, $data, $this->prefixDefault . 'registerPreAuth.do');
//    }

//    /**
//     * Register a new credit order.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:register_cart_credit
//     *
//     * @param int|string $orderId An order identifier
//     * @param int $amount An order amount
//     * @param string $returnUrl An url for redirecting a user after successfull order handling
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function registerCreditOrder($orderId, int $amount, string $returnUrl, array $data = []): array
//    {
//        return $this->doRegisterOrder($orderId, $amount, $returnUrl, $data, $this->prefixCredit . 'register.do');
//    }

//    /**
//     * Register a new credit order using a 2-step payment process.
//     *
//     * @param int|string $orderId An order identifier
//     * @param int $amount An order amount
//     * @param string $returnUrl An url for redirecting a user after successfully order handling
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function registerCreditOrderPreAuth($orderId, int $amount, string $returnUrl, array $data = []): array
//    {
//        return $this->doRegisterOrder($orderId, $amount, $returnUrl, $data, $this->prefixCredit . 'registerPreAuth.do');
//    }

    /**
     * Generic order register command.
     *
     * @param $orderId
     * @param int $amount
     * @param int $lifeTime
     * @param string $returnUrl
     * @param array $data
     * @param string $endpoint
     * @param string $method
     *
     * @return Response
     * @throws Exception\NetworkException
     * @throws JsonException
     * @throws ResponseParsingException
     */
    private function doRegisterOrder($orderId, int $amount, int $lifeTime, string $returnUrl, array $data, string $endpoint, string $method): Response
    {
        $data['orderNumber'] = $orderId;
        $data['amount'] = $amount;
        $data['returnUrl'] = $returnUrl;

        if ($this->options->currency()) {
            $data['currency'] = $this->options->currency();
        }

        if ($this->options->language()) {
            $data['language'] = $this->options->language();
        }

        if (isset($data['jsonParams'])) {
            if (!is_array($data['jsonParams'])) {
                throw new InvalidArgumentException('The "jsonParams" parameter must be an array.');
            }
            $data['jsonParams'] = json_encode($data['jsonParams'], JSON_THROW_ON_ERROR);
        }

        if ($lifeTime !== 0) {
            $data['sessionTimeoutSecs'] = $lifeTime;
        }

        if (isset($data['orderBundle']) && is_array($data['orderBundle'])) {
            $data['orderBundle'] = json_encode($data['orderBundle'], JSON_THROW_ON_ERROR);
        }

        $request = new Request($endpoint, $method, $data);

        return $this->connection->send($request);
    }

//    /**
//     * Deposit an existing order.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:deposit
//     *
//     * @param int|string $orderId An order identifier
//     * @param int $amount An order amount
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function deposit($orderId, int $amount, array $data = []): array
//    {
//        $data['orderId'] = $orderId;
//        $data['amount'] = $amount;
//
//        return $this->execute($this->prefixDefault . 'deposit.do', $data);
//    }

//    /**
//     * Reverse an existing order.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:reverse
//     *
//     * @param int|string $orderId An order identifier
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function reverseOrder($orderId, array $data = []): array
//    {
//        $data['orderId'] = $orderId;
//
//        return $this->execute($this->prefixDefault . 'reverse.do', $data);
//    }

//    /**
//     * Refund an existing order.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:refund
//     *
//     * @param int|string $orderId An order identifier
//     * @param int $amount An amount to refund
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function refundOrder($orderId, int $amount, array $data = []): array
//    {
//        $data['orderId'] = $orderId;
//        $data['amount'] = $amount;
//
//        return $this->execute($this->prefixDefault . 'refund.do', $data);
//    }

    /**
     * Get an existing order's status by Sberbank's gateway identifier.
     *
     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:getorderstatusextended
     *
     * @param int|string $orderId A Sberbank's gateway order identifier
     *
     * @return Response A server's response
     * @throws Exception\NetworkException
     * @throws ResponseParsingException
     */
    public function getOrderStatus($orderId): Response
    {
        $data['orderId'] = $orderId;

        if ($this->options->language()) {
            $data['language'] = $this->options->language();
        }

        $request = new Request(Connection::ENDPOINT_DEFAULT, 'getOrderStatusExtended.do', $data);

        return $this->connection->send($request);
    }

//    /**
//     * Get an existing order's status by own identifier.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:getorderstatusextended
//     *
//     * @param int|string $orderId An own order identifier
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function getOrderStatusByOwnId($orderId, array $data = []): array
//    {
//        $data['orderNumber'] = $orderId;
//
//        return $this->execute($this->prefixDefault . 'getOrderStatusExtended.do', $data);
//    }

//    /**
//     * Verify card enrollment in the 3DS.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:verifyEnrollment
//     *
//     * @param string $pan A primary account number
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function verifyEnrollment(string $pan, array $data = []): array
//    {
//        $data['pan'] = $pan;
//
//        return $this->execute($this->prefixDefault . 'verifyEnrollment.do', $data);
//    }

//    /**
//     * Update an SSL card list.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:updateSSLCardList
//     *
//     * @param int|string $orderId An order identifier
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function updateSSLCardList($orderId, array $data = []): array
//    {
//        $data['mdorder'] = $orderId;
//
//        return $this->execute($this->prefixDefault . 'updateSSLCardList.do', $data);
//    }

//    /**
//     * Get last orders for merchants.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:getLastOrdersForMerchants
//     *
//     * @param \DateTimeInterface $from A begining date of a period
//     * @param \DateTimeInterface|null $to An ending date of a period
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function getLastOrdersForMerchants(\DateTimeInterface $from, \DateTimeInterface $to = null, array $data = []): array
//    {
//        if (null === $to) {
//            $to = new \DateTime();
//        }
//
//        if ($from >= $to) {
//            throw new \InvalidArgumentException('A "from" parameter must be less than "to" parameter.');
//        }
//
//        $allowedStatuses = [
//            OrderStatus::CREATED,
//            OrderStatus::APPROVED,
//            OrderStatus::DEPOSITED,
//            OrderStatus::REVERSED,
//            OrderStatus::DECLINED,
//            OrderStatus::REFUNDED,
//        ];
//
//        if (isset($data['transactionStates'])) {
//            if (!is_array($data['transactionStates'])) {
//                throw new \InvalidArgumentException('A "transactionStates" parameter must be an array.');
//            }
//
//            if (empty($data['transactionStates'])) {
//                throw new \InvalidArgumentException('A "transactionStates" parameter cannot be empty.');
//            } else if (0 < count(array_diff($data['transactionStates'], $allowedStatuses))) {
//                throw new \InvalidArgumentException('A "transactionStates" parameter contains not allowed values.');
//            }
//        } else {
//            $data['transactionStates'] = $allowedStatuses;
//        }
//
//        $data['transactionStates'] = array_map('Voronkovich\SberbankAcquiring\OrderStatus::statusToString', $data['transactionStates']);
//
//        if (isset($data['merchants'])) {
//            if (!is_array($data['merchants'])) {
//                throw new \InvalidArgumentException('A "merchants" parameter must be an array.');
//            }
//        } else {
//            $data['merchants'] = [];
//        }
//
//        $data['from'] = $from->format($this->dateFormat);
//        $data['to'] = $to->format($this->dateFormat);
//        $data['transactionStates'] = implode(',', array_unique($data['transactionStates']));
//        $data['merchants'] = implode(',', array_unique($data['merchants']));
//
//        return $this->execute($this->prefixDefault . 'getLastOrdersForMerchants.do', $data);
//    }

//    /**
//     * Payment order binding.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:paymentOrderBinding
//     *
//     * @param int|string $orderId An order identifier
//     * @param int|string $bindingId A binding identifier
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function paymentOrderBinding($orderId, $bindingId, array $data = []): array
//    {
//        $data['mdOrder'] = $orderId;
//        $data['bindingId'] = $bindingId;
//
//        return $this->execute($this->prefixDefault . 'paymentOrderBinding.do', $data);
//    }

//    /**
//     * Activate a binding.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:bindCard
//     *
//     * @param int|string $bindingId A binding identifier
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function bindCard($bindingId, array $data = []): array
//    {
//        $data['bindingId'] = $bindingId;
//
//        return $this->execute($this->prefixDefault . 'bindCard.do', $data);
//    }

//    /**
//     * Deactivate a binding.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:unBindCard
//     *
//     * @param int|string $bindingId A binding identifier
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function unBindCard($bindingId, array $data = []): array
//    {
//        $data['bindingId'] = $bindingId;
//
//        return $this->execute($this->prefixDefault . 'unBindCard.do', $data);
//    }

//    /**
//     * Extend a binding.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:extendBinding
//     *
//     * @param int|string $bindingId A binding identifier
//     * @param \DateTimeInterface $newExprity A new expiration date
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function extendBinding($bindingId, \DateTimeInterface $newExpiry, array $data = []): array
//    {
//        $data['bindingId'] = $bindingId;
//        $data['newExpiry'] = $newExpiry->format('Ym');
//
//        return $this->execute($this->prefixDefault . 'extendBinding.do', $data);
//    }

//    /**
//     * Get bindings.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:getBindings
//     *
//     * @param int|string $clientId A binding identifier
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function getBindings($clientId, array $data = []): array
//    {
//        $data['clientId'] = $clientId;
//
//        return $this->execute($this->prefixDefault . 'getBindings.do', $data);
//    }

//    /**
//     * Get a receipt status.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:getreceiptstatus
//     *
//     * @param array $data A data
//     *
//     * @return array A server's response
//     */
//    public function getReceiptStatus(array $data): array
//    {
//        return $this->execute($this->prefixDefault . 'getReceiptStatus.do', $data);
//    }

//    /**
//     * Pay with Apple Pay.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:payment_applepay
//     *
//     * @param int|string $orderNumber Order identifier
//     * @param string $merchant Merchant
//     * @param string $paymentToken Payment token
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function payWithApplePay($orderNumber, string $merchant, string $paymentToken, array $data = []): array
//    {
//        $data['orderNumber'] = $orderNumber;
//        $data['merchant'] = $merchant;
//        $data['paymentToken'] = $paymentToken;
//
//        return $this->execute($this->prefixApple . 'payment.do', $data);
//    }

//    /**
//     * Pay with Google Pay.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:payment_googlepay
//     *
//     * @param int|string $orderNumber Order identifier
//     * @param string $merchant Merchant
//     * @param string $paymentToken Payment token
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function payWithGooglePay($orderNumber, string $merchant, string $paymentToken, array $data = []): array
//    {
//        $data['orderNumber'] = $orderNumber;
//        $data['merchant'] = $merchant;
//        $data['paymentToken'] = $paymentToken;
//
//        return $this->execute($this->prefixGoogle . 'payment.do', $data);
//    }

//    /**
//     * Pay with Samsung Pay.
//     *
//     * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:payment_samsungpay
//     *
//     * @param int|string $orderNumber Order identifier
//     * @param string $merchant Merchant
//     * @param string $paymentToken Payment token
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function payWithSamsungPay($orderNumber, string $merchant, string $paymentToken, array $data = []): array
//    {
//        $data['orderNumber'] = $orderNumber;
//        $data['merchant'] = $merchant;
//        $data['paymentToken'] = $paymentToken;
//
//        return $this->execute($this->prefixSamsung . 'payment.do', $data);
//    }

//    /**
//     * Get QR code for payment through SBP.
//     *
//     * @param int|string $orderId An order identifier
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function getSbpDynamicQr($orderId, array $data = []): array
//    {
//        if (empty($this->prefixSbpQr)) {
//            throw new RuntimeException('The "prefixSbpQr" option is unspecified.');
//        }
//
//        $data['mdOrder'] = $orderId;
//
//        return $this->execute($this->prefixSbpQr . 'dynamic/get.do', $data);
//    }

//    /**
//     * Get QR code status.
//     *
//     * @param int|string $orderId An order identifier
//     * @param string $qrId A QR code identifier
//     * @param array $data Additional data
//     *
//     * @return array A server's response
//     */
//    public function getSbpQrStatus($orderId, string $qrId, array $data = []): array
//    {
//        if (empty($this->prefixSbpQr)) {
//            throw new RuntimeException('The "prefixSbpQr" option is unspecified.');
//        }
//
//        $data['mdOrder'] = $orderId;
//        $data['qrId'] = $qrId;
//
//        return $this->execute($this->prefixSbpQr . 'status.do', $data);
//    }
}
