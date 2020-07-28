<?php

namespace App\Repository;

use App\Entity\RunStep;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RunStep|null find($id, $lockMode = null, $lockVersion = null)
 * @method RunStep|null findOneBy(array $criteria, array $orderBy = null)
 * @method RunStep[]    findAll()
 * @method RunStep[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RunStepRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RunStep::class);
    }

    // /**
    //  * @return RunStep[] Returns an array of RunStep objects
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
    public function findOneBySomeField($value): ?RunStep
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
