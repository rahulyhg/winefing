<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Box")
     * @ORM\JoinColumn(nullable=false)
     */
    private $box;

    /**
     * @var CharacteristicCategoryTr
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\BoxItemTr", mappedBy="boxItem", fetch="EAGER", cascade="ALL")
     */
    private $boxItemTrs;

    /**
     * @var CharacteristicCategoryTr
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\BoxItemChoice", mappedBy="boxItem", fetch="EAGER", cascade="ALL")
     */
    private $boxItemChoices;

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
}

