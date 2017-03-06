<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * DayPrice
 *
 * @ORM\Table(name="day_price")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\DayPriceRepository")
 */
class DayPrice
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\RentalOrder", inversedBy="dayPrices")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"rentalOrder"})
     */
    private $rentalOrder;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     * @Groups({"default"})
     */
    private $date;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     * @Groups({"default"})
     */
    private $price;


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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return DayPriceOrder
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return DayPriceOrder
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
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
}

