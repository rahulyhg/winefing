<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * PropertyCategory
 *
 * @ORM\Table(name="property_category")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\PropertyCategoryRepository")
 */
class PropertyCategory
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
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @var PropertyCategoryTrs
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\PropertyCategoryTr", mappedBy="propertyCategory", fetch="EAGER", cascade="ALL")
     */
    private $propertyCategoryTrs;

    /**
     * @var PropertyCategoryTrs
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\Property", mappedBy="propertyCategory", fetch="EAGER", cascade="ALL")
     */
    private $properties;

    public function _construct(){
        $this->propertyCategoryTrs[] = new ArrayCollection();
        $this->properties[] = new ArrayCollection();
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
     * Set code
     *
     * @param string $code
     *
     * @return PropertyCategory
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return PropertyCategoryTrs
     */
    public function getProperties()
    {
        return $this->properties;
    }
}

