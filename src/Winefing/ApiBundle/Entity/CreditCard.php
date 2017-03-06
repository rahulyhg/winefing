<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * CreditCard
 *
 * @ORM\Table(name="credit_card")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\CreditCardRepository")
 */
class CreditCard
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"id", "default"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\User", inversedBy="creditCards")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"user"})
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="owner", type="string", length=255)
     * @Groups({"default"})
     */
    private $owner;
    /**
     * @var string
     *
     * @ORM\Column(name="lemonWayId", type="string", length=255)
     * @Groups({"default"})
     */
    private $lemonWayId;


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
     * Set lemonWayId
     *
     * @param string $lemonWayId
     *
     * @return CreditCard
     */
    public function setLemonWayId($lemonWayId)
    {
        $this->lemonWayId = $lemonWayId;

        return $this;
    }

    /**
     * Get lemonWayId
     *
     * @return string
     */
    public function getLemonWayId()
    {
        return $this->lemonWayId;
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

    /**
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

}

