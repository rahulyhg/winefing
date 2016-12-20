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
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Box", inversedBy="boxItems")
     * @Groups({"boxes"})
     */
    private $boxes;

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

    public function _construct(){
        $this->boxItemChoices[] = new ArrayCollection();
        $this->boxItemTrs[] = new ArrayCollection();
        $this->boxes[] = new ArrayCollection();
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
    public function addBox(Box $box) {
        $this->boxes[]=$box;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBoxes()
    {
        return $this->boxes;
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

