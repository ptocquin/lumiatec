<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
/**
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    // /**
    //  * @return Log[] Returns an array of Log objects
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

    
    public function findByControllerLightingTime($controller, $lighting, $time)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.controller = :controller')
            ->andWhere('l.luminaire = :lighting')
            ->andWhere('l.time = :time')
            ->setParameter('controller', $controller)
            ->setParameter('lighting', $lighting)
            ->setParameter('time', $time)
            ->getQuery()
            ->getResult()
        ;
    }

   /**
    * @return Log[] Returns an array of Log objects
    */
    
    public function getLuminaireLastLog($luminaire)
    {
        return $this->createQueryBuilder('l')
            ->where('l.luminaire = :luminaire')
            ->andWhere('l.type = :type')
            ->setParameter('luminaire', $luminaire)
            ->setParameter('type', 'luminaire_info')
            ->orderBy('l.time', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }

   /**
    * @return Log
    */
    
    public function getControllerLastLog($controller)
    {
        return $this->createQueryBuilder('l')
            ->where('l.controller = :controller')
            ->andWhere('l.type = :type')
            ->setParameter('controller', $controller)
            ->setParameter('type', 'luminaire_info')
            ->orderBy('l.time', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

   /**
    * @return Log[] Returns an array of Log objects
    */
    
    public function getLastLog()
    {
        return $this->createQueryBuilder('l')
            // ->andWhere('l.type = :type')
            // ->setParameter('type', 'cluster_info')
            ->orderBy('l.time', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }
    
}
