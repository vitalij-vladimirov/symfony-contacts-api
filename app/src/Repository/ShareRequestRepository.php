<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ShareRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
     * @param int $senderId
     * @param string $status
     *
     * @return ShareRequest[] Returns an array of ShareRequest objects
     */
    public function findBySenderIdAndStatus(
        int $senderId,
        string $status = ShareRequest::STATUS_CREATED
    ) {
        return $this->createQueryBuilder('s')
            ->andWhere('s.sender_id = :senderId')
            ->setParameter('senderId', $senderId)
            ->andWhere('s.status = :status')
            ->setParameter('status', $status)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param int $receiverId
     * @param string $status
     *
     * @return ShareRequest[] Returns an array of ShareRequest objects
     */
    public function findByReceiverIdAndStatus(
        int $receiverId,
        string $status = ShareRequest::STATUS_CREATED
    ) {
        return $this->createQueryBuilder('s')
            ->andWhere('s.receiver_id = :receiverId')
            ->setParameter('receiverId', $receiverId)
            ->andWhere('s.status = :status')
            ->setParameter('status', $status)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult()
        ;
    }
}
