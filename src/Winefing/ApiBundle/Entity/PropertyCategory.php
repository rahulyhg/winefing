<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
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
     * @Groups({"id", "default"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $code;

    /**
     * @var PropertyCategoryTrs
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\PropertyCategoryTr", mappedBy="propertyCategory", fetch="EAGER", cascade="ALL")
     * @Groups({"trs"})
     */
    private $propertyCategoryTrs;

    /**
     * @var PropertyCategoryTrs
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\Property", mappedBy="propertyCategory", fetch="EAGER", cascade="ALL")
     * @Groups({"properties"})
     */
    private $properties;

    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $name;

    public function __construct(){
        $this->propertyCategoryTrs = new ArrayCollection();
        $this->properties = new ArrayCollection();
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

    /**
     * @return PropertyCategoryTrs
     */
    public function getPropertyCategoryTrs()
    {
        return $this->propertyCategoryTrs;
    }
    public function addPropertyCategoryTr(PropertyCategoryTr $propertyCategoryTr) {
        $this->propertyCategoryTrs[] = $propertyCategoryTr;
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
        foreach($this->getPropertyCategoryTrs() as $tr) {
            if($tr->getLanguage()->getCode() == $language) {
                $this->name = $tr->getName();
                break;
            }
        }
    }
    public function getDisplayName($language){
        foreach($this->propertyCategoryTrs as $tr){
            if($tr->getLanguage()->getCode() == $language) {
                return $tr->getName();
                break;
            }
        }
    }
}

