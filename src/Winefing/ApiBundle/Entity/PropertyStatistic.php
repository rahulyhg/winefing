<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 07/02/2017
 * Time: 15:44
 */

namespace Winefing\ApiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

class PropertyStatistic
{
    /**
     * @var string
     * @Groups({"default"})
     * @Type("integer")
     */
    private $nbRental;

    /**
     * @var float
     * @Groups({"default"})
     * @Type("float")
     */
    private $minPrice;

    /**
     * @var float
     * @Groups({"default"})
     * @Type("float")
     */
    private $maxPrice;

    /**
     * @var string
     * @Groups({"default"})
     * @Type("reservationNumber")
     */
    private $reservationNumber;

    /**
     * @Type("Winefing\ApiBundle\Entity\Property")
     */
    private $property;

    public function __construct(Property $property) {
        $this->property = $property;
        $this->nbRental = $property->getRentals()->count();
        $this->setMinMaxPrice();
    }

    /**
     * @return string
     */
    public function getNbRental()
    {
        return $this->nbRental;
    }

    /**
     * @param string $nbRental
     */
    public function setNbRental($nbRental)
    {
        $this->nbRental = $nbRental;
    }

    /**
     * @return float
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * @param float $minPrice
     */
    public function setMinPrice($minPrice)
    {
        $this->minPrice = $minPrice;
    }

    /**
     * @return float
     */
    public function getMaxPrice()
    {
        return $this->maxPrice;
    }

    /**
     * @param float $maxPrice
     */
    public function setMaxPrice($maxPrice)
    {
        $this->maxPrice = $maxPrice;
    }

    /**
     * @return string
     */
    public function getReservationNumber()
    {
        return $this->reservationNumber;
    }

    /**
     * @param string $reservationNumber
     */
    public function setReservationNumber($reservationNumber)
    {
        $this->reservationNumber = $reservationNumber;
    }

    public function setMinMaxPrice() {
        $i = 0;
        foreach($this->property->getRentals() as $rental) {
            if($i == 0) {
                $this->minPrice = $rental->getprice();
                $this->maxPrice = $rental->getprice();
            } else {
                if($rental->getprice() < $this->minPrice) {
                    $this->minPrice = $rental->getprice();
                } elseif($rental->getprice() > $this->maxPrice) {
                    $this->maxPrice = $rental->getprice();
                }
            }
            $i++;
        }
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param string $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }
}