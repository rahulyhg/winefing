<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 27/01/2017
 * Time: 09:25
 */

namespace Winefing\ApiBundle\Entity;


use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Client extends BaseClient
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}