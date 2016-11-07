<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\WineRegion")
     * @ORM\JoinColumn(nullable=false)
     */
    private $wineRegion;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Address", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false)
     */
    private $address;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\User", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=60)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=500, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="history", type="string", length=500, nullable=true)
     */
    private $history;


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
    public function setWineRegion($wineRegion)
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

}

