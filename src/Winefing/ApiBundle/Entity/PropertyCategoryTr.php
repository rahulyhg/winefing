<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * PropertyCategoryTr
 *
 * @ORM\Table(name="property_category_tr")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\PropertyCategoryTrRepository")
 */
class PropertyCategoryTr
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Groups({"default"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Language")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"language"})
     */
    private $language;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\PropertyCategory", inversedBy="propertyCategoryTrs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"propertyCategory"})
     */
    private $propertyCategory;

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
     * @return PropertyCategoryTr
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
     * Set language
     *
     * @param string $language
     *
     * @return PropertyCategoryTr
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return mixed
     */
    public function getPropertyCategory()
    {
        return $this->propertyCategory;
    }

    /**
     * @param mixed $propertyCategory
     */
    public function setPropertyCategory($propertyCategory)
    {
        $this->propertyCategory = $propertyCategory;
    }
}

