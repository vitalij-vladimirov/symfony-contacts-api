<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private ContactFixtures $contactFixtures;
    private ShareRequestFixtures $shareRequestFixtures;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        ContactFixtures $contactFixtures,
        ShareRequestFixtures $shareRequestFixtures
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->contactFixtures = $contactFixtures;
        $this->shareRequestFixtures = $shareRequestFixtures;
    }

    public function load(ObjectManager $manager): void
    {
        $emptyManager = clone $manager;

        $userPhones = [
            37061111111,
            37062222222,
            37063333333,
        ];

        foreach ($userPhones as $userPhone) {
            $user = (new User())
                ->setPhoneNr($userPhone)
                ->setRoles(['ROLE_USER'])
                ->setApiToken(bin2hex(random_bytes(60)))
            ;

            $user->setPassword($this->passwordEncoder->encodePassword($user, 'pass'));

            $manager->persist($user);
        }

        $manager->flush();

        if (getenv('APP_ENV') === 'dev') {
            $this->contactFixtures->load($emptyManager);
            $this->shareRequestFixtures->load($emptyManager);
        }
    }
}
