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

    // /**
    //  * @return ShareRequest[] Returns an array of ShareRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ShareRequest
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
