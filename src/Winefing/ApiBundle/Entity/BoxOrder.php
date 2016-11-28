<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BoxOrder
 *
 * @ORM\Table(name="box_order")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\BoxOrderRepository")
 */
class BoxOrder
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Box")
     * @ORM\JoinColumn(nullable=false)
     */
    private $box;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getBox()
    {
        return $this->box;
    }

    /**
     * @param mixed $box
     */
    public function setBox($box)
    {
        $this->box = $box;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}

