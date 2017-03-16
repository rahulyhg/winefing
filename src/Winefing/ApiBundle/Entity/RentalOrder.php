<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * RentalOrder
 *
 * @ORM\Table(name="rental_order")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\RentalOrderRepository")
 */
class RentalOrder
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
     * @var string
     *
     * @ORM\Column(name="rentalName", type="string", length=255)
     * @Groups({"default"})
     */
    private $rentalName;

    /**
     * @var string
     *
     * @ORM\Column(name="propertyName", type="string", length=255)
     * @Groups({"default"})
     */
    private $propertyName;


    /**
     * @var string
     *
     * @ORM\Column(name="domainName", type="string", length=255)
     * @Groups({"default"})
     */
    private $domainName;

    /**
     * @var Rental
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Rental", inversedBy="rentalOrders", fetch="EXTRA_LAZY")
     * @Groups({"default"})
     */
    private $rental;

    /**
     * @var dayPrices
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\DayPrice", mappedBy="rentalOrder", fetch="EXTRA_LAZY", cascade="ALL")
     * @Groups({"dayPrices"})
     */
    private $dayPrices;

    /**
     * @ORM\OneToOne(targetEntity="Winefing\ApiBundle\Entity\RentalOrderGift", inversedBy="rentalOrder", cascade="ALL")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"default"})
     */
    private $rentalOrderGift;

    /**
     * @var string
     *
     * @ORM\Column(name="startDate", type="datetime")
     * @Groups({"default"})
     */
    private $startDate;

    /**
     * @var string
     *
     * @ORM\Column(name="endDate", type="datetime")
     * @Groups({"default"})
     */
    private $endDate;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     * @Groups({"default"})
     */
    private $amount;

    /**
     * @var float
     *
     * @ORM\Column(name="total", type="float")
     * @Groups({"default"})
     */
    private $total;

    /**
     * @var float
     *
     * @ORM\Column(name="averagePrice", type="float")
     * @Groups({"default"})
     */
    private $averagePrice;

    /**
     * @var float
     *
     * @ORM\Column(name="leftToPay", type="float")
     * @Groups({"default"})
     */
    private $leftToPay;

    /**
     * @var float
     *
     * @ORM\Column(name="clientComission", type="float")
     * @Groups({"default"})
     */
    private $clientComission;

    /**
     * @var float
     *
     * @ORM\Column(name="hostComission", type="float")
     * @Groups({"default"})
     */
    private $hostComission;

    /**
     * @var int
     *
     * @ORM\Column(name="dayNumber", type="integer")
     * @Groups({"default"})
     */
    private $dayNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="hostCompanyName", type="string", length=255)
     * @Groups({"default"})
     */
    private $hostCompanyName;

    /**
     * @ORM\OneToOne(targetEntity="Winefing\ApiBundle\Entity\Address", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $hostCompanyAddress;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\InvoiceInformation", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $invoiceInformation;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Invoice", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $invoiceClient;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Invoice", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $invoiceHost;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\LemonWay", cascade="ALL")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"default"})
     */
    private $lemonWay;

    /**
     * @var
     * @Groups({"default"})
     * @Type("integer")
     */
    private $domainId;


    public function __construct()
    {
        $this->dayPrices = new ArrayCollection();
        $this->invoiceInformation = new InvoiceInformation();
    }

    public function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @param string $billingName
     */
    public function setBillingName($billingName)
    {
        $this->billingName = $billingName;
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
     * Set rental
     *
     * @param string $rental
     *
     * @return RentalOrder
     */
    public function setRental($rental)
    {
        $this->rental = $rental;

        return $this;
    }

    /**
     * Get rental
     *
     * @return string
     */
    public function getRental()
    {
        return $this->rental;
    }

    /**
     * Set startDate
     *
     * @param string $startDate
     *
     * @return RentalOrder
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return string
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param string $endDate
     *
     * @return RentalOrder
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return string
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set averagePrice
     *
     * @param float $averagePrice
     *
     * @return RentalOrder
     */
    public function setAveragePrice($averagePrice)
    {
        $this->averagePrice = $averagePrice;

        return $this;
    }

    /**
     * Get averagePrice
     *
     * @return float
     */
    public function getAveragePrice()
    {
        return $this->averagePrice;
    }

    /**
     * @return float
     */
    public function getClientComission()
    {
        return $this->clientComission;
    }

    /**
     * @param float $clientComission
     */
    public function setClientComission($clientComission)
    {
        $this->clientComission = $clientComission;
    }

    /**
     * @return float
     */
    public function getHostComission()
    {
        return $this->hostComission;
    }

    /**
     * @param float $hostComission
     */
    public function setHostComission($hostComission)
    {
        $this->hostComission = $hostComission;
    }

    /**
     * Set dayNumber
     *
     * @param integer $dayNumber
     *
     * @return RentalOrder
     */
    public function setDayNumber($dayNumber)
    {
        $this->dayNumber = $dayNumber;

        return $this;
    }

    /**
     * Get dayNumber
     *
     * @return int
     */
    public function getDayNumber()
    {
        return $this->dayNumber;
    }


    /**
     * @return dayPrices
     */
    public function getDayPrices()
    {
        return $this->dayPrices;
    }

    /**
     * @param dayPrices $dayPrices
     */
    public function setDayPrices($dayPrices)
    {
        $this->dayPrices = $dayPrices;
    }

    public function addDayPrice(DayPrice $dayPrice) {
        $this->dayPrices[] = $dayPrice;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getRentalOrderGift()
    {
        return $this->rentalOrderGift;
    }

    /**
     * @param mixed $rentalOrderGift
     */
    public function setRentalOrderGift($rentalOrderGift)
    {
        $this->rentalOrderGift = $rentalOrderGift;
    }

    /**
     * @return string
     */
    public function getHostCompanyName()
    {
        return $this->hostCompanyName;
    }

    /**
     * @param string $hostCompanyName
     */
    public function setHostCompanyName($hostCompanyName)
    {
        $this->hostCompanyName = $hostCompanyName;
    }

    /**
     * @return mixed
     */
    public function getHostCompanyAddress()
    {
        return $this->hostCompanyAddress;
    }

    /**
     * @param mixed $hostCompanyAddress
     */
    public function setHostCompanyAddress($hostCompanyAddress)
    {
        $this->hostCompanyAddress = $hostCompanyAddress;
    }

    /**
     * @return string
     */
    public function getRentalName()
    {
        return $this->rentalName;
    }

    /**
     * @param string $rentalName
     */
    public function setRentalName($rentalName)
    {
        $this->rentalName = $rentalName;
    }

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @param string $propertyName
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * @param string $domainName
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
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

    /**
     * @return mixed
     */
    public function getInvoiceClient()
    {
        return $this->invoiceClient;
    }

    /**
     * @param mixed $invoiceClient
     */
    public function setInvoiceClient($invoiceClient)
    {
        $this->invoiceClient = $invoiceClient;
    }

    /**
     * @return mixed
     */
    public function getInvoiceHost()
    {
        return $this->invoiceHost;
    }

    /**
     * @param mixed $invoiceHost
     */
    public function setInvoiceHost($invoiceHost)
    {
        $this->invoiceHost = $invoiceHost;
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
     * @return float
     */
    public function getLeftToPay()
    {
        return $this->leftToPay;
    }

    /**
     * @param float $leftToPay
     */
    public function setLeftToPay($leftToPay)
    {
        $this->leftToPay = $leftToPay;
    }



    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getDomainId()
    {
        return $this->domainId;
    }


    public function setDomainId() {
        $this->domainId = $this->getRental()->getProperty()->getDomain()->getId();
    }

}

