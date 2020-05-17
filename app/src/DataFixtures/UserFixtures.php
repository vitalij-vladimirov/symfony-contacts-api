<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Contact;
use App\Entity\ShareRequest;
use App\Entity\User;
use App\Repository\ContactRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Nubs\RandomNameGenerator\All as RandomNameGenerator;
use Throwable;

class UserFixtures extends Fixture
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private UserRepository $userRepository;
    private ContactRepository $contactRepository;
    private RandomNameGenerator $randomNameGenerator;
    private ObjectManager $manager;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        ContactRepository $contactRepository
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->contactRepository = $contactRepository;

        $this->randomNameGenerator = RandomNameGenerator::create();
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = clone $manager;

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

        $users = $this->userRepository->findAll();

        $this->loadContacts($users);
        $this->loadShareRequests($users);
    }

    /**
     * @param User[] $users
     * @throws Throwable
     */
    private function loadContacts(array $users): void
    {
        $manager = clone $this->manager;

        foreach ($users as $user) {
            $contactsCount = random_int(1, 100);

            for ($i = 1; $i <= $contactsCount; ++$i) {
                $randomPhoneNr = random_int(37060000001, 37069999999);

                $contact = (new Contact())
                    ->setPhoneNr($randomPhoneNr)
                    ->setUser($user)
                    ->setName($this->createRandomName())
                ;

                $manager->persist($contact);
            }
        }

        $manager->flush();
    }

    /**
     * @param User[] $users
     * @throws Throwable
     */
    private function loadShareRequests(array $users): void
    {
        $manager = clone $this->manager;

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

    private function createRandomName(): string
    {
        $randomName = $this->randomNameGenerator->getName();

        if (strlen($randomName) <= 55) {
            return $randomName;
        }

        return $this->createRandomName();
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
