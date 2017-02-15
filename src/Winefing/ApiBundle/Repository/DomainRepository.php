<?php

namespace Winefing\ApiBundle\Repository;

/**
 * DomainRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DomainRepository extends \Doctrine\ORM\EntityRepository
{
    function findOneWithUser($userId)
    {
        $query = $this->createQueryBuilder('domain')
            ->join("domain.user", "user")
            ->where('user.id = :userId')
            ->setParameter('userId', $userId)
            ->setMaxResults(1)
            ->getQuery();
        return $query->getOneOrNullResult();
    }
    function findGroupByWineRegion()
    {
        $query = $this->createQueryBuilder('domain')
            ->join("domain.wineRegion", "wineRegion")
            ->groupBy('wineRegion.id')
            ->getQuery();
        return $query->getResult();
    }
    function findOneWithMediaId($mediaId)
    {
        $query = $this->createQueryBuilder('domain')
            ->innerJoin("domain.medias", "medias")
            ->where('medias.id = :mediaId')
            ->setParameter('mediaId', $mediaId)
            ->setMaxResults(1)
            ->getQuery();
        return $query->getOneOrNullResult();
    }
    function findWithCriterias($criterias) {
        //basic
        $queryBuilder = $this->createQueryBuilder('domain')
            ->innerJoin("domain.wineRegion", "wineRegion")
            ->innerJoin("domain.properties", "property")
            ->innerJoin("property.rentals", "rental")
            ->where("rental.peopleNumber >= :peopleNumer")
            ->setParameter("peopleNumer", $criterias["peopleNumber"]);

        //check for the wineRegion parameter
        if(!empty($criterias["wineRegion"])) {
            $queryBuilder
                ->andWhere("wineRegion.id in (:wineRegion)")
                ->setParameter("wineRegion", array_values($criterias["wineRegion"]));
        }
        //check for the location available
        if(!empty($criterias["startDate"])) {

        }
        //check for the price slider
        if(!empty($criterias["price"])) {
            $price = explode(",",$criterias["price"]);
            $queryBuilder
            ->andWhere("rental.price >= :minPrice and <= :maxPrice")
            ->setParameter("minPrice", $price[0])
            ->setParameter("maxPrice", $price[1]);
        }
        $query = $queryBuilder->getQuery();
        return $query->getResult();
    }
}
