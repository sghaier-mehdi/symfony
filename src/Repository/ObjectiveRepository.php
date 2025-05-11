<?php

namespace App\Repository;

use App\Entity\Objective;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Objective>
 *
 * @method Objective|null find($id, $lockMode = null, $lockVersion = null)
 * @method Objective|null findOneBy(array $criteria, array $orderBy = null)
 * @method Objective[]    findAll()
 * @method Objective[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ObjectiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Objective::class);
    }

    public function save(Objective $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Objective $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Objective[] Returns an array of objectives by user
     */
    public function findByUser($user): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.pointsRequired', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
} 