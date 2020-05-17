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
     * @return JsonResponse
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
        }
    }
}
