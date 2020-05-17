<?php
declare(strict_types=1);

namespace App\Mapper;

use App\Entity\Contact;

class ContactMapper
{
    public function mapContactsToArray(array $contacts): array
    {
        $resolvedContacts = [];

        foreach ($contacts as $contact) {
            $resolvedContacts[] = $this->mapContactToArray($contact);
        }

        return $resolvedContacts;
    }

    public function mapContactToArray(Contact $contact): array
    {
        return [
            'id' => $contact->getId(),
            'name' => $contact->getName(),
            'phone_nr' => $contact->getPhoneNr(),
        ];
    }
}
