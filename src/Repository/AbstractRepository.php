<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;

trait AbstractRepository
{
  public function getPage($page = 1)
  {
    if ($page < 1) {
      $page = 1;
    }

    return floor($page);
  }

  public function getLimit($limit = 20)
  {
    if ($limit < 1 || $limit > 20) {
      $limit = 20;
    }

    return floor($limit);
  }

  public function getOffset($page, $limit)
  {
    $offset = 0;
    if ($page != 0 && $page != 1) {
      $offset = ($page - 1) * $limit;
    }

    return $offset;
  }

  /**
   * $query The query builder
   * $entityName the name of the entity to count in. Like App\Entity\SubCategory
   * $parentName the name of the relation in the $entityName. Like category
   * $countExp the expression of counting.
   * $preParamName the name use to distinguish different request.
   * @param QueryBuilder $query
   * @param string|null $entityName
   * @param string|null $parentName
   * @param string|null $countExp
   * @param string $preParamName
   * @return QueryBuilder
   */
  public function setLowerGreaterEqual(
    QueryBuilder $query,
    ?string $entityName,
    ?string $parentName,
    ?string $countExp,
    string $preParamName
  ) {
    if ($countExp) {
      preg_match('/(l|le|e|g|ge)(\d+)((l|le|e|g|ge)(\d+))?/', $countExp, $match, PREG_UNMATCHED_AS_NULL);
      $sup = null;
      $supE = null;
      $eq = null;
      $lower = null;
      $lowerE = null;
      for ($i = 1; $i <= 3; $i = $i + 2) {
        if ($match[$i] != null && $match[$i + 1] != null) {
          switch ($match[$i]) {
            case "l":
              $lower = $match[$i + 1];
              break;
            case "le":
              $lowerE = $match[$i + 1];
              break;
            case "e":
              $eq = $match[$i + 1];
              break;
            case "g":
              $sup = $match[$i + 1];
              break;
            case "ge":
              $supE = $match[$i + 1];
              break;
            default:
              break;
          }
        }
      }
      $subQuery = '(select count(s.id) from ' . $entityName . ' s where e.id = s.' . $parentName . ')';
      if ($sup != null)
        $query = $query->andWhere($subQuery . ' > :' . $preParamName . 'sup')
          ->setParameter($preParamName . 'sup', $sup);
      if ($supE != null)
        $query = $query->andWhere($subQuery . ' >= :' . $preParamName . 'supE')
          ->setParameter($preParamName . 'supE', $supE);
      if ($eq != null)
        $query = $query->andWhere($subQuery . ' == :' . $preParamName . 'eq')
          ->setParameter($preParamName . 'eq', $eq);
      if ($lower != null)
        $query = $query->andWhere($subQuery . ' < :' . $preParamName . 'lower')
          ->setParameter($preParamName . 'lower', $lower);
      if ($lowerE != null)
        $query = $query->andWhere($subQuery . ' > :' . $preParamName . 'lowerE')
          ->setParameter($preParamName . 'lowerE', $lowerE);
    }
    return $query;
  }

  public function resultCount(
    QueryBuilder $query,
    int $page,
    int $limit,
    bool $noPagination,
    string $sort,
    string $sortBy
  ) {
    $page = $this->getPage($page);
    $limit = $this->getLimit($limit);
    $offset = $this->getOffset($page, $limit);
    if (strpos($sortBy, 'COUNT') === false) {
      $lSortBy = 'e.' . $sortBy;
    } else {
      $lSortBy = $sortBy;
    }

    $queryCount = clone $query;
    $count =  $queryCount
      ->select("count(DISTINCT e.id)")
      ->getQuery()->getSingleScalarResult();
    if (!$noPagination) {
      $ret = $query
        ->groupBy('e.id')
        ->orderBy($lSortBy, $sort)
        ->getQuery()->getResult();
    } else {
      $ret = $query
        ->setMaxResults($limit)
        ->setFirstResult($offset)
        ->groupBy('e.id')
        ->orderBy($lSortBy, $sort)
        ->getQuery()->getResult();
    }
    return [$ret, $count, count($ret) + $offset, $page, $limit];
  }
}
