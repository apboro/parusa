<?php

namespace App\SberbankAcquiring;

use App\SberbankAcquiring\Exception\NetworkException;
use App\SberbankAcquiring\HttpClient\HttpClientInterface;
use InvalidArgumentException;

class Connection
{
    public const API_URI = 'https://securepayments.sberbank.ru';
    public const API_URI_TEST = 'https://3dsec.sberbank.ru';

    public const API_PREFIX_DEFAULT = '/payment/rest/';
    public const API_PREFIX_CREDIT = '/sbercredit/';
    public const API_PREFIX_APPLE = '/payment/applepay/';
    public const API_PREFIX_GOOGLE = '/payment/google/';
    public const API_PREFIX_SAMSUNG = '/payment/samsung/';

    public const ENDPOINT_DEFAULT = 'default';
    public const ENDPOINT_CREDIT = 'credit';
    public const ENDPOINT_APPLE_PAY = 'apple_pay';
    public const ENDPOINT_GOOGLE_PAY = 'google_pay';
    public const ENDPOINT_SAMSUNG_PAY = 'samsung_pay';
    public const ENDPOINT_SBP_QR = 'sbp_qr';

    /** @var string An API uri. */
    private string $apiUri;

    /** @var string Authentication username. */
    private string $userName;

    /** @var string Authentication password. */
    private string $password;

    /** @var string Authentication token. */
    private string $token;

    /** @var HttpClientInterface */
    private HttpClientInterface $httpClient;

    /** @var string An HTTP method. */
    private string $httpMethod;

    /** @var array API endpoints prefix. */
    private array $endpoints;

    /**
     * Constructor.
     *
     * @param array $auth
     * @param HttpClientInterface $client
     * @param bool $production
     * @param string $method
     * @param array $options
     *
     * @return  void
     */
    public function __construct(array $auth, HttpClientInterface $client, bool $production = false, string $method = HttpClientInterface::METHOD_POST, array $options = [])
    {
        $this->apiUri = $production ? self::API_URI : self::API_URI_TEST;

        if (isset($auth['token'])) {
            $this->token = $auth['token'];
        } else if (isset($auth['userName'], $auth['password'])) {
            $this->userName = $auth['userName'];
            $this->password = $auth['password'];
        } else {
            throw new InvalidArgumentException('You must provide authentication credentials: "userName" and "password", or "token".');
        }

        $allowed = [
            'prefixDefault',
            'prefixCredit',
            'prefixApple',
            'prefixGoogle',
            'prefixSamsung',
            'prefixSbpQr',
        ];

        $unknown = array_diff(array_keys($options), $allowed);

        if (!empty($unknown)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unknown options [%s]. Allowed options: [%s].',
                    implode(', ', $unknown), implode(', ', $allowed)
                )
            );
        }

        $this->endpoints = [
            self::ENDPOINT_DEFAULT => $options['prefixDefault'] ?? self::API_PREFIX_DEFAULT,
            self::ENDPOINT_CREDIT => $options['prefixCredit'] ?? self::API_PREFIX_CREDIT,
            self::ENDPOINT_APPLE_PAY => $options['prefixApple'] ?? self::API_PREFIX_APPLE,
            self::ENDPOINT_GOOGLE_PAY => $options['prefixGoogle'] ?? self::API_PREFIX_GOOGLE,
            self::ENDPOINT_SAMSUNG_PAY => $options['prefixSamsung'] ?? self::API_PREFIX_SAMSUNG,
            self::ENDPOINT_SBP_QR => $options['prefixSbpQr'] ?? null,
        ];

        if (!in_array($method, [HttpClientInterface::METHOD_GET, HttpClientInterface::METHOD_POST], true)) {
            throw new InvalidArgumentException(
                sprintf('An HTTP method "%s" is not supported. Use "%s" or "%s".', $method, HttpClientInterface::METHOD_GET, HttpClientInterface::METHOD_POST)
            );
        }

        $this->httpMethod = $method;

        $this->httpClient = $client;
    }


    /**
     * Send request and get response.
     *
     * @param Request $request
     *
     * @return Response A server's response
     * @throws NetworkException
     * @throws Exception\ResponseParsingException
     *
     */
    public function send(Request $request): Response
    {
        $uri = $this->apiUri . $this->endpoints[$request->endpoint()] . $request->method();
        $method = $this->httpMethod;
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';

        $data = $request->all();

        if (isset($this->token)) {
            $data['token'] = $this->token;
        } else {
            $data['userName'] = $this->userName;
            $data['password'] = $this->password;
        }

        $data = http_build_query($data, '', '&');

        [$code, $response] = $this->httpClient->request($uri, $method, $headers, $data);

        return new Response($code, $response);
    }
}
