<?php
declare(strict_types=1);

namespace App\Exception;

class BadRequestApiException extends ApiException
{
    public function __construct(
        string $message = 'Bad Request',
        string $shortCode = 'bad_request',
        int $httpCode = 400,
        array $data = []
    ) {
        parent::__construct($message, $shortCode, $httpCode, $data);
    }
}
