<?php

namespace App\Repository;

use App\Entity\Gallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use FOS\RestBundle\Request\ParamFetcher;

/**
 * @method Gallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gallery[]    findAll()
 * @method Gallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GalleryRepository extends ServiceEntityRepository
{
    use AbstractRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gallery::class);
    }

    public function findAllPagination(ParamFetcher $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');
        $sort = $paramFetcher->get('sort');
        $sortBy = $paramFetcher->get('sortBy');
        $search = $paramFetcher->get('search');
        $query = $this->createQueryBuilder('e');
        if ($search != null)
            $query = $query->andWhere('LOWER(e.title) LIKE :search')
                ->setParameter('search', "%" . addcslashes(strtolower($search), '%_') . '%');
        return $this->resultCount($query, $page, $limit, false, $sort, $sortBy);
    }
}
