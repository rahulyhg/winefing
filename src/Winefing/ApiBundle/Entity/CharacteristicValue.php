<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
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
     * @Groups({"id", "default"})
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Domain", mappedBy="characteristicValues", fetch="EXTRA_LAZY", cascade={"persist", "merge", "detach"})
     * @Groups({"domains"})
     */
    private $domains;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Property", mappedBy="characteristicValues", fetch="EXTRA_LAZY", cascade={"persist", "merge", "detach"})
     * @Groups({"properties"})
     */
    private $properties;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Rental", mappedBy="characteristicValues", fetch="EXTRA_LAZY", cascade={"persist", "merge", "detach"})
     * @Groups({"properties"})
     */
    private $rentals;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Characteristic")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $characteristic;

    /**
     * @var string
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     * @Groups({"default"})
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
        $rental->addCharacteristicValue($this);
        $this->rentals[] = $rental;
        return $this;
    }
    public function setTr($language) {
        $this->getCharacteristic()->setTr($language);
        $this->getCharacteristic()->getCharacteristicCategory()->setTr($language);
    }
}

