<?php
declare(strict_types=1);

namespace App\Exception;

class NotFoundApiException extends ApiException
{
    public function __construct(
        string $message = 'Not Found',
        string $shortCode = 'not_found',
        int $httpCode = 404,
        array $data = []
    ) {
        parent::__construct($message, $shortCode, $httpCode, $data);
    }
}
