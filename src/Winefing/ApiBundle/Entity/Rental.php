<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

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
     * @Groups({"id", "default"})
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\RentalOrder", mappedBy="rental")
     * @Groups({"rentalOrders"})
     */
    private $rentalOrders;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\CharacteristicValue", inversedBy="rentals")
     * @Groups({"characteristicValues"})
     */
    private $characteristicValues;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Media", inversedBy="rentals", cascade={"persist", "merge", "detach"}, fetch="EXTRA_LAZY")
     * @Groups({"medias"})
     */
    private $medias;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Property", inversedBy="rentals", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"property"})
     */
    private $property;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=60, nullable=true)
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
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $mediaPresentation;

    /**
     * @var float
     * @ORM\Column(name="price", type="decimal", precision=6, scale=2, nullable=true)
     * @Groups({"default"})
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(name="peopleNumber", type="integer", nullable=true)
     * @Groups({"default"})
     */
    private $peopleNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="minimumRentalPeriod", type="integer", nullable=true)
     * @Groups({"default"})
     */
    private $minimumRentalPeriod;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\RentalPromotion", mappedBy="rentals", fetch="EXTRA_LAZY", cascade={"persist", "merge", "detach"})
     */
    private $rentalPromotions;

    /**
     * @var string
     *
     * @ORM\Column(name="rentalCategory", type="string", length=255)
     * @Groups({"default"})
     */
    private $rentalCategory;

    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $characteristicValuesByCategory;

    /**
     * @var bool
     *
     * @ORM\Column(name="activated", type="boolean")
     * @Groups({"default"})
     */
    private $activated = 1;


    public function addRentalPromotion(RentalPromotion $rentalPromotion) {
        $this->rentalPromotions[] = $rentalPromotion;
    }

    public function __construct() {
        $this->characteristicValues = new ArrayCollection();
        $this->medias = new ArrayCollection();
        $this->rentalPromotions = new ArrayCollection();
        $this->rentalOrders = new ArrayCollection();
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
        $this->characteristicValues[] = $characteristicValue;
    }

    public function addMedia(Media $media) {
        $this->medias[] = $media;
        return $this;
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getPeopleNumber()
    {
        return $this->peopleNumber;
    }

    /**
     * @param int $peopleNumber
     */
    public function setPeopleNumber($peopleNumber)
    {
        $this->peopleNumber = $peopleNumber;
    }

    /**
     * @return int
     */
    public function getMinimumRentalPeriod()
    {
        return $this->minimumRentalPeriod;
    }

    /**
     * @param int $minimumRentalPeriod
     */
    public function setMinimumRentalPeriod($minimumRentalPeriod)
    {
        $this->minimumRentalPeriod = $minimumRentalPeriod;
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
    public function getRentalOrders()
    {
        return $this->rentalOrders;
    }

    /**
     * @param mixed $rentalOrders
     */
    public function setRentalOrders($rentalOrders)
    {
        $this->rentalOrders = $rentalOrders;
    }
    public function setTr($language) {
        foreach($this->characteristicValues as $characteristicValue) {
            $characteristicValue->getCharacteristic()->setTr($language);
            $characteristicValue->getCharacteristic()->getCharacteristicCategory()->setTr($language);
        }
    }
    public function getCharacteristicValuesActivated() {
        $characteristicValuesActivated = new ArrayCollection();
        foreach($this->getCharacteristicValues() as $characteristicValue) {
            if($characteristicValue->getCharacteristic()->getActivated()) {
                $characteristicValuesActivated[] = $characteristicValue;
            }
        }
        return $characteristicValuesActivated;
    }

    /**
     * @return mixed
     */
    public function getRentalPromotions()
    {
        return $this->rentalPromotions;
    }

    /**
     * @param mixed $rentalPromotions
     */
    public function setRentalPromotions($rentalPromotions)
    {
        $this->rentalPromotions = $rentalPromotions;
    }

    /**
     * @return string
     */
    public function getRentalCategory()
    {
        return $this->rentalCategory;
    }

    /**
     * @param string $rentalCategory
     */
    public function setRentalCategory($rentalCategory)
    {
        $this->rentalCategory = $rentalCategory;
    }

    /**
     * @return mixed
     */
    public function getCharacteristicValuesByCategory()
    {
        return $this->characteristicValuesByCategory;
    }

    /**
     * @param mixed $characteristicValuesByCategory
     */
    public function setCharacteristicValuesByCategory($characteristicValuesByCategory)
    {
        $this->characteristicValuesByCategory = $characteristicValuesByCategory;
    }

    /**
     * @return boolean
     */
    public function isActivated()
    {
        return $this->activated;
    }

    /**
     * @param boolean $activated
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;
    }
}

