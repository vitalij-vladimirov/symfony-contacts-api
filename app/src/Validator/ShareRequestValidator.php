<?php
declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ShareRequestValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validateContactId(
        $contactId = null,
        string $fieldName = 'contact_id'
    ): ConstraintViolationListInterface {
        $constraint = [
            new Constraints\NotNull([
                'message' => 'Field \'' . $fieldName . '\' is required.',
            ]),
            new Constraints\Type([
                'int',
                'message' => 'Field \'' . $fieldName . '\' should be of type string.',
            ]),
            new Constraints\GreaterThanOrEqual([
                'value' => 1,
                'message' => 'Field \'' . $fieldName . '\' should be greater than or equal to  1.',
            ]),
        ];

        return $this->validator->validate($contactId, $constraint);
    }
}
