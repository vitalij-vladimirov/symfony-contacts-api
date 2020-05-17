<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Contact;
use App\Entity\User;
use App\Tests\BaseTestCase;

class ContactControllerTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->truncate(User::class);
        $this->truncate(Contact::class);
    }

    public function testWillGetFilteredListOfContacts(): void
    {
        $user = $this->createUser();

        $contact1 = (new Contact())
            ->setUser($user)
            ->setName('John Doe')
            ->setPhoneNr(37069999991)
        ;

        $contact2 = (new Contact())
            ->setUser($user)
            ->setName('Jane Doe')
            ->setPhoneNr(37069999992)
        ;

        $contact3 = (new Contact())
            ->setUser($user)
            ->setName('James Dean')
            ->setPhoneNr(37069999993)
        ;

        $this->entityManager->persist($contact1);
        $this->entityManager->persist($contact2);
        $this->entityManager->persist($contact3);
        $this->entityManager->flush();

        $request = $this->sendRequest(
            'GET',
            '/api/contacts',
            $user->getApiToken(),
            [
                'search' => 'Doe'
            ]
        );

        $this->assertEquals(200, $request->getStatusCode());
        $this->assertEquals(
            [
                'total' => 2,
                'data' => [
                    [
                        'id' => $contact2->getId(),
                        'name' => 'Jane Doe',
                        'phone_nr' => 37069999992,
                    ],
                    [
                        'id' => $contact1->getId(),
                        'name' => 'John Doe',
                        'phone_nr' => 37069999991,
                    ],
                ]
            ],
            $request->getContentDecoded()
        );
    }

    public function testWillGetExactContact(): void
    {
        $user = $this->createUser();

        $contact1 = (new Contact())
            ->setUser($user)
            ->setName('John Doe')
            ->setPhoneNr(37069999991)
        ;

        $contact2 = (new Contact())
            ->setUser($user)
            ->setName('Jane Doe')
            ->setPhoneNr(37069999992)
        ;

        $this->entityManager->persist($contact1);
        $this->entityManager->persist($contact2);
        $this->entityManager->flush();

        $request = $this->sendRequest(
            'GET',
            '/api/contacts/' . $contact2->getId(),
            $user->getApiToken()
        );

        $this->assertEquals(200, $request->getStatusCode());
        $this->assertEquals(
            [
                'id' => $contact2->getId(),
                'name' => 'Jane Doe',
                'phone_nr' => 37069999992,
            ],
            $request->getContentDecoded()
        );
    }

    public function testWillCreateContact(): void
    {
        $user = $this->createUser();

        $request = $this->sendRequest(
            'POST',
            '/api/contacts',
            $user->getApiToken(),
            [
                'name' => 'King Richard',
                'phone_nr' => 37067865953,
            ]
        );

        $this->assertEquals(200, $request->getStatusCode());
        $this->assertEquals(
            [
                'id' => 1,
                'name' => 'King Richard',
                'phone_nr' => 37067865953,
            ],
            $request->getContentDecoded()
        );
    }

    public function testWillUpdateContact(): void
    {
        $user = $this->createUser();

        $contact = (new Contact())
            ->setUser($user)
            ->setName('Hannibal Barca')
            ->setPhoneNr(37069999994)
        ;

        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        $request = $this->sendRequest(
            'PUT',
            '/api/contacts/' . $contact->getId(),
            $user->getApiToken(),
            [
                'name' => 'Scipio Africanus',
                'phone_nr' => 37069999995,
            ]
        );

        $this->assertEquals(200, $request->getStatusCode());
        $this->assertEquals(
            [
                'id' => $contact->getId(),
                'name' => 'Scipio Africanus',
                'phone_nr' => 37069999995,
            ],
            $request->getContentDecoded()
        );
    }

    public function testWillPatchContact(): void
    {
        $user = $this->createUser();

        $contact = (new Contact())
            ->setUser($user)
            ->setName('Alexander Bell')
            ->setPhoneNr(37069999996)
        ;

        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        $request = $this->sendRequest(
            'PATCH',
            '/api/contacts/' . $contact->getId(),
            $user->getApiToken(),
            [
                'name' => 'Alexander Graham Bell',
            ]
        );

        $this->assertEquals(200, $request->getStatusCode());
        $this->assertEquals(
            [
                'id' => $contact->getId(),
                'name' => 'Alexander Graham Bell',
                'phone_nr' => 37069999996,
            ],
            $request->getContentDecoded()
        );
    }

    public function testWillDeleteContact(): void
    {
        $user = $this->createUser();

        $contact = (new Contact())
            ->setUser($user)
            ->setName('Osama bin Laden')
            ->setPhoneNr(37069999997)
        ;

        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        $request = $this->sendRequest(
            'DELETE',
            '/api/contacts/' . $contact->getId(),
            $user->getApiToken(),
        );

        $this->assertEquals(204, $request->getStatusCode());
        $this->assertEmpty($request->getContentDecoded());

        $request = $this->sendRequest(
            'GET',
            '/api/contacts',
            $user->getApiToken(),
        );

        $this->assertEquals(
            [
                'total' => 0,
                'data' => []
            ],
            $request->getContentDecoded()
        );
    }

    public function testWillTryToCreateContactWithInvalidDataAndFail(): void
    {
        $user = $this->createUser();

        $request = $this->sendRequest(
            'POST',
            '/api/contacts',
            $user->getApiToken(),
            [
                'name' => 'Mister Bean',
                'phone_nr' => 123,
            ]
        );

        $this->assertEquals(400, $request->getStatusCode());
        $this->assertEquals(
            [
                'error_code' => 'bad_request',
                'message' => 'Bad Request',
                'data' => [
                    'Field \'phone_nr\' is too short. Its length should vary between 9 and 15.'
                ]
            ],
            $request->getContentDecoded()
        );
    }

    public function testWillTryToUpdateOtherUserContactAndFail(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $contact1 = (new Contact())
            ->setUser($user1)
            ->setName('Julius Caesar')
            ->setPhoneNr(37069999997)
        ;

        $contact2 = (new Contact())
            ->setUser($user2)
            ->setName('Marcus Licinius Crassus')
            ->setPhoneNr(37069999998)
        ;

        $this->entityManager->persist($contact1);
        $this->entityManager->persist($contact2);
        $this->entityManager->flush();

        $request = $this->sendRequest(
            'PUT',
            '/api/contacts/' . $contact1->getId(),
            $user2->getApiToken(),
            [
                'name' => 'Pompey',
                'phone_nr' => 37069999999,
            ]
        );

        $this->assertEquals(404, $request->getStatusCode());
        $this->assertEquals(
            [
                'error_code' => 'contact_not_found',
                'message' => 'Contact not found',
            ],
            $request->getContentDecoded()
        );
    }
}
