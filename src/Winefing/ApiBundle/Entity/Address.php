<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Address
 *
 * @ORM\Table(name="address")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\AddressRepository")
 */
class Address
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
     * @var domains
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\Domain", mappedBy="address", fetch="EXTRA_LAZY")
     */
    private $domains;

    /**
     * @var properties
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\Property", mappedBy="address", fetch="EXTRA_LAZY")
     */
    private $properties;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\User", fetch="EXTRA_LAZY", cascade={"persist", "merge", "detach"})
     * @Groups({"addresses"})
     */
    private $users;

    /**
     * @var string
     *
     * @ORM\Column(name="streetAddress", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $streetAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="route", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $route;

    /**
     * @var string
     *
     * @ORM\Column(name="additionalInformation", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $additionalInformation;

    /**
     * @var string
     *
     * @ORM\Column(name="political", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $political;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255)
     * @Groups({"default"})
     * @Assert\NotBlank()
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="locality", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $locality;

    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="float", nullable=true)
     * @Groups({"default"})
     */
    private $lat;

    /**
     * @var float
     *
     * @ORM\Column(name="lng", type="float", nullable=true)
     * @Groups({"default"})
     */
    private $lng;

    /**
     * @var string
     *
     * @ORM\Column(name="formattedAddress", type="string", length=255)
     * @Groups({"default"})
     * @Assert\NotBlank()
     */
    private $formattedAddress;


    public function __construct(){
        $this->users = new ArrayCollection();
    }
    public function __clone() {
        $this->id = null;
        $this->domains = new ArrayCollection();
        $this->properties = new ArrayCollection();
        $this->users = new ArrayCollection();
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
     * Set streetAddress
     *
     * @param string $streetAddress
     *
     * @return Address
     */
    public function setStreetAddress($streetAddress)
    {
        $this->streetAddress = $streetAddress;

        return $this;
    }

    /**
     * Get streetAddress
     *
     * @return string
     */
    public function getStreetAddress()
    {
        return $this->streetAddress;
    }

    /**
     * Set route
     *
     * @param string $route
     *
     * @return Address
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set political
     *
     * @param string $political
     *
     * @return Address
     */
    public function setPolitical($political)
    {
        $this->political = $political;

        return $this;
    }

    /**
     * Get political
     *
     * @return string
     */
    public function getPolitical()
    {
        return $this->political;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Address
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set postalCode
     *
     * @param string $postalCode
     *
     * @return Address
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set locality
     *
     * @param string $locality
     *
     * @return Address
     */
    public function setLocality($locality)
    {
        $this->locality = $locality;

        return $this;
    }

    /**
     * Get locality
     *
     * @return string
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * Set lat
     *
     * @param float $lat
     *
     * @return Address
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param float $lng
     *
     * @return Address
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set formattedAddress
     *
     * @param string $formattedAddress
     *
     * @return Address
     */
    public function setFormattedAddress($formattedAddress)
    {
        $this->formattedAddress = $formattedAddress;

        return $this;
    }

    /**
     * Get formattedAddress
     *
     * @return string
     */
    public function getFormattedAddress()
    {
        return $this->formattedAddress;
    }

    /**
     * @return domains
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @return properties
     */
    public function getProperties()
    {
        return $this->properties;
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
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }
    public function addUser(User $user) {
        $this->users[] = $user;
        return $this;
    }
    public function clearUsers() {
        $this->users = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }

    /**
     * @param string $additionalInformation
     */
    public function setAdditionalInformation($additionalInformation)
    {
        $this->additionalInformation = $additionalInformation;
    }


}

