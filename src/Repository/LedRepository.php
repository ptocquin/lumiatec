<?php

namespace App\Repository;

use App\Entity\Led;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Led|null find($id, $lockMode = null, $lockVersion = null)
 * @method Led|null findOneBy(array $criteria, array $orderBy = null)
 * @method Led[]    findAll()
 * @method Led[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LedRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Led::class);
    }

    // /**
    //  * @return Led[] Returns an array of Led objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Led
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
