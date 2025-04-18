<?php

namespace App\Repository;

use App\Entity\Challenge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Challenge>
 */
class ChallengeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Challenge::class);
    }

    //    /**
    //     * @return Challenge[] Returns an array of Challenge objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Challenge
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @return Challenge[] Returns an array of Challenge objects ordered by challengeSection.ordering, challengeCategory.ordering and challenge.ordering
     */
    public function findAllWithCategoriesOrderedBy(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.challengeCategory', 'cat')
            ->leftJoin('cat.challengeSection', 'sec')
            ->orderBy('sec.ordering', 'ASC')
            ->addOrderBy('cat.ordering', 'ASC')
            ->addOrderBy('c.ordering', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
