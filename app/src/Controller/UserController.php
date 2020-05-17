<?php
declare(strict_types=1);

namespace App\Controller;

use App\ErrorCode\UserErrorCode;
use App\Exception\BadRequestApiException;
use App\Exception\WrongUserCredentialsException;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class UserController
{
    private UserManager $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Get User token
     * @Route(
     *     "/api/users/token",
     *     name="get_user_token",
     *     condition="context.getMethod() in ['GET']"
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws BadRequestApiException
     */
    public function getUserToken(Request $request): JsonResponse
    {
        try {
            return new JsonResponse([
                'token' => $this->userManager->getUserToken($request->getUserInfo())
            ]);
        } catch (WrongUserCredentialsException $exception) {
            throw new BadRequestApiException(
                $exception->getMessage(),
                UserErrorCode::WRONG_USER_CREDENTIALS
            );
        } catch (Throwable $throwable) {
            throw new BadRequestApiException(
                'User credentials not found.',
                UserErrorCode::WRONG_USER_CREDENTIALS
            );
        }
    }
}
