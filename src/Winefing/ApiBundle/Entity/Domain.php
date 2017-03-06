<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

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
     * @Groups({"id","default"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\WineRegion", inversedBy="domains", cascade={"persist", "merge", "detach"})
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
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Tag", inversedBy="domains", fetch="EXTRA_LAZY")
     * @Groups({"tags"})
     */
    private $tags;

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
     * @ORM\Column(name="description", type="string", length=3000, nullable=true)
     * @Groups({"default"})
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="history", type="string", length=3000, nullable=true)
     * @Groups({"default"})
     */
    private $history;

    /**
     * @Groups({"default"})
     * @Type("string")
     */
    private $mediaPresentation;

    /**
     * @Groups({"domainMediasPresentation"})
     * @Type("Winefing\ApiBundle\Entity\DomainMediasPresentation")
     */
    private $domainMediasPresentation;

    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $characteristicValuesByCategory;

    /**
     * @Type("Winefing\ApiBundle\Entity\DomainStatistic")
     * @Groups({"stat"})
     */
    private $domainStatistic;

    public function __construct() {
        $this->medias = new ArrayCollection();
        $this->characteristicValues = new ArrayCollection();
        $this->properties = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    /**
     * @param Properties $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
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
    public function setWineRegion(WineRegion $wineRegion)
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
        //set wine Region
        $this->wineRegion->setTr($language);
        //set tags
        foreach($this->tags as $tag) {
            $tag->setTr($language);
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
    public function setMediaPresentation()
    {
        if(count($this->medias) > 0) {
            foreach ($this->medias as $media) {
                if ($media->isPresentation()) {
                    $this->mediaPresentation = $media->getName();
                    $this->medias->removeElement($media);
                    break;

                }
            }
            if(empty($this->mediaPresentation)) {
                $this->mediaPresentation = $this->medias[0]->getName();
                $this->medias->removeElement($this->medias[0]);

            }
        } else {
            $this->mediaPresentation = 'default.png';
        }
        return $this->mediaPresentation;
    }

    /**
     * @param mixed
     */
    public function getMediaPresentation()
    {
        return $this->mediaPresentation;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

//    /**
//     * @param mixed $tags
//     */
//    public function setTags($tags)
//    {
//        $this->tags = $tags;
//    }
    public function addTag(Tag $tag) {
        $this->tags[] = $tag;
    }
    public function resetTags() {
        $this->tags = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getDomainMediasPresentation()
    {
        return $this->domainMediasPresentation;
    }

    /**
     * @param mixed $domainMediasPresentation
     */
    public function setDomainMediasPresentation($domainMediasPresentation)
    {
        $this->domainMediasPresentation = $domainMediasPresentation;
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
     * @return mixed
     */
    public function getDomainStatistic()
    {
        return $this->domainStatistic;
    }

    /**
     * @param mixed $domainStatistic
     */
    public function setDomainStatistic($domainStatistic)
    {
        $this->domainStatistic = $domainStatistic;
    }
}

