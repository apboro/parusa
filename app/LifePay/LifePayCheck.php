<?php

namespace App\LifePay;

class LifePayCheck
{
    /**
     * Calc check code.
     *
     * @param array $params
     * @param string $url
     *
     * @return  string
     */
    public static function calc(array $params, string $url): string
    {
        ksort($params, SORT_LOCALE_STRING);

        $parsed = parse_url($url);
        $path = $parsed['path'];
        $host = $parsed['host'] ?? '';

        $data = implode("\n", ['POST', $host, $path, self::httpQueryRFC3986($params)]);

        $secret = env('LIFE_PAY_IE_SECRET');

        return base64_encode(hash_hmac("sha256", $data, $secret, true));
    }

    /**
     * Check signed data.
     *
     * @param array $params
     * @param string $url
     * @param string $method
     * @param string $check
     *
     * @return  bool
     */
    public static function check(array $params, string $url, string $method, string $check): bool
    {
        ksort($params, SORT_LOCALE_STRING);

        $parsed = parse_url($url);
        $path = $parsed['path'];
        $host = $parsed['host'] ?? '';

        $data = implode("\n", [strtoupper($method), $host, $path, self::httpQueryRFC3986($params)]);

        $secret = env('LIFE_PAY_IE_SECRET');

        $computed = base64_encode(hash_hmac("sha256", $data, $secret, true));

        return $check === $computed;
    }

    /**
     * Join params to query string.
     *
     * @param array $data
     * @param string $separator
     *
     * @return  string
     */
    protected static function httpQueryRFC3986(array $data, string $separator = '&'): string
    {
        $arguments = [];
        foreach ($data as $key => $argument) {
            $arguments[] = $key . '=' . rawurlencode($argument);
        }
        return implode($separator, $arguments);
    }
}
