<?php

namespace Winefing\ApiBundle\Repository;

/**
 * RentalPromotionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RentalPromotionRepository extends \Doctrine\ORM\EntityRepository
{
    function findConflictForRental($startDate, $endDate, $rentalId)
    {
        $startDate = date("Y-m-d", strtotime($startDate));
        $endDate = date("Y-m-d", strtotime($endDate));
        $query = $this->createQueryBuilder('rentalPromotion')
            ->select("rental.name")
            ->join("rentalPromotion.rentals", "rental")
            ->where('rental.id = :rentalId and ((rentalPromotion.startDate BETWEEN :startDate and :endDate) or (rentalPromotion.endDate BETWEEN :startDate and :endDate) or (rentalPromotion.startDate < :startDate and rentalPromotion.endDate > :endDate) )')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('rentalId', $rentalId)
            ->setMaxResults(1)
            ->getQuery();
        return $query->getResult();
    }
    function findByUser($userId){
        $query = $this->createQueryBuilder('rentalPromotion')
            ->join("rentalPromotion.rentals", "rental")
            ->join("rental.property", "property")
            ->join("property.domain", "domain")
            ->join("domain.user", "user")
            ->where('user.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery();
        return $query->getResult();
    }
    function findCurrentPromotionForRental($rentalId){
        $query = $this->createQueryBuilder('rentalPromotion')
            ->join("rentalPromotion.rentals", "rental")
            ->where('rental.id = :rentalId and rentalPromotion.startDate >= :todayDate')
            ->setParameter('rentalId', $rentalId)
            ->setParameter('todayDate', date("Y-m-d"))
            ->getQuery();
        return $query->getResult();
    }
}
