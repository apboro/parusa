<?php

namespace App\SberbankAcquiring\HttpClient;

use App\SberbankAcquiring\Exception\NetworkException;
use InvalidArgumentException;
use RuntimeException;
use function curl_close;

class CurlClient implements HttpClientInterface
{
    /** @var resource CURL instance. */
    private $curl;

    /** @var array CURL options */
    private array $options;

    public function __construct(array $options = [])
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('Curl extension is not loaded.');
        }

        $this->options = $options;
    }

    /**
     * Get CURL instance.
     *
     * @return resource
     */
    private function getCurl()
    {
        if (null === $this->curl) {
            $this->curl = curl_init();
            curl_setopt_array($this->curl, $this->options);
            if ($cert = env('CURL_CERT_FILE')) {
                curl_setopt($this->curl, CURLOPT_CAINFO, storage_path($cert));
            }
            if (env('CURL_CERT_DISABLE')) {
                curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
            }
        }

        return $this->curl;
    }

    /**
     * Perform request.
     *
     * @param string $uri
     * @param string $method
     * @param array $headers
     * @param string $data
     *
     * @return array
     * @throws NetworkException
     */
    public function request(string $uri, string $method = HttpClientInterface::METHOD_GET, array $headers = [], string $data = ''): array
    {
        $options = [];

        if ($method === HttpClientInterface::METHOD_GET) {
            $options[CURLOPT_HTTPGET] = true;
            $options[CURLOPT_URL] = $uri . '?' . $data;

        } else if ($method === HttpClientInterface::METHOD_POST) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_URL] = $uri;
            $options[CURLOPT_POSTFIELDS] = $data;

        } else {
            throw new InvalidArgumentException(
                sprintf('An HTTP method "%s" is not supported. Use "%s" or "%s".', $method, HttpClientInterface::METHOD_GET, HttpClientInterface::METHOD_POST)
            );
        }

        foreach ($headers as $key => $value) {
            $options[CURLOPT_HTTPHEADER][] = "$key: $value";
        }

        $options[CURLOPT_RETURNTRANSFER] = true;

        $curl = $this->getCurl();
        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            $errorCode = curl_errno($curl);

            throw new NetworkException('Curl error: ' . $error, $errorCode);
        }

        $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        return [$httpCode, $response];
    }

    /**
     * Free CURL instance on destructing.
     */
    public function __destruct()
    {
        if (null !== $this->curl) {
            curl_close($this->curl);
        }
    }
}
