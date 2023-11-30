<?php

namespace App\Exceptions\Tickets;

use Exception;
use Illuminate\Http\JsonResponse;

class TicketsValidationException extends Exception
{
    protected array $customErrors = [];
    protected array $payload = [];

    public function __construct(array $errors = [], array $payload = [])
    {
        parent::__construct();

        $this->customErrors = $errors;
        $this->payload = $payload;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Не все поля корректно заполнены',
            'status' => 'Validation error',
            'code' => 422,
            'errors' => $this->customErrors,
            'payload' => $this->payload,
        ], 422);
    }

}
