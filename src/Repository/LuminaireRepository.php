<?php

namespace App\Repository;

use App\Entity\Luminaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
/**
 * @method Luminaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Luminaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Luminaire[]    findAll()
 * @method Luminaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LuminaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Luminaire::class);
    }

    public function getXMax($controller)
    {
        return $this->createQueryBuilder('l')
            ->select('MAX(l.colonne) as x_max')
            ->andWhere('l.controller = :controller')
            ->setParameter('controller', $controller) 
            ->getQuery()
            ->getSingleResult()
            ;
    }


    public function getYMax($controller)
    {
        return $this->createQueryBuilder('l')
            ->select('MAX(l.ligne) as y_max')
            ->andWhere('l.controller = :controller')
            ->setParameter('controller', $controller) 
            ->getQuery()
            ->getSingleResult()
            ;
    }

    public function getByXY($x, $y, $controller): ?Luminaire
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.ligne = :y')
            ->andWhere('l.colonne = :x')
            ->andWhere('l.controller = :controller')
            ->setParameter('y', $y)
            ->setParameter('x', $x)
            ->setParameter('controller', $controller)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getNotMapped($controller)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.ligne is null')
            ->andWhere('l.colonne is null')
            ->andWhere('l.controller = :controller')
            ->setParameter('controller', $controller)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Luminaire[] Returns an array of Luminaire objects
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
    public function findOneBySomeField($value): ?Luminaire
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
