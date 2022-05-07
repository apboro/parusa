<?php

namespace App\SberbankAcquiring;

use ArrayAccess;

class Request implements ArrayAccess
{
    /** @var array Request payload */
    protected array $payload = [];

    /** @var string Request endpoint */
    protected string $endpoint;

    /** @var string Request method */
    protected string $method;

    public function __construct(string $endpoint, string $method, array $payload = [])
    {
        $this->endpoint = $endpoint;
        $this->method = $method;
        $this->payload = $payload;
    }

    /**
     * Get request endpoint
     *
     * @return string
     */
    public function endpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * Get request method
     *
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Get request payload
     *
     * @return array
     */
    public function all(): array
    {
        return $this->payload;
    }

    /**
     * @param $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->payload);
    }

    /**
     * @param $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->payload[$offset];
    }

    /**
     * @param $offset
     * @param $value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->payload[$offset] = $value;
    }

    /**
     * @param $offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->payload[$offset]);
    }
}
