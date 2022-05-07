<?php

namespace App\SberbankAcquiring\Exception;

class BadResponseException extends SberbankAcquiringException
{
    /** @var string Response instance. */
    private string $response;

    /**
     * Get response instance.
     *
     * @return string|null
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }

    /**
     * Attach response instance to exception.
     *
     * @param string $response
     *
     * @return void
     */
    public function setResponse(string $response): void
    {
        $this->response = $response;
    }
}
