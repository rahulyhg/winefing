<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\CharacteristicCategory;

/**
 * Scope
 *
 * @ORM\Table(name="scope")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\ScopeRepository")
 */
class Scope
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
     * @ORM\Column(name="name", type="string", length=25, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var CharacteristicCategory
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\CharacteristicCategory", mappedBy="scope", fetch="EAGER")
     */
    private $characteristicCategories;

    public function __construct() {
        $this->characteristicCategories = new ArrayCollection();
    }

    public function addCharacteristicCategory(CharacteristicCategory $characteristicCategory)
    {
      $this->characteristicCategories[] = $characteristicCategory;

      return $this;
    }

    public function removeCharacteristicCategory(CharacteristicCategory $characteristicCategory)
    {
      $this->characteristicCategories->removeElement($characteristicCategory);
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
     * Set name
     *
     * @param string $name
     *
     * @return Scope
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
     * @return Scope
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
     * @return CharacteristicCategories
     */
    public function getCharacteristicCategories()
    {
        return $this->characteristicCategories;
    }
}

