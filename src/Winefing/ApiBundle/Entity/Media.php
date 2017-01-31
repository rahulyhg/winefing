<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;

/**
 * Media
 *
 * @ORM\Table(name="media")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\MediaRepository")
 */
class Media
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"id", "default"})
     *
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Groups({"default"})
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="presentation", type="boolean")
     * @Groups({"default"})
     */
    private $presentation = 0;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Domain", mappedBy="medias", fetch="EXTRA_LAZY", cascade={"persist", "merge", "detach"})
     * @Groups({"domains"})
     */
    private $domains;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Property", mappedBy="medias", fetch="EXTRA_LAZY", cascade={"persist", "merge", "detach"})
     * @Groups({"properties"})
     */
    private $properties;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Rental", mappedBy="medias", fetch="EXTRA_LAZY", cascade={"persist", "merge", "detach"})
     * @Groups({"rentals"})
     */
    private $rentals;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Box", mappedBy="medias", fetch="EXTRA_LAZY")
     * @Groups({"boxes"})
     */
    private $boxes;

    public function addDomain(Domain $domain) {
        $this->domains[] = $domain;
        $domain->addMedia($this);
        return $this;
    }
    public function addProperty(Property $property) {
        $this->properties[] = $property;
        $property->addMedia($this);
        return $this;
    }
    public function addRental(Rental $rental) {
        $this->rentals[] = $rental;
        $rental->addMedia($this);
        return $this;
    }
    public function addBox(Box $box) {
        $this->boxes[] = $box;
        $box->addMedia($this);
        return $this;
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return boolean
     */
    public function isPresentation()
    {
        return $this->presentation;
    }

    /**
     * @param boolean $presentation
     */
    public function setPresentation($presentation)
    {
        $this->presentation = $presentation;
    }


    /**
     * @return mixed
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @return mixed
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return mixed
     */
    public function getRentals()
    {
        return $this->rentals;
    }

}

