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

class DomainStatistic
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
     * @Type("Winefing\ApiBundle\Entity\Domain")
     */
    private $domain;

    public function __construct(Domain $domain) {
        $this->domain = $domain;
        $nbRental = 0;
        foreach($domain->getProperties() as $property) {
            $nbRental = $nbRental + $property->getRentals()->count();
        }
        $this->nbRental = $nbRental;
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

    public function setMinMaxPrice()
    {
        $i = 0;
        foreach ($this->domain->getProperties() as $property) {
            foreach ($property->getRentals() as $rental) {
                if ($i == 0) {
                    $this->minPrice = $rental->getprice();
                    $this->maxPrice = $rental->getprice();
                } else {
                    if ($rental->getprice() < $this->minPrice) {
                        $this->minPrice = $rental->getprice();
                    } elseif ($rental->getprice() > $this->maxPrice) {
                        $this->maxPrice = $rental->getprice();
                    }
                }
                $i++;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }
}