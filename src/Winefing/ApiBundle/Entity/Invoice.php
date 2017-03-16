<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 07/03/2017
 * Time: 10:47
 */

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * The total TTC and HT appear only on the box invoice.
 * Indeed, the invoice for a rental order is a little bit different : only the winefing'fees appears on the invoice, for the client and for the host.
 *
 * Class Invoice
 * @ORM\Table(name="invoice")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\InvoiceRepository")
 */
class Invoice
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
     * @var float
     *
     * @ORM\Column(name="totalHT", type="float")
     * @Groups({"default"})
     */
    private $totalHT;

    /**
     * @var float
     *
     * @ORM\Column(name="totalTTC", type="float")
     * @Groups({"default"})
     */
    private $totalTTC;
    /**
     * @var float
     *
     * @ORM\Column(name="totalTax", type="float")
     * @Groups({"default"})
     */
    private $totalTax;

    /**
     * @var float
     *
     * @ORM\Column(name="tax", type="float")
     * @Groups({"default"})
     */
    private $tax;

    public function __construct($totalTTC, $tax)
    {
        $this->setTax($tax);
        $this->setTotalTTC($totalTTC);
    }

    /**
     * @return float
     */
    public function getTotalHT()
    {
        return $this->totalHT;
    }

    /**
     * @param float $totalHT
     */
    public function setTotalHT($totalHT)
    {
        $this->totalHT = $totalHT;
    }

    /**
     * @return float
     */
    public function getTotalTTC()
    {
        return $this->totalTTC;
    }

    /**
     * @param float $totalTTC
     */
    public function setTotalTTC($totalTTC)
    {
        $this->totalTTC = $totalTTC;
        $this->totalTax = round($totalTTC * ($this->tax/100), 2);
        $this->totalHT = $this->totalTTC - $this->totalTax;
    }

    /**
     * @return float
     */
    public function getTotalTax()
    {
        return $this->totalTax;
    }

    /**
     * @param float $totalTax
     */
    public function setTotalTax($totalTax)
    {
        $this->totalTax = $totalTax;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param float $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}