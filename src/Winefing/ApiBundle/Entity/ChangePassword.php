<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 03/03/2017
 * Time: 16:58
 */

namespace Winefing\ApiBundle\Entity;


use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;

class ChangePassword
{
    /**
     * @SecurityAssert\UserPassword(
     *     message = "Wrong value for your current password"
     * )
     */
    protected $currentPassword;

    /**
     * @Type("string")
     */
    protected $password;

    /**
     * @return mixed
     */
    public function getCurrentPassword()
    {
        return $this->currentPassword;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }


}