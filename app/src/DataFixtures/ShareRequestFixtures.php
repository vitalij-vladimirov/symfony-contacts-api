<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ShareRequest;
use App\Repository\ContactRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ShareRequestFixtures extends Fixture
{
    private UserRepository $userRepository;
    private ContactRepository $contactRepository;

    public function __construct(
        UserRepository $userRepository,
        ContactRepository $contactRepository
    ) {
        $this->userRepository = $userRepository;
        $this->contactRepository = $contactRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->userRepository->findAll();

        if ($users === null) {
            return;
        }

        foreach ($users as $user) {
            $shareCount = random_int(1, 20);

            $contacts = $this->contactRepository->findAll();

            $totalRequests = 0;
            foreach ($contacts as $contact) {
                $shareRequest = (new ShareRequest())
                    ->setSender($user)
                    ->setReceiver($this->getRandomObject($users, $user))
                    ->setPhoneNr($contact->getPhoneNr())
                    ->setName($contact->getName())
                    ->setStatus($this->getRandomObject(ShareRequest::STATUS_LIST))
                ;

                $manager->persist($shareRequest);

                if (++$totalRequests === $shareCount) {
                    break;
                }
            }
        }

        $manager->flush();
    }

    private function getRandomObject(array $objects, $except = null)
    {
        if ($except === null) {
            return $objects[random_int(0, count($objects)-1)];
        }

        $objectsList = [];

        foreach ($objects as $object) {
            if ($object === $except) {
                continue;
            }

            $objectsList[] = $object;
        }

        if (count($objectsList) === 0) {
            return null;
        }

        return $this->getRandomObject($objectsList);
    }
}
