<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Rental
 *
 * @ORM\Table(name="rental")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\RentalRepository")
 */
class Rental
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
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\CharacteristicValue", inversedBy="rentals")
     */
    private $characteristicValues;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Media", inversedBy="rentals", cascade={"persist", "merge", "detach"})
     */
    private $medias;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Property")
     * @ORM\JoinColumn(nullable=false)
     */
    private $property;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=60, nullable=true)
     */
    private $name;

    public function _construct() {
        $this->characteristicValues = new ArrayCollection();
        $this->medias = new ArrayCollection();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param mixed $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
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
     * Set name
     *
     * @param string $name
     *
     * @return Rental
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return CharacteristicValues
     */
    public function getCharacteristicValues()
    {
        return $this->characteristicValues;
    }

    public function addCharacteristicValue(CharacteristicValue $characteristicValue) {
        $this->characteristicValue[] = $characteristicValue;
        return $this;
    }

    public function addMedia(Media $media) {
        $this->medias[] = $media;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMedias()
    {
        return $this->medias;
    }
}

