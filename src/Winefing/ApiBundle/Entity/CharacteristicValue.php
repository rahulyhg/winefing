<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * CharacteristicValue
 *
 * @ORM\Table(name="characteristic_value")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\CharacteristicValueRepository")
 */
class CharacteristicValue
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
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Domain", mappedBy="characteristicValues", cascade={"persist", "merge", "detach"})
     */
    private $domains;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Property", mappedBy="characteristicValues", cascade={"persist", "merge", "detach"})
     */
    private $properties;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Rental", mappedBy="characteristicValues", cascade={"persist", "merge", "detach"})
     */
    private $rentals;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Characteristic")
     * @ORM\JoinColumn(nullable=false)
     */
    private $characteristic;

    /**
     * @var string
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     */
    private $value;

    public function _construct(){
        $this->properties[] = new ArrayCollection();
        $this->rentals[] = new ArrayCollection();
        $this->domains[] = new ArrayCollection();
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
    public function getCharacteristic()
    {
        return $this->characteristic;
    }

    /**
     * @param mixed $characteristic
     */
    public function setCharacteristic($characteristic)
    {
        $this->characteristic = $characteristic;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getDomains()
    {
        return $this->domains;
    }
    public function addDomain(Domain $domain) {
        $domain->addCharacteristicValue($this);
        $this->domains[] = $domain;
        return $this;
    }

    public function addProperty(Property $property) {
        $property->addCharacteristicValue($this);
        $this->properties[] = $property;
        return $this;
    }

    public function addRental(Rental $rental) {
        $rental->addCharacteristicRentalValue($this);
        $this->rentals[] = $rental;
        return $this;
    }
}

