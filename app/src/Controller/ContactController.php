<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\ErrorCode\CommonErrorCode;
use App\ErrorCode\ContactErrorCode;
use App\Exception\BadRequestApiException;
use App\Exception\DuplicateException;
use App\Exception\NotFoundApiException;
use App\Repository\ContactRepository;
use App\Mapper\ContactMapper;
use App\Service\ContactManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\ValidationApiException;
use JsonException;

class ContactController extends AbstractController
{
    private ContactManager $contactManager;
    private ContactMapper $contactMapper;
    private ContactRepository $contactRepository;

    public function __construct(
        ContactManager $contactManager,
        ContactMapper $contactMapper,
        ContactRepository $contactRepository
    ) {
        $this->contactManager = $contactManager;
        $this->contactMapper = $contactMapper;
        $this->contactRepository = $contactRepository;
    }

    /**
     * Get User contacts
     * @Route(
     *     "/api/contacts",
     *     name="get_contacts",
     *     condition="context.getMethod() in ['GET']"
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getContacts(Request $request): JsonResponse
    {
        $contacts = $this->contactManager->getUserContacts(
            $this->getUser(),
            $request->query->get('search')
        );

        return new JsonResponse([
            'total' => count($contacts),
            'data' => $this->contactMapper->mapContactsToArray($contacts)
        ]);
    }

    /**
     * Get User contact by id
     * @Route(
     *     "/api/contacts/{id}",
     *     name="get_contact_by_id",
     *     condition="context.getMethod() in ['GET']"
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws NotFoundApiException
     */
    public function getContact(int $id): JsonResponse
    {
        $contact = $this->contactRepository->findOneByUserAndId($this->getUser(), $id);

        if ($contact === null) {
            throw new NotFoundApiException(
                'Contact not found.',
                ContactErrorCode::CONTACT_NOT_FOUND
            );
        }

        return new JsonResponse(
            $this->contactMapper->mapContactToArray($contact)
        );
    }

    /**
     * Create User contact
     * @Route(
     *     "/api/contacts",
     *     name="create_contact",
     *     condition="context.getMethod() in ['POST']"
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws BadRequestApiException
     * @throws ValidationApiException
     */
    public function createContact(Request $request): JsonResponse
    {
        try {
            $contact = $this->contactManager->createContact($this->getUser(), $request);
        } catch (JsonException $exception) {
            throw new BadRequestApiException(
                'Wrong data format.',
                CommonErrorCode::SYNTAX_ERROR
            );
        } catch (DuplicateException $exception) {
            throw new BadRequestApiException(
                $exception->getMessage(),
                CommonErrorCode::DUPLICATE
            );
        }

        return new JsonResponse(
            $this->contactMapper->mapContactToArray($contact)
        );
    }

    /**
     * Update User contact
     * @Route(
     *     "/api/contacts/{id}",
     *     name="update_contact",
     *     condition="context.getMethod() in ['PUT']"
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws BadRequestApiException
     * @throws NotFoundApiException
     */
    public function updateContact(int $id, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $contact = $this->contactRepository->findOneByUserAndId($user, $id);

        if ($contact === null) {
            throw new NotFoundApiException(
                'Contact not found.',
                ContactErrorCode::CONTACT_NOT_FOUND
            );
        }

        try {
            $contact = $this->contactManager->updateContact($user, $contact, $request);
        } catch (JsonException $exception) {
            throw new BadRequestApiException(
                'Wrong data format.',
                CommonErrorCode::SYNTAX_ERROR
            );
        } catch (DuplicateException $exception) {
            throw new BadRequestApiException(
                $exception->getMessage(),
                CommonErrorCode::DUPLICATE
            );
        }

        return new JsonResponse(
            $this->contactMapper->mapContactToArray($contact)
        );
    }

    /**
     * Patch User contact
     * @Route(
     *     "/api/contacts/{id}",
     *     name="patch_contact",
     *     condition="context.getMethod() in ['PATCH']"
     * )
     *
     * @param int $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws BadRequestApiException
     * @throws NotFoundApiException
     * @throws ValidationApiException
     */
    public function patchContact(int $id, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $contact = $this->contactRepository->findOneByUserAndId($user, $id);

        if ($contact === null) {
            throw new NotFoundApiException(
                'Contact not found.',
                ContactErrorCode::CONTACT_NOT_FOUND
            );
        }

        try {
            $contact = $this->contactManager->patchContact($user, $contact, $request);
        } catch (JsonException $exception) {
            throw new BadRequestApiException(
                'Wrong data format.',
                CommonErrorCode::SYNTAX_ERROR
            );
        } catch (DuplicateException $exception) {
            throw new BadRequestApiException(
                $exception->getMessage(),
                CommonErrorCode::DUPLICATE
            );
        }

        return new JsonResponse(
            $this->contactMapper->mapContactToArray($contact)
        );
    }

    /**
     * Delete User contact
     * @Route(
     *     "/api/contacts/{id}",
     *     name="delete_contact",
     *     condition="context.getMethod() in ['DELETE']"
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws NotFoundApiException
     */
    public function deleteContact(int $id): JsonResponse
    {
        $contact = $this->contactRepository->findOneByUserAndId($this->getUser(), $id);

        if ($contact === null) {
            throw new NotFoundApiException(
                'Contact not found.',
                ContactErrorCode::CONTACT_NOT_FOUND
            );
        }

        $this->contactManager->deleteContact($contact);

        return new JsonResponse([], 204);
    }
}
