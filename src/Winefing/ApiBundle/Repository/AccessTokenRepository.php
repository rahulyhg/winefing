<?php

namespace Winefing\ApiBundle\Repository;

/**
 * AddressRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AccessTokenRepository extends \Doctrine\ORM\EntityRepository
{
    function findOneByUser($userId)
    {
        $query = $this->createQueryBuilder('accessToken')
            ->join("accessToken.user", "user")
            ->where('user.id = :userId')
            ->orderBy('accessToken.id', 'DESC')
            ->setParameter('userId', $userId)
            ->setMaxResults(1)
            ->getQuery();
        return $query->getOneOrNullResult();
    }
}