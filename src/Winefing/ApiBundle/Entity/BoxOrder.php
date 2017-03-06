<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
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
     * @Groups({"id", "default"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Box")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"box"})
     */
    private $box;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"user"})
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\BoxItemChoice", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"boxOrderItemChoices"})
     */
    private $boxItemChoices;
    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Address", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"billingAddress"})
     */
    private $billingAddress;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Address", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"deliveringAddress"})
     */
    private $deliveringAddress;

    /**
     * @var float
     * @Groups({"default"})
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    public function __construct(Box $box, User $user)
    {
        $this->boxItemChoices = new ArrayCollection();
        $this->user = $user;
        $this->box = $box;
        $this->price = $box->getPrice();
    }


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
    /**
     * @return mixed
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param mixed $billingAddress
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = clone $billingAddress;
    }

    /**
     * @return mixed
     */
    public function getDeliveringAddress()
    {
        return $this->deliveringAddress;
    }

    /**
     * @param mixed $deliveringAddress
     */
    public function setDeliveringAddress($deliveringAddress)
    {
        $this->deliveringAddress = clone $deliveringAddress;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getBoxItemChoices()
    {
        return $this->boxItemChoices;
    }

    public function addBoxItemChoice(BoxItemChoice $boxItemChoice) {
        $this->boxItemChoices[] = $boxItemChoice;
    }


}

