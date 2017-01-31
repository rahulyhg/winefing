<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * CharacteristicCategoryTr
 *
 * @ORM\Table(name="characteristic_category_tr")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\CharacteristicCategoryTrRepository")
 */
class CharacteristicCategoryTr
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\CharacteristicCategory", inversedBy="characteristicCategoryTrs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"characteristicCategory"})
     */
    private $characteristicCategory;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Language")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"language"})
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     * @Groups({"default"})
     */
    private $name;


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
     * @return mixed
     */
    public function getCharacteristicCategory()
    {
        return $this->characteristicCategory;
    }

    /**
     * @param mixed $characteristicCategory
     */
    public function setCharacteristicCategory($characteristicCategory)
    {
        $this->characteristicCategory = $characteristicCategory;
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
     * Set name
     *
     * @param string $name
     *
     * @return CharacteristicCategoryTr
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

}

