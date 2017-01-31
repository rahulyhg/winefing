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
    function findOneByUserId($userId)
    {
        $query = $this->createQueryBuilder('domain')
            ->join("domain.user", "user")
            ->where('user.id = :userId')
            ->setParameter('userId', $userId)
            ->setMaxResults(1)
            ->getQuery();
        return $query->getResult();
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
}
