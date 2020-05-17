<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\ShareRequest;
use App\ErrorCode\CommonErrorCode;
use App\ErrorCode\ShareRequestErrorCode;
use App\Exception\BadRequestApiException;
use App\Exception\BadRequestException;
use App\Exception\DuplicateException;
use App\Exception\NotFoundApiException;
use App\Repository\ShareRequestRepository;
use App\Mapper\ContactMapper;
use App\Mapper\ShareRequestMapper;
use App\Service\ShareRequestManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\ValidationApiException;
use JsonException;

class ShareRequestController extends AbstractController
{
    private ShareRequestManager $shareRequestManager;
    private ShareRequestMapper $shareRequestResolver;
    private ShareRequestRepository $shareRequestRepository;
    private ContactMapper $contactMapper;

    public function __construct(
        ShareRequestManager $shareRequestManager,
        ShareRequestMapper $shareRequestResolver,
        ShareRequestRepository $shareRequestRepository,
        ContactMapper $contactMapper
    ) {
        $this->shareRequestManager = $shareRequestManager;
        $this->shareRequestResolver = $shareRequestResolver;
        $this->shareRequestRepository = $shareRequestRepository;
        $this->contactMapper = $contactMapper;
    }

    /**
     * Get User created Share requests
     * @Route(
     *     "/api/share-requests",
     *     name="get_created_share_requests",
     *     condition="context.getMethod() in ['GET']"
     * )
     *
     * @return JsonResponse
     */
    public function getCreatedShareRequests(): JsonResponse
    {
        $shareRequests = $this->shareRequestRepository->findBySenderIdAndStatus($this->getUser());

        return new JsonResponse([
            'total' => count($shareRequests),
            'data' => $this->shareRequestResolver->mapShareRequestsToArray($shareRequests)
        ]);
    }

    /**
     * Get User received Share requests
     * @Route(
     *     "/api/share-requests/received",
     *     name="get_received_share_requests",
     *     condition="context.getMethod() in ['GET']"
     * )
     *
     * @return JsonResponse
     */
    public function getReceivedShareRequests(): JsonResponse
    {
        $shareRequests = $this->shareRequestRepository->findByReceiverIdAndStatus($this->getUser());

        return new JsonResponse([
            'total' => count($shareRequests),
            'data' => $this->shareRequestResolver->mapShareRequestsToArray($shareRequests)
        ]);
    }

    /**
     * Create Share request
     * @Route(
     *     "/api/share-requests",
     *     name="create_share_request",
     *     condition="context.getMethod() in ['POST']"
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws BadRequestApiException
     * @throws ValidationApiException
     */
    public function createShareRequests(Request $request): JsonResponse
    {
        try {
            $shareRequest = $this->shareRequestManager->createShareRequest($this->getUser(), $request);
        } catch (JsonException $exception) {
            throw new BadRequestApiException(
                'Wrong data format.',
                CommonErrorCode::SYNTAX_ERROR
            );
        } catch (BadRequestException $exception) {
            throw new BadRequestApiException($exception->getMessage());
        } catch (DuplicateException $exception) {
            throw new BadRequestApiException(
                $exception->getMessage(),
                CommonErrorCode::DUPLICATE
            );
        }

        return new JsonResponse(
            $this->shareRequestResolver->mapShareRequestToArray($shareRequest)
        );
    }

    /**
     * Accept Share request by receiver
     * @Route(
     *     "/api/share-requests/{id}/accept",
     *     name="accept_share_request",
     *     condition="context.getMethod() in ['PATCH']"
     * )
     *
     * @param int $id
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     * @throws NotFoundApiException
     * @throws ValidationApiException
     */
    public function acceptShareRequests(int $id, Request $request): JsonResponse
    {
        $shareRequest = $this->shareRequestRepository
            ->findOneByReceiverAndIdAndStatus($this->getUser(), $id);

        if ($shareRequest === null) {
            throw new NotFoundApiException(
                'Pending share request not found.',
                ShareRequestErrorCode::SHARE_REQUEST_NOT_FOUND
            );
        }

        try {
            $contact = $this->shareRequestManager->acceptShareRequest($shareRequest, $request);
        } catch (JsonException $exception) {
            throw new BadRequestApiException(
                'Wrong data format.',
                CommonErrorCode::SYNTAX_ERROR
            );
        }

        return new JsonResponse(
            $this->contactMapper->mapContactToArray($contact)
        );
    }

    /**
     * Reject Share request by receiver
     * @Route(
     *     "/api/share-requests/{id}/reject",
     *     name="reject_share_request",
     *     condition="context.getMethod() in ['PATCH']"
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws BadRequestApiException
     * @throws NotFoundApiException
     */
    public function rejectShareRequests(int $id): JsonResponse
    {
        $shareRequest = $this->shareRequestRepository
            ->findOneByReceiverAndIdAndStatus($this->getUser(), $id);

        if ($shareRequest === null) {
            throw new NotFoundApiException(
                'Pending share request not found.',
                ShareRequestErrorCode::SHARE_REQUEST_NOT_FOUND
            );
        }

        try {
            $this->shareRequestManager->changeShareRequestStatus(
                $shareRequest,
                ShareRequest::STATUS_REJECTED
            );
        } catch (BadRequestException $exception) {
            throw new BadRequestApiException($exception->getMessage());
        }

        return new JsonResponse([], 204);
    }

    /**
     * Cancel Share request by creator
     * @Route(
     *     "/api/share-requests/{id}/cancel",
     *     name="cancel_share_request",
     *     condition="context.getMethod() in ['PATCH']"
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws NotFoundApiException
     * @throws BadRequestApiException
     */
    public function cancelShareRequests(int $id): JsonResponse
    {
        $shareRequest = $this->shareRequestRepository
            ->findOneBySenderAndIdAndStatus($this->getUser(), $id);

        if ($shareRequest === null) {
            throw new NotFoundApiException(
                'Pending share request not found.',
                ShareRequestErrorCode::SHARE_REQUEST_NOT_FOUND
            );
        }

        try {
            $this->shareRequestManager->changeShareRequestStatus(
                $shareRequest,
                ShareRequest::STATUS_CANCELLED
            );
        } catch (BadRequestException $exception) {
            throw new BadRequestApiException($exception->getMessage());
        }

        return new JsonResponse([], 204);
    }
}
