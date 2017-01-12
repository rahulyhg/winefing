<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
/**
 * BoxItem
 *
 * @ORM\Table(name="box_item")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\BoxItemRepository")
 */
class BoxItem
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Box", inversedBy="boxItems", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"box"})
     */
    private $box;

    /**
     * @var CharacteristicCategoryTr
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\BoxItemTr", mappedBy="boxItem", fetch="EAGER", cascade="ALL")
     * @Groups({"boxItemTrs"})
     */
    private $boxItemTrs;

    /**
     * @var CharacteristicCategoryTr
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\BoxItemChoice", mappedBy="boxItem", fetch="EAGER", cascade="ALL")
     * @Groups({"boxItemChoices"})
     */
    private $boxItemChoices;

    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $name;

    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $description;

    public function _construct(){
        $this->boxItemChoices[] = new ArrayCollection();
        $this->boxItemTrs[] = new ArrayCollection();
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
     * @return CharacteristicCategoryTr
     */
    public function getBoxItemChoices()
    {
        return $this->boxItemChoices;
    }

    public function addBoxItemChoice(BoxItemChoice $boxItemChoice) {
        $this->boxItemChoices[]=$boxItemChoice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBox()
    {
        return $this->box;
    }

    /**
     * @param mixed $box
     */
    public function setBox($box)
    {
        $this->box = $box;
    }

    /**
     * @return CharacteristicCategoryTr
     */
    public function getBoxItemTrs()
    {
        return $this->boxItemTrs;
    }

    public function addBoxItemTr(BoxItemTr $boxItemTr) {
        $this->boxItemTrs[] = $boxItemTr;
        return $this;
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
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setTr($language) {
        foreach($this->boxItemTrs as $boxItemTr) {
            if($boxItemTr->getLanguage()->getCode() == $language) {
                $this->name = $boxItemTr->getName();
                $this->description = $boxItemTr->getDescription();
                break;
            }
        }
    }
    public function resetBoxes(){
        $this->boxes = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }
}

