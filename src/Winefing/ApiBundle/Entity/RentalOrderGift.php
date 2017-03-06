<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
/**
 * RentalOrderGift
 *
 * @ORM\Table(name="rental_order_gift")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\RentalOrderGiftRepository")
 */
class RentalOrderGift
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"id","default"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=500)
     * @Groups({"default"})
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="float")
     * @Groups({"default"})
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="signature", type="string", length=255)
     * @Groups({"default"})
     */
    private $signature;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Address", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"address"})
     */
    private $address;

    /**
     * @ORM\OneToOne(targetEntity="Winefing\ApiBundle\Entity\RentalOrder", mappedBy="rentalOrderGift")
     * @ORM\JoinColumn(nullable=true)
     */
    private $rentalOrder;


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
     * Set message
     *
     * @param string $message
     *
     * @return RentalOrderGift
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set signature
     *
     * @param string $signature
     *
     * @return RentalOrderGift
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return RentalOrderGift
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return mixed
     */
    public function getRentalOrder()
    {
        return $this->rentalOrder;
    }

    /**
     * @param mixed $rentalOrder
     */
    public function setRentalOrder($rentalOrder)
    {
        $this->rentalOrder = $rentalOrder;
    }

    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }
}

