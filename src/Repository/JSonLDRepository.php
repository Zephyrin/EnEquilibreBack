<?php

namespace App\Repository;

use App\Entity\JSonLD;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method JSonLD|null find($id, $lockMode = null, $lockVersion = null)
 * @method JSonLD|null findOneBy(array $criteria, array $orderBy = null)
 * @method JSonLD[]    findAll()
 * @method JSonLD[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JSonLDRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JSonLD::class);
    }

    // /**
    //  * @return JSonLD Returns JSonLD objects
    //  */
    /* public function findById($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    } */


    /*
    public function findOneBySomeField($value): ?JSonLD
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
