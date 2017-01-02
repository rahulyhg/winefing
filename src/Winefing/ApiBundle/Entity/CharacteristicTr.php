<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * CharacteristicTr
 *
 * @ORM\Table(name="characteristic_tr")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\CharacteristicTrRepository")
 */
class CharacteristicTr
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Characteristic", inversedBy="characteristicTrs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"characteristic"})
     */
    private $characteristic;

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
    public function getCharacteristic()
    {
        return $this->characteristic;
    }

    /**
     * @param mixed $characteristic
     */
    public function setCharacteristic($characteristic)
    {
        $this->characteristic = $characteristic;
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
     * @return CharacteristicTr
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

