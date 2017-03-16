<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
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
     * @Groups({"id", "default"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\BoxItem", inversedBy="boxItemChoices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $boxItem;

    /**
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\BoxItemChoiceTr", mappedBy="boxItemChoice", fetch="EAGER", cascade="ALL")
     * @Groups({"trs"})
     */
    private $boxItemChoiceTrs;

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

    public function __construct(){
        $this->boxItemChoiceTrs = new ArrayCollection();
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
     * @return mixed
     */
    public function getBoxItemChoiceTrs()
    {
        return $this->boxItemChoiceTrs;
    }

    public function addBoxItemChoiceTr(BoxItemChoiceTr $boxItemChoiceTr) {
        $this->boxItemChoiceTrs[] = $boxItemChoiceTr;
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
        foreach($this->boxItemChoiceTrs as $boxItemChoiceTr) {
            if($boxItemChoiceTr->getLanguage()->getCode() == $language) {
                $this->name = $boxItemChoiceTr->getName();
                $this->description = $boxItemChoiceTr->getDescription();
                break;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

}

