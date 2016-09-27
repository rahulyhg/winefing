<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * CharacteristicCategory
 *
 * @ORM\Table(name="characteristic_category")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\CharacteristicCategoryRepository")
 */
class CharacteristicCategory
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Scope", inversedBy="characteristicCategories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $scope;

    /**
     * @var Characteristic
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\Characteristic", mappedBy="characteristicCategory", fetch="EAGER")
     */
    private $characteristics;

    /**
     * @var CharacteristicCategoryTr
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\CharacteristicCategoryTr", mappedBy="characteristicCategory", fetch="EAGER", cascade="ALL")
     */
    private $characteristicCategoryTrs;

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param mixed $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @var bool
     *
     * @ORM\Column(name="activated", type="boolean")
     */
    private $activated;

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
     * Set description
     *
     * @param string $description
     *
     * @return CharacteristicCategory
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
     * Set activated
     *
     * @param boolean $activated
     *
     * @return CharacteristicCategory
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * Get activated
     *
     * @return bool
     */
    public function getActivated()
    {
        return $this->activated;
    }

    /**
     * @return Characteristic
     */
    public function getCharacteristics()
    {
        return $this->characteristics;
    }

    /**
     * @return CharacteristicCategoryTr
     */
    public function getCharacteristicCategoryTrs()
    {
        return $this->characteristicCategoryTrs;
    }

    public function __construct() {
        $this->characteristicCategoryTrs = new ArrayCollection();
    }

    public function addCharacteristicCategoryTr(CharacteristicCategoryTr $characteristicCategoryTr)
    {
        $this->characteristicCategoryTrs[] = $characteristicCategoryTr;
        $characteristicCategoryTr->setCharacteristicCategory($this);
        return $this;
    }

    public function removeCharacteristicCategoryTr(CharacteristicCategoryTr $characteristicCategoryTr)
    {
        $this->characteristicCategoryTrs->removeElement($characteristicCategoryTr);
    }
}

