<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="format", type="string", length=255)
     */
    private $format;

    /**
     * @var boolean
     *
     * @ORM\Column(name="presentation", type="boolean")
     */
    private $presentation = 0;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Domain", mappedBy="medias")
     */
    private $domains;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Property", mappedBy="medias")
     */
    private $properties;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Rental", mappedBy="medias")
     */
    private $rentals;


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
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
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

