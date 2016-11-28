<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BoxOrderItemChoice
 *
 * @ORM\Table(name="box_order_item_choice")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\BoxOrderItemChoiceRepository")
 */
class BoxOrderItemChoice
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\BoxOrder")
     * @ORM\JoinColumn(nullable=false)
     */
    private $boxOrder;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\BoxItemChoice")
     * @ORM\JoinColumn(nullable=false)
     */
    private $boxItemChoice;


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
    public function getBoxOrder()
    {
        return $this->boxOrder;
    }

    /**
     * @param mixed $boxOrder
     */
    public function setBoxOrder($boxOrder)
    {
        $this->boxOrder = $boxOrder;
    }

    /**
     * @return mixed
     */
    public function getBoxItemChoice()
    {
        return $this->boxItemChoice;
    }

    /**
     * @param mixed $boxItemChoice
     */
    public function setBoxItemChoice($boxItemChoice)
    {
        $this->boxItemChoice = $boxItemChoice;
    }
}

