<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
     */
    private $id;

    /**
     * @var BoxItems
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\BoxItem", mappedBy="box", fetch="EAGER", cascade="ALL")
     */
    private $boxItems;

//    /**
//     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Domain", cascade={"persist", "merge", "detach"})
//     */
//    private $domains;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Media", cascade={"persist", "merge", "detach"})
     */
    private $medias;

    /**
     * @var BoxTrs
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\BoxTr", mappedBy="box", fetch="EAGER", cascade="ALL")
     */
    private $boxTrs;

    public function _construct() {
        $this->boxItems[] = new ArrayCollection();
        $this->boxTrs = new ArrayCollection();
        $this->medias = new ArrayCollection();
        return $this;
    }

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;


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
}

