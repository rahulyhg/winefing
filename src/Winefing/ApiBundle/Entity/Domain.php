<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;

/**
 * Domain
 *
 * @ORM\Table(name="domain")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\DomainRepository")
 */
class Domain
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"default"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\WineRegion")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $wineRegion;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Media", inversedBy="domains", cascade={"persist", "merge", "detach"})
     * @Groups({"medias"})
     */
    private $medias;

    /**
     * @var Properties
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\Property", mappedBy="domain", fetch="EXTRA_LAZY", cascade="ALL")
     * @Groups({"properties"})
     */
    private $properties;


    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Address", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"address"})
     */
    private $address;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\User", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"user"})
     *
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\CharacteristicValue", inversedBy="domains")
     * @Groups({"characteristicValues"})
     */
    private $characteristicValues;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=60)
     * @Groups({"default"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=500, nullable=true)
     * @Groups({"default"})
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="history", type="string", length=500, nullable=true)
     * @Groups({"default"})
     */
    private $history;

    public function _construct() {
        $this->medias = new ArrayCollection();
        $this->characteristicValues = new ArrayCollection();
        $this->properties = new ArrayCollection();
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
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Domain
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
     * Set description
     *
     * @param string $description
     *
     * @return Domain
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set history
     *
     * @param string $history
     *
     * @return Domain
     */
    public function setHistory($history)
    {
        $this->history = $history;

        return $this;
    }

    /**
     * Get history
     *
     * @return string
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @return mixed
     */
    public function getWineRegion()
    {
        return $this->wineRegion;
    }

    /**
     * @param mixed $wineRegion
     */
    public function setWineRegion($wineRegion)
    {
        $this->wineRegion = $wineRegion;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
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
    public function getMedias()
    {
        return $this->medias;
    }
    public function addMedia(Media $media) {
        $this->medias[] = $media;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProperties()
    {
        return $this->properties;
    }

    public function addProperty(Property $property) {
        $this->properties[] = $property;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCharacteristicValues()
    {
        return $this->characteristicValues;
    }
    public function addCharacteristicValue(CharacteristicValue $characteristicValue) {
        $this->characteristicValues[] = $characteristicValue;
        return $this;
    }
    public function setTr($language) {
        foreach($this->getCharacteristicValues() as $characteristicValue) {
            $characteristicValue->setTr($language);
        }
    }
}

