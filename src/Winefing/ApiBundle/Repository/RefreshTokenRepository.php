<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 29/01/2017
 * Time: 20:32
 */

namespace Winefing\ApiBundle\Repository;


class RefreshTokenRepository extends \Doctrine\ORM\EntityRepository{
    function findOneByUser($userId)
    {
        $query = $this->createQueryBuilder('refreshToken')
            ->join("refreshToken.user", "user")
            ->where('user.id = :userId')
            ->orderBy('refreshToken.id', 'DESC')
            ->setParameter('userId', $userId)
            ->setMaxResults(1)
            ->getQuery();
        return $query->getOneOrNullResult();
    }
}