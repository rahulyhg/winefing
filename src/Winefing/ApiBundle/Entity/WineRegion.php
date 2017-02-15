<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
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
     * @Groups({"id", "default"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Country")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"country"})
     */
    private $country;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\WineRegionTr", mappedBy="wineRegion", fetch="EAGER", cascade={"all"})
     * @Groups({"trs"})
     */
    private $wineRegionTrs;

    /**
     * @var Domain
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\Domain", mappedBy="wineRegion", fetch="EAGER", cascade="ALL")
     * @Groups({"domains"})
     */
    private $domains;

    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $picture;

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

    public function __construct()
    {
        $this->domains = new ArrayCollection();
        $this->wineRegionTrs = new ArrayCollection();
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
        $this->domains = new ArrayCollection();
    }

    public function getDisplayName($language){
        foreach($this->wineRegionTrs as $wineRegionTr){
            if($wineRegionTr->getLanguage()->getCode() == $language) {
                return $wineRegionTr->getName();
                break;
            }
        }
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
    public function setTr($language)
    {
        foreach($this->getWineRegionTrs() as $tr) {
            if($tr->getLanguage()->getCode() == $language) {
                $this->name = $tr->getName();
                $this->description = $tr->getDescription();
                break;
            }
        }
    }

    /**
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    /**
     * @return Domain
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @param Domain $domains
     */
    public function setDomains($domains)
    {
        $this->domains = $domains;
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


}

