<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Contact;
use App\Entity\ShareRequest;
use App\Entity\User;
use App\Exception\BadRequestException;
use App\Exception\DuplicateException;
use App\Exception\ValidationApiException;
use App\Repository\ContactRepository;
use App\Repository\ShareRequestRepository;
use App\Repository\UserRepository;
use App\Validator\ContactValidator;
use App\Validator\ShareRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use JsonException;

class ShareRequestManager
{
    private ShareRequestValidator $shareRequestValidator;
    private ShareRequestRepository $shareRequestRepository;
    private UserRepository $userRepository;
    private ContactRepository $contactRepository;
    private ContactValidator $contactValidator;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ShareRequestValidator $shareRequestValidator,
        ShareRequestRepository $shareRequestRepository,
        UserRepository $userRepository,
        ContactRepository $contactRepository,
        ContactValidator $contactValidator,
        EntityManagerInterface $entityManager
    ) {
        $this->shareRequestValidator = $shareRequestValidator;
        $this->shareRequestRepository = $shareRequestRepository;
        $this->userRepository = $userRepository;
        $this->contactRepository = $contactRepository;
        $this->contactValidator = $contactValidator;
        $this->entityManager = $entityManager;
    }

    /**
     * @param User|UserInterface $sender
     * @param Request $request
     *
     * @return ShareRequest
     * @throws BadRequestException
     * @throws DuplicateException
     * @throws JsonException
     * @throws ValidationApiException
     */
    public function createShareRequest(User $sender, Request $request): ShareRequest
    {
        $shareRequestData = $this->getRequestData($request);

        if ($sender->getPhoneNr() === (int)$shareRequestData['receiver']) {
            throw new BadRequestException('Can\'t share contact with yourself.');
        }

        $receiver = $this->userRepository->findOneByPhoneNr($shareRequestData['receiver']);

        if ($receiver === null) {
            throw new BadRequestException('User with provided receiver phone number not found.');
        }

        $contact = $this->contactRepository->findOneByUserAndId($sender, $shareRequestData['contact_id']);

        if ($contact === null) {
            throw new BadRequestException('Contact not found.');
        }

        $shareRequestExists = $this->shareRequestRepository->findOneBySenderAndReceiverAndPhoneNr(
            $sender,
            $receiver,
            $contact->getPhoneNr()
        );

        if ($shareRequestExists !== null) {
            throw new DuplicateException('Share request already exists.');
        }

        $shareRequest = (new ShareRequest())
            ->setSender($sender)
            ->setReceiver($receiver)
            ->setName($shareRequestData['name'] ?? $contact->getName())
            ->setPhoneNr($contact->getPhoneNr())
            ->setStatus(ShareRequest::STATUS_CREATED)
        ;

        $this->entityManager->persist($shareRequest);
        $this->entityManager->flush();

        return $shareRequest;
    }

    public function acceptShareRequest(ShareRequest $shareRequest, Request $request): Contact
    {
        if (!empty($request->getContent()) && strlen($request->getContent()) > 10) {
            $requestContent = json_decode(
                $request->getContent(),
                true,
                JSON_PARTIAL_OUTPUT_ON_ERROR,
                JSON_THROW_ON_ERROR
            );

            if (isset($requestContent['name'])) {
                $validation = $this->contactValidator->validateName($requestContent['name']);

                if ($validation->count() !== 0) {
                    throw new ValidationApiException($validation);
                }
            }
        }

        $contact = $this->contactRepository->findOneByUserAndPhoneNr(
            $shareRequest->getReceiver(),
            $shareRequest->getPhoneNr()
        );

        if ($contact !== null) {
            $contact->setName($requestContent['name'] ?? $shareRequest->getName());
        } else {
            $contact = (new Contact())
                ->setUser($shareRequest->getReceiver())
                ->setName($requestContent['name'] ?? $shareRequest->getName())
                ->setPhoneNr($shareRequest->getPhoneNr())
            ;
        }

        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        $this->changeShareRequestStatus($shareRequest, ShareRequest::STATUS_ACCEPTED);

        return $contact;
    }

    public function changeShareRequestStatus(ShareRequest $shareRequest, string $status): void
    {
        if (!in_array($status, ShareRequest::STATUS_LIST, true)) {
            throw new BadRequestException('Incorrect status.');
        }

        $shareRequest->setStatus($status);

        $this->entityManager->persist($shareRequest);
        $this->entityManager->flush();
    }

    private function getRequestData(Request $request): array
    {
        $shareRequestData = json_decode(
            $request->getContent(),
            true,
            JSON_PARTIAL_OUTPUT_ON_ERROR,
            JSON_THROW_ON_ERROR
        );

        $validation = $this->contactValidator->validatePhoneNr($shareRequestData['receiver'], 'receiver');

        $validation->addAll(
            $this->shareRequestValidator->validateContactId($shareRequestData['contact_id'] ?? null)
        );

        if (isset($shareRequestData['name'])) {
            $validation->addAll(
                $this->contactValidator->validateName($shareRequestData['name'])
            );
        }

        if ($validation->count() !== 0) {
            throw new ValidationApiException($validation);
        }

        return $shareRequestData;
    }
}
