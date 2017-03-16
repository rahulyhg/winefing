<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 05/10/2016
 * Time: 13:59
 */

namespace Winefing\ApiBundle\Repository;
use Winefing\ApiBundle\Entity\UserGroupEnum;

class UserRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAdmin()
    {
        $query = $this->createQueryBuilder('user')
            ->where('user.roles like :blog or user.roles like :managment')
            ->setParameter('blog', '%'.UserGroupEnum::Blog.'%')
            ->setParameter('managment', '%'.UserGroupEnum::Managment.'%')
            ->getQuery();
        $users = $query->getResult();
        return $users;
    }
    public function findHost()
    {
        $query = $this->createQueryBuilder('user')
            ->where('user.roles like :host')
            ->setParameter('host', '%'.UserGroupEnum::Host.'%')
            ->getQuery();
        $users = $query->getResult();
        return $users;
    }
    public function findOneWithUserAndDomain($userId, $domainId) {
        $query = $this->createQueryBuilder('user')
            ->join('user.wineList', 'domain')
            ->where('domain.id = :domainId and user.id = :userId')
            ->setParameter('domainId', $domainId)
            ->setParameter('userId', $userId)
            ->setMaxResults(1)
            ->getQuery();
        return $query->getOneOrNullResult();
    }

    public function findOneWithRental($rental) {
        $query = $this->createQueryBuilder('user')
            ->join('user.domains', 'domain')
            ->join('domain.properties', 'property')
            ->join('property.rentals', 'rental')
            ->where('rental.id = :rentalId')
            ->setParameter('rentalId', $rental)
            ->setMaxResults(1)
            ->getQuery();
        return $query->getOneOrNullResult();
    }
}