<?php
declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationApiException extends ApiException
{
    public function __construct(
        ConstraintViolationListInterface $validationErrors
    ) {
        parent::__construct(
            $message = 'Bad Request',
            $shortCode = 'bad_request',
            $httpCode = 400,
            $this->generateData($validationErrors)
        );
    }

    private function generateData(ConstraintViolationListInterface $validationErrors): array
    {
        $data = [];

        foreach ($validationErrors as $key => $validationError) {
            $messages = explode('|', $validationError->getMessage());
            $data[] = str_replace(['...', '..'], '.', $messages[1] ?? $messages[0]);
        }

        return $data;
    }
}
