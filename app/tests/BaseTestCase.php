<?php
declare(strict_types=1);

namespace App\Tests;

use App\Entity\User;
use App\Exception\LogicException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Data\Response;

class BaseTestCase extends WebTestCase
{
    private const ALLOWED_HTTP_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    protected const DEFAULT_PASSWORD = 'pass';

    /** @var UserPasswordEncoderInterface */
    protected $passwordEncoder;

    /** @var EntityManagerInterface */
    protected $entityManager;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->entityManager = self::$container->get(EntityManagerInterface::class);
        $this->passwordEncoder = self::$container->get(UserPasswordEncoderInterface::class);
    }

    protected function truncate(string $entity): void
    {
        $connection = $this->entityManager->getConnection();
        $truncateQuery = $connection->getDatabasePlatform()->getTruncateTableSQL(
            $this->entityManager->getClassMetadata($entity)->getTableName()
        );

        if ($connection->getDatabasePlatform()->supportsForeignKeyConstraints()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
        }

        $connection->executeUpdate($truncateQuery);
    }

    protected function createUser(
        int $phoneNr = null,
        string $password = self::DEFAULT_PASSWORD
    ): User {
        if ($phoneNr === null) {
            $phoneNr = random_int(37060000001, 37069999999);
        }

        $user = (new User())
            ->setPhoneNr($phoneNr)
            ->setRoles(['ROLE_USER'])
            ->setApiToken(bin2hex(random_bytes(60)))
        ;

        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    protected function sendRequest(
        string $method,
        string $uri,
        string $token,
        array $data = []
    )
    {
        if (!in_array($method, self::ALLOWED_HTTP_METHODS, true)) {
            throw new LogicException('Not allowed method!');
        }

        $query = [];
        $jsonData = '{}';

        if (count($data) !== 0) {
            switch ($method) {
                case 'GET':
                    $query = $data;
                    break;
                case 'POST':
                case 'PUT':
                case 'PATCH':
                    $jsonData = json_encode($data, JSON_THROW_ON_ERROR);
                    break;
            }
        }

        self::ensureKernelShutdown();

        $client = self::createClient();

        $client->request(
            $method,
            $uri,
            $query,
            [],
            [
                'HTTP_X_AUTH_TOKEN' => $token,
                'CONTENT_TYPE' => 'application/json',
            ],
            $jsonData
        );

        return new Response($client->getResponse());
    }
}
