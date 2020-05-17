<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Contact;
use App\Entity\ShareRequest;
use App\Entity\User;
use App\Repository\ContactRepository;
use App\Repository\ShareRequestRepository;
use App\Repository\UserRepository;
use App\Service\ShareRequestManager;
use App\Validator\ContactValidator;
use App\Validator\ShareRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShareRequestManagerTest extends TestCase
{
    /** @var ShareRequestValidator|MockObject */
    private $shareRequestValidatorMock;

    /** @var ShareRequestRepository|MockObject */
    private $shareRequestRepositoryMock;

    /** @var UserRepository|MockObject */
    private $userRepositoryMock;

    /** @var ContactRepository|MockObject */
    private $contactRepositoryMock;

    /** @var ContactValidator|MockObject */
    private $contactValidatorMock;

    /** @var EntityManagerInterface|MockObject */
    private $entityManagerMock;

    /** @var User|MockObject */
    private $senderMock;

    /** @var Request|MockObject */
    private $requestMock;

    /** @var ShareRequest|MockObject */
    private $shareRequestMock;

    private ShareRequestManager $shareRequestManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->shareRequestValidatorMock = $this->createMock(ShareRequestValidator::class);
        $this->shareRequestRepositoryMock = $this->createMock(ShareRequestRepository::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->contactRepositoryMock = $this->createMock(ContactRepository::class);
        $this->contactValidatorMock = $this->createMock(ContactValidator::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->senderMock = $this->createMock(User::class);
        $this->requestMock = $this->createMock(Request::class);
        $this->shareRequestMock = $this->createMock(ShareRequest::class);

        $this->shareRequestManager = new ShareRequestManager(
            $this->shareRequestValidatorMock,
            $this->shareRequestRepositoryMock,
            $this->userRepositoryMock,
            $this->contactRepositoryMock,
            $this->contactValidatorMock,
            $this->entityManagerMock
        );
    }

    public function testWillCreateShareRequest(): void
    {
        $this
            ->senderMock
            ->expects($this->once())
            ->method('getPhoneNr')
            ->willReturn(37067654321)
        ;

        $this
            ->requestMock
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('{"receiver":37061234567,"contact_id":1,"name":"Attila the Hun"}')
        ;

        $this
            ->requestMock
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('{"name":"Genghis Khan"}')
        ;

        $this
            ->contactValidatorMock
            ->expects($this->once())
            ->method('validatePhoneNr')
        ;

        $this
            ->shareRequestValidatorMock
            ->expects($this->once())
            ->method('validateContactId')
        ;

        $this
            ->contactValidatorMock
            ->expects($this->once())
            ->method('validateName')
        ;

        $this
            ->userRepositoryMock
            ->expects($this->once())
            ->method('findOneByPhoneNr')
            ->willReturn(new User())
        ;

        $this
            ->contactRepositoryMock
            ->expects($this->once())
            ->method('findOneByUserAndId')
            ->willReturn(
                (new Contact())
                    ->setPhoneNr(37069173465)
            )
        ;

        $this
            ->shareRequestRepositoryMock
            ->expects($this->once())
            ->method('findOneBySenderAndReceiverAndPhoneNr')
            ->willReturn(null)
        ;

        $this
            ->entityManagerMock
            ->expects($this->once())
            ->method('persist')
        ;

        $this->shareRequestManager->createShareRequest(
            $this->senderMock,
            $this->requestMock
        );
    }

    public function testWillAcceptShareRequest(): void
    {
        $this
            ->requestMock
            ->expects($this->exactly(3))
            ->method('getContent')
            ->willReturn('{"name":"Genghis Khan"}')
        ;

        $this
            ->contactValidatorMock
            ->expects($this->once())
            ->method('validateName')
        ;

        $this
            ->contactRepositoryMock
            ->expects($this->once())
            ->method('findOneByUserAndPhoneNr')
            ->willReturn(new Contact())
        ;

        $this
            ->shareRequestMock
            ->expects($this->once())
            ->method('getReceiver')
            ->willReturn(new User())
        ;

        $this
            ->shareRequestMock
            ->expects($this->once())
            ->method('getPhoneNr')
            ->willReturn(37061593575)
        ;

        $this
            ->entityManagerMock
            ->expects($this->exactly(2))
            ->method('persist')
        ;

        $this->shareRequestManager->acceptShareRequest(
            $this->shareRequestMock,
            $this->requestMock
        );
    }
}
