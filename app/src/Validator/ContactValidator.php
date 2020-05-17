<?php
declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ContactValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validateContactData(array $contact): ConstraintViolationListInterface
    {
        $contactValidation = (new ConstraintViolationList());
        $contactValidation->addAll($this->validatePhoneNr($contact['phone_nr'] ?? null));
        $contactValidation->addAll($this->validateName($contact['name'] ?? null));

        return $contactValidation;
    }

    public function validatePhoneNr($phoneNr = null, string $fieldName = 'phone_nr'): ConstraintViolationListInterface
    {
        $constraint = [
            new Constraints\NotNull([
                'message' => 'Field \'' . $fieldName . '\' is required.',
            ]),
            new Constraints\Type([
                'int',
                'message' => 'Field \'' . $fieldName . '\' should be of type integer.',
            ]),
            new Constraints\Length([
                'min' => 9,
                'max' => 15,
                'minMessage' => 'Field \'' . $fieldName . '\' is too short. Its length should vary between 9 and 15.',
                'maxMessage' => 'Field \'' . $fieldName . '\' is too long. Its length should vary between 9 and 15.',
            ]),
        ];

        return $this->validator->validate($phoneNr, $constraint);
    }

    public function validateName($name = null, string $fieldName = 'name'): ConstraintViolationListInterface
    {
        $constraint = [
            new Constraints\NotNull([
                'message' => 'Field \'' . $fieldName . '\' is required.',
            ]),
            new Constraints\Type([
                'string',
                'message' => 'Field \'' . $fieldName . '\' should be of type string.',
            ]),
            new Constraints\Length([
                'min' => 5,
                'max' => 55,
                'minMessage' => 'Field \'' . $fieldName . '\' is too short. Its length should vary between 5 and 55.',
                'maxMessage' => 'Field \'' . $fieldName . '\' is too long. Its length should vary between 5 and 55.',
            ]),
        ];

        return $this->validator->validate($name, $constraint);
    }
}
