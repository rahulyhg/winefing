<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Type;

/**
 * Characteristic
 *
 * @ORM\Table(name="characteristic")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\CharacteristicRepository")
 */
class Characteristic
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Format")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $format;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\CharacteristicCategory", inversedBy="characteristics", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $characteristicCategory;


    /**
     * @var CharacteristicTr
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\CharacteristicTr", mappedBy="characteristic", fetch="EAGER", cascade="ALL")
     * @Groups({"trs"})
     */
    private $characteristicTrs;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(name="picture", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $picture;

    /**
     * @var bool
     *
     * @ORM\Column(name="activated", type="boolean")
     * @Groups({"default"})
     */
    private $activated;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=50)
     * @Groups({"default"})
     */
    private $code;

    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
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
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param mixed $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
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
    public function setChacarteristicCategory($chararteristicCategory)
    {
        $this->characteristicCategory = $chararteristicCategory;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Characteristic
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
     * Set picture
     *
     * @param string $picture
     *
     * @return Characteristic
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set activated
     *
     * @param boolean $activated
     *
     * @return Characteristic
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
     * @return CharacteristicTr
     */
    public function getCharacteristicTrs()
    {
        return $this->characteristicTrs;
    }

    public function __construct() {
        $this->characteristicTrs = new ArrayCollection();
    }

    public function addCharacteristicTr(CharacteristicTr $characteristicTr)
    {
        $this->characteristicTrs[] = $characteristicTr;
        $characteristicTr->setCharacteristic($this);
        return $this;
    }

    public function removeCharacteristicTr(CharacteristicTr $characteristicTr)
    {
        $this->characteristicTrs->removeElement($characteristicTr);
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
        foreach($this->getCharacteristicTrs() as $characteristicTr) {
            if($characteristicTr->getLanguage()->getCode() == $language) {
                $this->name = $characteristicTr->getName();
                break;
            }
        }
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
}

