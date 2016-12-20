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
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\BoxItem", mappedBy="boxes", cascade={"persist", "merge", "detach"})
     */
    private $boxItems;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Media", cascade={"persist", "merge", "detach"})
     */
    private $medias;

    /**
     * @var BoxTrs
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\BoxTr", mappedBy="box", fetch="EAGER", cascade="ALL")
     * @Groups({"boxTrs"})
     */
    private $boxTrs;

    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $mediaPresentation;

    public function _construct() {
        $this->boxItems = new ArrayCollection();
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
    public function deleteBoxItem(BoxItem $boxItem) {
        $this->boxItems->removeElement($boxItem);
        return $this;
    }
}

