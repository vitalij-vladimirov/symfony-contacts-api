<?php
declare(strict_types=1);

namespace App\Service;

use App\Exception\WrongUserCredentialsException;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserManager
{
    private UserRepository $userRepository;
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function getUserToken(string $userInfo): string
    {
        [$phoneNr, $password] = explode(':', $userInfo);

        $user = $this->userRepository->findOneBy([
            'phoneNr' => $phoneNr
        ]);

        if ($user === null) {
            throw new WrongUserCredentialsException('User not found.');
        }

        if (!$this->passwordEncoder->isPasswordValid($user, $password)) {
            throw new WrongUserCredentialsException('Wrong user credentials');
        }

        return $user->getApiToken();
    }
}
