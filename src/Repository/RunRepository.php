<?php

namespace App\Repository;

use App\Entity\Run;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Run|null find($id, $lockMode = null, $lockVersion = null)
 * @method Run|null findOneBy(array $criteria, array $orderBy = null)
 * @method Run[]    findAll()
 * @method Run[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Run::class);
    }

   /**
    * @return Run[] Returns an array of Run objects
    */
    
    public function getRunningRunsForCluster($id)
    {
        $today = new \DateTime();
        return $this->createQueryBuilder('r')
            ->where('r.start <= :today')
            ->andWhere('r.date_end >= :today')
            ->andWhere('r.cluster = :id')
            ->setParameter('today', $today)
            ->setParameter('id', $id)
            ->orderBy('r.start', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

   /**
    * @return Run[] Returns an array of Run objects
    */
    
    public function getAllByController($id)
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.cluster', 'c')
            ->leftJoin('c.controller', 'cc')
            ->where('cc.id = :controller')
            ->setParameter('controller', $id)
            ->orderBy('r.start', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Run[] Returns an array of Run objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Run
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
