<?php

namespace Winefing\ApiBundle\Repository;

/**
 * BoxOrderRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BoxOrderRepository extends \Doctrine\ORM\EntityRepository
{
    function findWithUser($userId)
    {
        $query = $this->createQueryBuilder('boxOrder')
            ->join("boxOrder.invoiceInformation", "invoiceInformation")
            ->join("invoiceInformation.user", "user")
            ->where('user.id = :userId')
            ->orderBy('boxOrder.id', 'DESC')
            ->setParameter('userId', $userId)
            ->getQuery();
        return $query->getResult();
    }
}
