<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Contact;
use App\Entity\User;
use App\Exception\DuplicateException;
use App\Exception\ValidationApiException;
use App\Repository\ContactRepository;
use App\Validator\ContactValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use JsonException;

class ContactManager
{
    private ContactRepository $contactRepository;
    private ContactValidator $contactValidator;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ContactRepository $contactRepository,
        ContactValidator $contactValidator,
        EntityManagerInterface $entityManager
    ) {
        $this->contactRepository = $contactRepository;
        $this->contactValidator = $contactValidator;
        $this->entityManager = $entityManager;
    }

    /**
     * @param User|UserInterface $user
     * @param string|null $searchPhrase
     *
     * @return Contact[]
     */
    public function getUserContacts(User $user, ?string $searchPhrase): array
    {
        if ($searchPhrase === null) {
            return $this->contactRepository->findByUser($user);
        }

        return $this->contactRepository->findByUserAndSearchValue($user, $searchPhrase);
    }

    /**
     * @param User|UserInterface $user
     * @param Request $request
     *
     * @return Contact
     * @throws DuplicateException
     * @throws JsonException
     * @throws ValidationApiException
     */
    public function createContact(User $user, Request $request): Contact
    {
        $contactData = $this->getContactData($request);

        $phoneNrExists = $this->contactRepository->findOneByUserAndPhoneNr($user, $contactData['phone_nr']);

        if ($phoneNrExists !== null) {
            throw new DuplicateException('Phone number already exists.');
        }

        $contact = (new Contact())
            ->setUser($user)
            ->setName($contactData['name'])
            ->setPhoneNr((int)$contactData['phone_nr'])
        ;

        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $contact;
    }

    public function updateContact(User $user, Contact $contact, Request $request): Contact
    {
        $contactData = $this->getContactData($request);

        $phoneNrExists = $this->contactRepository->findOneByUserAndPhoneNr(
            $user,
            $contactData['phone_nr'],
            $contact->getId()
        );

        if ($phoneNrExists !== null) {
            throw new DuplicateException('Another contact with same phone number already exists.');
        }

        $contact
            ->setName($contactData['name'])
            ->setPhoneNr((int)$contactData['phone_nr'])
        ;

        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $contact;
    }

    public function patchContact(User $user, Contact $contact, Request $request): Contact
    {
        $contactData = json_decode(
            $request->getContent(),
            true,
            JSON_PARTIAL_OUTPUT_ON_ERROR,
            JSON_THROW_ON_ERROR
        );

        if (isset($contactData['name'], $contactData['phone_nr'])) {
            return $this->updateContact($user, $contact, $request);
        }

        if (isset($contactData['name'])) {
            $validation = $this->contactValidator->validateName($contactData['name']);

            if ($validation->count() !== 0) {
                throw new ValidationApiException($validation);
            }

            $contact->setName($contactData['name']);
        }

        if (isset($contactData['phone_nr'])) {
            $validation = $this->contactValidator->validatePhoneNr($contactData['phone_nr']);

            if ($validation->count() !== 0) {
                throw new ValidationApiException($validation);
            }

            $contact->setPhoneNr($contactData['phone_nr']);
        }

        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $contact;
    }

    public function deleteContact(Contact $contact): void
    {
        $this->entityManager->remove($contact);
        $this->entityManager->flush();
    }

    private function getContactData(Request $request): array
    {
        $contactData = json_decode(
            $request->getContent(),
            true,
            JSON_PARTIAL_OUTPUT_ON_ERROR,
            JSON_THROW_ON_ERROR
        );

        $validation = $this->contactValidator->validateContactData($contactData);

        if ($validation->count() !== 0) {
            throw new ValidationApiException($validation);
        }

        return $contactData;
    }
}
