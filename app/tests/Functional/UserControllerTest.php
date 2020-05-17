<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Tests\BaseTestCase;

class UserControllerTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->truncate(User::class);

        self::ensureKernelShutdown();
    }

    public function testWillGetUserToken(): void
    {
        $user = $this->createUser();

        $client = self::createClient(
            [],
            [
                'PHP_AUTH_USER' => $user->getPhoneNr(),
                'PHP_AUTH_PW'   => self::DEFAULT_PASSWORD,
            ]
        );

        $client->request(
            'GET',
            '/api/users/token',
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                'token' => $user->getApiToken(),
            ],
            json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    public function testTryToGetUserTokenWithWrongAuthentication(): void
    {
        $client = self::createClient(
            [],
            [
                'PHP_AUTH_USER' => 37061234567,
                'PHP_AUTH_PW'   => 'bad-pass',
            ]
        );

        $client->request('GET', '/api/users/token');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                'error_code' => 'wrong_user_credentials',
                'message' => 'User not found.',
            ],
            json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    public function testTryToGetUserTokenWithoutAuthentication(): void
    {
        $client = self::createClient();
        
        $client->request('GET', '/api/users/token');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                'error_code' => 'wrong_user_credentials',
                'message' => 'User credentials not found.',
            ],
            json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    public function testWillTryToGetDataWithoutTokenAuthorizationAndFail(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/contacts');

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                'message' => 'Authentication Required',
            ],
            json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR)
        );
    }
}
