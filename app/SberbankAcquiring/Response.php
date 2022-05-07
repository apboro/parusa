<?php

namespace App\SberbankAcquiring;

use App\SberbankAcquiring\Exception\ResponseParsingException;
use ArrayAccess;
use Exception;

class Response implements ArrayAccess
{
    /** @var int Success error code */
    protected const ACTION_SUCCESS = 0;

    /** @var int Http response code */
    protected int $code;

    /** @var array */
    protected array $payload;

    /** @var int Sber error code */
    protected int $errorCode;

    /** @var string Sber error message */
    protected string $errorMessage;

    /**
     * @param int $code
     * @param string $response
     *
     * @throws ResponseParsingException
     */
    public function __construct(int $code, string $response)
    {
        $this->code = $code;

        try {
            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            throw new ResponseParsingException($exception->getMessage());
        }

        // Server's response can contain an error code and an error message in different fields.
        if (isset($data['errorCode'])) {
            $this->errorCode = (int)$data['errorCode'];
        } else if (isset($data['ErrorCode'])) {
            $this->errorCode = (int)$data['ErrorCode'];
        } else if (isset($data['error']['code'])) {
            $this->errorCode = (int)$data['error']['code'];
        } else {
            $this->errorCode = self::ACTION_SUCCESS;
        }

        if (isset($data['errorMessage'])) {
            $this->errorMessage = $data['errorMessage'];
        } else if (isset($data['ErrorMessage'])) {
            $this->errorMessage = $data['ErrorMessage'];
        } else if (isset($data['error']['message'])) {
            $this->errorMessage = $data['error']['message'];
        } else if (isset($data['error']['description'])) {
            $this->errorMessage = $data['error']['description'];
        } else {
            $this->errorMessage = 'Unknown error.';
        }

        unset($data['errorCode'], $data['ErrorCode'], $data['errorMessage'], $data['ErrorMessage'], $data['error'], $data['success']);

        $this->payload = $data;
    }

    /**
     * Is response success.
     *
     * @return  bool
     */
    public function isSuccess(): bool
    {
        return $this->code === 200 && $this->errorCode === self::ACTION_SUCCESS;
    }

    /**
     * Get error message.
     *
     * @return  string|null
     */
    public function errorMessage(): ?string
    {
        return ($this->code === 200) ? ($this->errorMessage ?? null) : 'Http connection error';
    }

    /**
     * Get response payload
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
