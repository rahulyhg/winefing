<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * BoxItemChoice
 *
 * @ORM\Table(name="box_item_choice")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\BoxItemChoiceRepository")
 */
class BoxItemChoice
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\BoxItem")
     * @ORM\JoinColumn(nullable=false)
     */
    private $boxItem;

    /**
     * @var CharacteristicCategoryTr
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\BoxItemChoiceTr", mappedBy="boxItemChoice", fetch="EAGER", cascade="ALL")
     */
    private $boxItemChoiceTrs;

    /**
     * @var int
     *
     * @ORM\Column(name="number", type="integer")
     */
    private $number;

    public function _construct(){
        $this->boxItemChoiceTrs[] = new ArrayCollection();
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
     * Set number
     *
     * @param integer $number
     *
     * @return BoxItemChoice
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return mixed
     */
    public function getBoxItem()
    {
        return $this->boxItem;
    }

    /**
     * @param mixed $boxItem
     */
    public function setBoxItem($boxItem)
    {
        $this->boxItem = $boxItem;
    }

    /**
     * @return CharacteristicCategoryTr
     */
    public function getBoxItemChoiceTrs()
    {
        return $this->boxItemChoiceTrs;
    }

    public function addBoxItemChoiceTr(BoxItemChoiceTr $boxItemChoiceTr) {
        $this->boxItemChoiceTrs[] = $boxItemChoiceTr;
    }
}

