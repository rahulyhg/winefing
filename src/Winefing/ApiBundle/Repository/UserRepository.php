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

}