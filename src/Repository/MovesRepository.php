<?php

namespace App\Repository;

use App\Entity\Moves;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Moves>
 *
 * @method Moves|null find($id, $lockMode = null, $lockVersion = null)
 * @method Moves|null findOneBy(array $criteria, array $orderBy = null)
 * @method Moves[]    findAll()
 * @method Moves[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Moves::class);
    }

//    /**
//     * @return Moves[] Returns an array of Moves objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Moves
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
