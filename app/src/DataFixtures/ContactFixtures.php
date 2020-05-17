<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Contact;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Nubs\RandomNameGenerator\All as RandomNameGenerator;

class ContactFixtures extends Fixture
{
    private UserRepository $userRepository;
    private RandomNameGenerator $randomNameGenerator;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->randomNameGenerator = RandomNameGenerator::create();
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->userRepository->findAll();

        if ($users === null) {
            return;
        }

        foreach ($users as $user) {
            $contactsCount = random_int(1, 100);

            for ($i = 1; $i <= $contactsCount; ++$i) {
                $randomPhoneNr = random_int(37060000001, 37069999999);

                $contact = (new Contact())
                    ->setPhoneNr($randomPhoneNr)
                    ->setUserId($user)
                    ->setName($this->createRandomName())
                ;

                $manager->persist($contact);
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
}
