<?php

namespace App\Repository;

use App\Entity\Controller;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
/**
 * @method Controller|null find($id, $lockMode = null, $lockVersion = null)
 * @method Controller|null findOneBy(array $criteria, array $orderBy = null)
 * @method Controller[]    findAll()
 * @method Controller[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ControllerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Controller::class);
    }

    // /**
    //  * @return Controller[] Returns an array of Controller objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Controller
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
