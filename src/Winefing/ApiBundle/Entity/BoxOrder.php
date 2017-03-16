<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
/**
 * BoxOrder
 *
 * @ORM\Table(name="box_order")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\BoxOrderRepository")
 */
class BoxOrder
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Box", inversedBy="boxOrders", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $box;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\BoxItemChoice", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $boxItemChoices;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\LemonWay", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $lemonWay;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Invoice", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $invoice;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\InvoiceInformation", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $invoiceInformation;



    public function __construct(Box $box)
    {
        $this->boxItemChoices = new ArrayCollection();
        $this->invoiceInformation = new InvoiceInformation();
        $this->box = $box;
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
     * @return mixed
     */
    public function getBoxItemChoices()
    {
        return $this->boxItemChoices;
    }

    public function addBoxItemChoice(BoxItemChoice $boxItemChoice) {
        $this->boxItemChoices[] = $boxItemChoice;
    }

    /**
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param mixed $invoice
     */
    public function setInvoice($invoice)
    {
        $invoice->setTotalTTC($this->getBox()->getPrice());
        $this->invoice = $invoice;
    }

    /**
     * @return mixed
     */
    public function getLemonWay()
    {
        return $this->lemonWay;
    }

    /**
     * @param mixed $lemonWay
     */
    public function setLemonWay($lemonWay)
    {
        $this->lemonWay = $lemonWay;
    }

    /**
     * @return mixed
     */
    public function getInvoiceInformation()
    {
        return $this->invoiceInformation;
    }

    /**
     * @param mixed $invoiceInformation
     */
    public function setInvoiceInformation($invoiceInformation)
    {
        $this->invoiceInformation = $invoiceInformation;
    }

}

