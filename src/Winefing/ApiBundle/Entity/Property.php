<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * Property
 *
 * @ORM\Table(name="property")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\PropertyRepository")
 */
class Property
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Domain", inversedBy="properties")
     * @ORM\JoinColumn(nullable=false)
     * @Type("Winefing\ApiBundle\Entity\Domain")
     * @Expose
     * @Groups({"domain"})
     */
    private $domain;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\CharacteristicValue", inversedBy="properties", fetch="LAZY")
     * @Groups({"characteristicValues"})
     */
    private $characteristicValues;

    /**
     * @var Rentals
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\Rental", mappedBy="property", fetch="LAZY", cascade="ALL")
     * @Groups({"rentals"})
     */
    private $rentals;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Media", inversedBy="properties", fetch="LAZY", cascade={"persist", "merge", "detach"})
     * @Groups({"medias"})
     */
    private $medias;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Address", inversedBy="properties", fetch="LAZY")
     * @ORM\JoinColumn(nullable=false)
     * @Type("Winefing\ApiBundle\Entity\Address")
     * @Groups({"address"})
     */
    private $address;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\PropertyCategory", inversedBy="properties")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"propertyCategory"})
     */
    private $propertyCategory;

    /**
     * @Groups({"default"})
     * @Type("string")
     */
    private $mediaPresentation;

    /**
     * @Groups({"default"})
     * @Type("boolean")
     *
     */
    private $isAddressDomain = false;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=60, nullable=true)
     * @Groups({"default"})
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $description;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function _construct() {
        $this->rentals = new ArrayCollection();
        $this->characteristicValues = new ArrayCollection();
        $this->medias = new ArrayCollection();
        return $this;
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

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Property
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
     * @return Property
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
    public function getPropertyCategory()
    {
        return $this->propertyCategory;
    }

    /**
     * @param mixed $propertyCategory
     */
    public function setPropertyCategory($propertyCategory)
    {
        $this->propertyCategory = $propertyCategory;
    }

    /**
     * @return mixed
     */
    public function getCharacteristicValues()
    {
        return $this->characteristicValues;
    }

    /**
     * @param mixed $characteristicValue
     */
    public function addCharacteristicValue(CharacteristicValue $characteristicValue)
    {
        $this->characteristicValues[] = $characteristicValue;
    }

    /**
     * @return mixed
     */
    public function setMediaPresentation()
    {
        if(count($this->medias) > 0) {
            foreach ($this->medias as $media) {
                if ($media->isPresentation()) {
                    $this->mediaPresentation = $media->getName();
                    break;

                }
            }
            if(empty($this->mediaPresentation)) {
                $this->mediaPresentation = $this->medias[0]->getName();

            }
        } else {
            $this->mediaPresentation = 'default.png';
        }
        return $this->mediaPresentation;
    }

    /**
     * @param mixed $mediaPresentation
     */
    public function getMediaPresentation()
    {
        return $this->mediaPresentation;
    }

    /**
     * @return Rentals
     */
    public function getRentals()
    {
        return $this->rentals;
    }

    /**
     * @return mixed
     */
    public function isAddressDomain()
    {

        return $this->isAddressDomain;
    }

    /**
     * @param mixed $isAddressDomain
     */
    public function setIsAddressDomain()
    {
        if($this->address->getId() == $this->domain->getAddress()->getId()) {
            $this->isAddressDomain = true;
        }
        return $this;
    }
}