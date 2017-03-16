<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
/**
 * RentalPromotion
 *
 * @ORM\Table(name="rental_promotion")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\RentalPromotionRepository")
 */
class RentalPromotion
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
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="date")
     * @Groups({"default"})
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="date")
     * @Groups({"default"})
     */
    private $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="reduction", type="decimal", precision=4, scale=2)
     * @Groups({"default"})
     */
    private $reduction;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Rental", inversedBy="rentalPromotions", fetch="EXTRA_LAZY", cascade={"persist", "merge", "detach"})
     * @Groups({"rentals"})
     */
    private $rentals;

    public function __construct() {
        $this->rentals = new ArrayCollection();
        return $this;
    }
    public function addRental(Rental $rental) {
        $rental->addRentalPromotion($this);
        $this->rentals[] = $rental;
    }
    /**
     * @return mixed
     */
    public function getRentals()
    {
        return $this->rentals;
    }
    public function setRentals($rentals) {
        $this->rentals = $rentals;
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
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return RentalPromotion
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return RentalPromotion
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set reduction
     *
     * @param string $reduction
     *
     * @return RentalPromotion
     */
    public function setReduction($reduction)
    {
        $this->reduction = $reduction;

        return $this;
    }

    /**
     * Get reduction
     *
     * @return string
     */
    public function getReduction()
    {
        return $this->reduction;
    }

    public function resetRentals() {
        $this->rentals = new ArrayCollection();
        return $this;
    }
}

