<?php
declare(strict_types=1);

namespace App\Exception;

use Exception;

class ApiException extends Exception
{
    private string $shortCode;
    private int $httpCode;
    private array $data;

    public function __construct(string $message, string $shortCode, int $httpCode, array $data = [])
    {
        parent::__construct($message, $code = 0, $previous = null);

        $this->shortCode = $shortCode;
        $this->httpCode = $httpCode;
        $this->data = $data;
    }

    public function getShortCode(): string
    {
        return $this->shortCode;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
