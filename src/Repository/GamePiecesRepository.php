<?php

namespace App\Repository;

use App\Entity\GamePieces;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GamePieces|null find($id, $lockMode = null, $lockVersion = null)
 * @method GamePieces|null findOneBy(array $criteria, array $orderBy = null)
 * @method GamePieces[]    findAll()
 * @method GamePieces[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GamePiecesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GamePieces::class);
    }

    // /**
    //  * @return GamePieces[] Returns an array of GamePieces objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GamePieces
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
