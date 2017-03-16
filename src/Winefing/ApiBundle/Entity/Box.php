<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
/**
 * Box
 *
 * @ORM\Table(name="box")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\BoxRepository")
 */
class Box
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
     * @var BoxItems
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\BoxItem", mappedBy="box", fetch="EAGER", cascade="ALL")
     * @Groups({"default"})
     */
    private $boxItems;

    /**
     * @var BoxItems
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\BoxOrder", mappedBy="box", fetch="EXTRA_LAZY")
     */
    private $boxOrders;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Media", inversedBy="boxes", cascade={"persist", "merge", "detach"})
     * @Groups({"medias"})
     */
    private $medias;

    /**
     * @var BoxTrs
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\BoxTr", mappedBy="box", fetch="EAGER", cascade="ALL")
     * @Groups({"trs"})
     */
    private $boxTrs;

    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $mediaPresentation;

    /**
     * @var
     * @Groups({"default"})
     * @Type("boolean")
     */
    private $hasChoice = 0;

    /**
     * @var
     * @Groups({"default"})
     * @Type("integer")
     */
    private $boxOrdersNumber = 0;

    public function __construct() {
        $this->boxItems = new ArrayCollection();
        $this->boxTrs = new ArrayCollection();
        $this->medias = new ArrayCollection();
        $this->boxOrders = new ArrayCollection();
        return $this;
    }
//    public function __clone()
//    {
//        // TODO: Implement __clone() method.
//        $this->boxTrs(clone $this->boxTrs);
//        $this->boxItems(clone $this->boxItems);
//        $this->medias = new ArrayCollection();
//
//    }

    /**
     * @var float
     * @Groups({"default"})
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $name;

    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="activated", type="boolean")
     * @Groups({"default"})
     */
    private $activated = 1;

    /**
     * @return mixed
     */
    public function gethasChoice()
    {
        foreach($this->boxItems as $boxItem){
            if(count($boxItem->getBoxItemChoices())) {
                $this->hasChoice = true;
            }
        }
        return $this->hasChoice;
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
     * Set price
     *
     * @param float $price
     *
     * @return Box
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return BoxItems
     */
    public function getBoxItems()
    {
        return $this->boxItems;
    }

    public function addBoxItem(BoxItem $boxItem) {
        $this->boxItems[] = $boxItem;
        return $this;
    }

    /**
     * @return BoxTrs
     */
    public function getBoxTrs()
    {
        return $this->boxTrs;
    }

    public function addBoxTr(BoxTr $boxTr) {
        $this->boxTrs[] = $boxTr;
        return $this;
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
     * @param mixed $medias
     */
    public function setMedias($medias)
    {
        $this->medias = $medias;
    }

    /**
     * @param BoxTrs $boxTrs
     */
    public function setBoxTrs($boxTrs)
    {
        $this->boxTrs = $boxTrs;
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
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param mixed $mediaPresentation
     */
    public function getMediaPresentation()
    {
        return $this->mediaPresentation;
    }
    public function deleteBoxItem(BoxItem $boxItem) {
        $this->boxItems->removeElement($boxItem);
        return $this;
    }
    public function setTr($language) {
        foreach($this->boxTrs as $boxTr) {
            if($boxTr->getLanguage()->getCode() == $language) {
                $this->name = $boxTr->getName();
                $this->description = $boxTr->getDescription();
                break;
            }
        }
        foreach ($this->getBoxItems() as $boxItem) {
            $boxItem->setTr($language);
            foreach($boxItem->getBoxItemChoices() as $boxItemChoice) {
                $boxItemChoice->setTr($language);
            }
        }
    }

    /**
     * @param BoxItems $boxItems
     */
    public function setBoxItems($boxItems)
    {
        $this->boxItems = $boxItems;
    }

    /**
     * @return mixed
     */
    public function getBoxOrdersNumber()
    {
        return $this->boxOrdersNumber;
    }

    /**
     * @param mixed $boxOrdersNumber
     */
    public function setBoxOrdersNumber()
    {
        $this->boxOrdersNumber = $this->boxOrders->count();
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

