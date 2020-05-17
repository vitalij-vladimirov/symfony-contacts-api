<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contact;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Contact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    /**
     * @param User $user
     *
     * @return Contact[] Returns an array of Contact objects
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    /**
     * @param User|UserInterface $user
     * @param int|string $contactId
     *
     * @return Contact|null
     */
    public function findOneByUserAndId(User $user, $contactId): ?Contact
    {
        return $this->findOneBy([
            'user' => $user,
            'id' => $contactId
        ]);
    }

    /**
     * @param User|UserInterface $user
     * @param int|string $phoneNr
     * @param int|null $exceptId
     *
     * @return Contact|null
     */
    public function findOneByUserAndPhoneNr(User $user, $phoneNr, int $exceptId = null): ?Contact
    {
        if ($exceptId !== null) {
            $contacts = $this->createQueryBuilder('c')
                ->where('c.user = :user')
                ->andWhere('c.id <> :contactId')
                ->andWhere('c.phoneNr = :phoneNr')
                ->setParameters([
                    'user' => $user,
                    'contactId' => $exceptId,
                    'phoneNr' => $phoneNr,
                ])
                ->orderBy('c.name', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getResult()
            ;

            return count($contacts) === 1 ? $contacts[0] : null;
        }

        return $this->findOneBy([
            'user' => $user,
            'phoneNr' => $phoneNr
        ]);
    }

    /**
     * @param User $user
     * @param string $searchValue
     *
     * @return Contact[] Returns an array of Contact objects
     */
    public function findByUserAndSearchValue(User $user, string $searchValue): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.name LIKE :searchValue OR c.phoneNr LIKE :searchValue')
            ->setParameters([
                'user' => $user,
                'searchValue' => '%' . $searchValue . '%',
            ])
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
