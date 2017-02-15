<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * WineRegionTr
 *
 * @ORM\Table(name="wine_region_tr")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\WineRegionTrRepository")
 */
class WineRegionTr
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
     * @var
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\WineRegion", inversedBy="wineRegionTrs")
     * @Groups({"wineRegion"})
     */
    private $wineRegion;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Language")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"language"})
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
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

    /**
     * Set name
     *
     * @param string $name
     *
     * @return WineRegionTr
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
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
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
}

