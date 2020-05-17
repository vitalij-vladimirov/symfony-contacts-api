<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ShareRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method ShareRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShareRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShareRequest[]    findAll()
 * @method ShareRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShareRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShareRequest::class);
    }

    /**
     * @param User|UserInterface $sender
     * @param string $status
     *
     * @return ShareRequest[] Returns an array of ShareRequest objects
     */
    public function findBySenderIdAndStatus(
        User $sender,
        string $status = ShareRequest::STATUS_CREATED
    ): array {
        return $this->createQueryBuilder('s')
            ->andWhere('s.sender = :sender')
            ->setParameter('sender', $sender)
            ->andWhere('s.status = :status')
            ->setParameter('status', $status)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param User|UserInterface $receiver
     * @param string $status
     *
     * @return ShareRequest[] Returns an array of ShareRequest objects
     */
    public function findByReceiverIdAndStatus(
        User $receiver,
        string $status = ShareRequest::STATUS_CREATED
    ): array {
        return $this->createQueryBuilder('s')
            ->andWhere('s.receiver = :receiver')
            ->setParameter('receiver', $receiver)
            ->andWhere('s.status = :status')
            ->setParameter('status', $status)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param User $sender
     * @param User $receiver
     * @param int|string $phoneNr
     *
     * @return ShareRequest|null
     */
    public function findOneBySenderAndReceiverAndPhoneNr(
        User $sender,
        User $receiver,
        $phoneNr
    ): ?ShareRequest {
        return $this->findOneBy([
            'sender' => $sender,
            'receiver' => $receiver,
            'phoneNr' => $phoneNr
        ]);
    }

    /**
     * @param User|UserInterface $sender
     * @param int $shareRequestId
     * @param string $status
     *
     * @return ShareRequest|null
     */
    public function findOneBySenderAndIdAndStatus(
        User $sender,
        int $shareRequestId,
        string $status = ShareRequest::STATUS_CREATED
    ): ?ShareRequest {
        return $this->findOneBy([
            'sender' => $sender,
            'id' => $shareRequestId,
            'status' => $status
        ]);
    }

    /**
     * @param User|UserInterface $receiver
     * @param int $shareRequestId
     * @param string $status
     *
     * @return ShareRequest|null
     */
    public function findOneByReceiverAndIdAndStatus(
        User $receiver,
        int $shareRequestId,
        string $status = ShareRequest::STATUS_CREATED
    ): ?ShareRequest {
        return $this->findOneBy([
            'receiver' => $receiver,
            'id' => $shareRequestId,
            'status' => $status
        ]);
    }
}
