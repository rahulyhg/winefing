<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * WineRegion
 *
 * @ORM\Table(name="wine_region")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\WineRegionRepository")
 */
class WineRegion
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Country")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\WineRegionTr", mappedBy="wineRegion", fetch="EAGER", cascade={"all"})
     */
    private $wineRegionTrs;

    private $title;


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
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getWineRegionTrs()
    {
        return $this->wineRegionTrs;
    }

    public function addWineRegionTr(WineRegionTr $wineRegionTr) {
        $this->wineRegionTrs[] = $wineRegionTr;
        $wineRegionTr->setWineRegion($this);
        return $this;
    }

    public function _construct() {
        $this->wineRegionTrs = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

}

